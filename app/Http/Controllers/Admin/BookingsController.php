<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingQuotationMail;
use App\Models\Booking;
use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\HotelRoom;
use App\Models\PackageInclusion;
use App\Models\PackageInclusionPackage;
use App\Models\PackagePlanItem;
use App\Models\State;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class BookingsController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with('package')->latest('created_at')->paginate(20);
        $processing = false;

        return view('admin.bookings', compact('bookings', 'processing'));
    }

    public function processing(): View
    {
        $bookings = Booking::with('package')
            ->where('status', 'pending')
            ->whereNotNull('package_id')
            ->latest('created_at')
            ->paginate(20);
        $processing = true;

        return view('admin.bookings', compact('bookings', 'processing'));
    }

    public function show(Booking $booking): View
    {
        $itineraryItems = $booking->package
            ? $this->bookingItineraryItems($booking->package, $booking->itinerary_override)
            : collect();
        $displayPackageName = $booking->itinerary_override['custom_package']['name']
            ?? $booking->package?->name
            ?? 'N/A';

        return view('admin.bookings.show', compact('booking', 'itineraryItems', 'displayPackageName'));
    }

    public function process(Request $request, Booking $booking): View
    {
        abort_if($booking->status !== 'pending', 422, 'Only pending bookings can be processed.');
        abort_if($booking->package === null, 422, 'This booking has no package itinerary.');

        $packages = TourPackage::query()->orderBy('name')->get();
        $packageMode = $request->query('package_mode')
            ?? (isset($booking->itinerary_override['custom_package']) ? 'custom' : 'current');
        abort_unless(in_array($packageMode, ['current', 'existing', 'custom'], true), 422, 'Choose a package option.');
        $selectedPackage = $packageMode === 'existing' && $request->query('package_id')
            ? TourPackage::query()->findOrFail($request->query('package_id'))
            : $booking->package;
        $savedCustomPackage = $selectedPackage->is($booking->package)
            ? ($booking->itinerary_override['custom_package'] ?? null)
            : null;

        $samePackage = $selectedPackage->is($booking->package);
        if ($packageMode === 'existing') {
            $itinerarySnapshot = null;
        } elseif ($packageMode === 'custom' && $savedCustomPackage === null) {
            $itinerarySnapshot = [
                'days' => (int) $selectedPackage->days,
                'items' => [],
            ];
        } else {
            $itinerarySnapshot = $samePackage ? $booking->itinerary_override : null;
        }
        $itineraryItems = $this->bookingItineraryItems($selectedPackage, $itinerarySnapshot);
        $destinationCityIds = $packageMode === 'custom'
            ? []
            : $this->bookingDestinationCityIds($selectedPackage, $itineraryItems);
        $placesQuery = CityPlace::query()->with('city');
        if ($packageMode !== 'custom') {
            $placesQuery->whereIn('city_id', $destinationCityIds);
        }
        $places = $placesQuery->orderBy('name')->get();
        $hotelRoomsQuery = HotelRoom::query()
            ->with(['hotel.city', 'roomType'])
            ->whereNotNull('customer_adult_price');
        if ($packageMode !== 'custom') {
            $hotelRoomsQuery->whereHas('hotel', fn (Builder $query) => $query->whereIn('city_id', $destinationCityIds));
        }
        $hotelRooms = $hotelRoomsQuery->orderBy('name')->get();
        $inclusionItems = $packageMode === 'custom'
            ? PackageInclusion::query()->orderBy('name')->get()
            : $selectedPackage->inclusionItems()->with('inclusion')->orderBy('id')->get();
        $inclusionOptions = $inclusionItems->map(fn (PackageInclusion|PackageInclusionPackage $item): array => [
            'id' => $item->id,
            'label' => $item instanceof PackageInclusion
                ? $item->name
                : ($item->custom_name ?: $item->inclusion?->name ?: 'Included service'),
            'time' => $item instanceof PackageInclusion
                ? substr((string) ($item->scheduled_time ?? ''), 0, 5)
                : substr((string) ($item->custom_scheduled_time ?: $item->inclusion?->scheduled_time ?: ''), 0, 5),
        ])->values();
        $itineraryDays = $this->bookingItineraryDays($selectedPackage, $itinerarySnapshot, $itineraryItems);
        $countries = Country::query()->orderBy('name')->get();
        $states = State::query()->with('country')->orderBy('name')->get();
        $cities = City::query()->with('state.country')->orderBy('name')->get();
        $customPackage = $savedCustomPackage ?? [];
        if ($packageMode === 'custom' && $savedCustomPackage !== null && ! isset($customPackage['destinations']['city'])) {
            $customPackage['destinations']['city'] = $itineraryItems->flatMap(fn (array $item): array => [
                $item['place']?->city_id,
                $item['hotelRoom']?->hotel?->city_id,
            ])->filter()->unique()->values()->all();
        }
        if ($packageMode === 'custom' && isset($customPackage['destinations']['city'])) {
            $destinationCities = $cities->whereIn('id', $customPackage['destinations']['city']);
            $customPackage['destinations']['state'] ??= $destinationCities->pluck('state_id')->unique()->values()->all();
            $customPackage['destinations']['country'] ??= $destinationCities
                ->map(fn (City $city): ?int => $city->state?->country_id)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return view('admin.bookings.process', compact(
            'booking',
            'packages',
            'selectedPackage',
            'packageMode',
            'customPackage',
            'itineraryItems',
            'itineraryDays',
            'countries',
            'states',
            'cities',
            'places',
            'hotelRooms',
            'inclusionItems',
            'inclusionOptions',
        ));
    }

    public function updateProcessing(Request $request, Booking $booking): RedirectResponse
    {
        abort_if($booking->status !== 'pending', 422, 'Only pending bookings can be processed.');
        $currentItineraryItems = $booking->package
            ? $this->bookingItineraryItems($booking->package, $booking->itinerary_override)
            : collect();

        $validator = Validator::make($request->all(), [
            'package_id' => ['nullable', 'integer', 'exists:tour_packages,id'],
            'package_mode' => ['required', 'in:current,existing,custom'],
            'custom_package_name' => ['required_if:package_mode,custom', 'nullable', 'string', 'max:120'],
            'custom_base_price' => ['required_if:package_mode,custom', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'custom_category' => ['nullable', 'string', 'max:80'],
            'custom_tour_type' => ['required_if:package_mode,custom', 'nullable', 'in:group,private'],
            'custom_description' => ['nullable', 'string', 'max:2000'],
            'custom_departure_city' => ['required_if:package_mode,custom', 'nullable', 'string', 'max:100'],
            'custom_child_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'custom_infant_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'custom_discount_percentage' => ['required_if:package_mode,custom', 'nullable', 'numeric', 'min:0', 'max:100'],
            'custom_tax_percentage' => ['required_if:package_mode,custom', 'nullable', 'numeric', 'min:0', 'max:100'],
            'custom_departure_date' => ['required_if:package_mode,custom', 'nullable', 'date'],
            'custom_destinations' => ['required_if:package_mode,custom', 'array:country,state,city'],
            'custom_destinations.country' => ['required_if:package_mode,custom', 'array', 'min:1', 'max:100'],
            'custom_destinations.country.*' => ['integer', 'distinct', 'exists:countries,id'],
            'custom_destinations.state' => ['required_if:package_mode,custom', 'array', 'min:1', 'max:100'],
            'custom_destinations.state.*' => ['integer', 'distinct', 'exists:states,id'],
            'custom_destinations.city' => ['required_if:package_mode,custom', 'array', 'min:1', 'max:100'],
            'custom_destinations.city.*' => ['integer', 'distinct', 'exists:cities,id'],
            'days' => ['required', 'integer', 'min:1', 'max:99'],
            'itinerary' => ['nullable', 'array', 'max:100'],
            'itinerary.*' => ['array:kind,selection_id,day_number,start_time,nights,additional_price'],
            'itinerary.*.kind' => ['required', 'in:place,hotel,inclusion'],
            'itinerary.*.selection_id' => ['required', 'integer', 'min:1'],
            'itinerary.*.day_number' => ['required', 'integer', 'min:1', 'max:99'],
            'itinerary.*.start_time' => ['required', 'date_format:H:i'],
            'itinerary.*.nights' => ['nullable', 'integer', 'min:1', 'max:99'],
            'itinerary.*.additional_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);
        $validator->after(function (ValidationValidator $validator) use ($booking): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $validatedData = $validator->getData();
            $package = isset($validatedData['package_id'])
                ? TourPackage::query()->find($validatedData['package_id'])
                : $booking->package;
            if ($package === null) {
                $validator->errors()->add('package_id', 'Choose an existing package as the itinerary template.');

                return;
            }
            $packageMode = $validatedData['package_mode'];
            $isCustomPackage = $packageMode === 'custom';
            $isBookingPackage = $booking->package_id === $package->id;
            if ($packageMode === 'current' && ! $isBookingPackage) {
                $validator->errors()->add('package_id', 'The current-package option must keep the package originally booked.');

                return;
            }
            $packageSnapshot = $isBookingPackage && $packageMode !== 'existing'
                ? $booking->itinerary_override
                : null;
            $packageItems = $this->bookingItineraryItems($package, $packageSnapshot);
            $destinationCityIds = $isCustomPackage
                ? array_map('intval', $validatedData['custom_destinations']['city'] ?? [])
                : $this->bookingDestinationCityIds($package, $packageItems);
            if ($isCustomPackage) {
                $selectedCountryIds = array_map('intval', $validatedData['custom_destinations']['country'] ?? []);
                $selectedStateIds = array_map('intval', $validatedData['custom_destinations']['state'] ?? []);
                $validStateIds = State::query()
                    ->whereKey($selectedStateIds)
                    ->whereIn('country_id', $selectedCountryIds)
                    ->pluck('id')
                    ->map(fn (mixed $id): int => (int) $id)
                    ->all();
                if (count($validStateIds) !== count(array_unique($selectedStateIds))) {
                    $validator->errors()->add('custom_destinations.state', 'Choose states belonging to the selected countries.');
                }

                $validCityIds = City::query()
                    ->whereKey($destinationCityIds)
                    ->whereIn('state_id', $validStateIds)
                    ->pluck('id')
                    ->map(fn (mixed $id): int => (int) $id)
                    ->all();
                if (count($validCityIds) !== count(array_unique($destinationCityIds))) {
                    $validator->errors()->add('custom_destinations.city', 'Choose cities belonging to the selected states.');
                }
            }
            $submittedItems = array_values($validatedData['itinerary'] ?? []);
            foreach ($submittedItems as $index => $item) {
                if ($item['day_number'] > $validatedData['days']) {
                    $validator->errors()->add("itinerary.{$index}.day_number", 'The itinerary day cannot be greater than this booking’s duration.');
                }

                if ($item['kind'] === 'place' && ! CityPlace::query()
                    ->whereKey($item['selection_id'])
                    ->whereIn('city_id', $destinationCityIds)
                    ->exists()) {
                    $validator->errors()->add("itinerary.{$index}.selection_id", $isCustomPackage
                        ? 'Choose a place from one of this custom package’s selected destination cities.'
                        : 'Choose a place from one of this package’s destination cities.');
                } elseif ($item['kind'] === 'hotel' && ! HotelRoom::query()
                    ->whereKey($item['selection_id'])
                    ->whereNotNull('customer_adult_price')
                    ->whereHas(
                        'hotel',
                        fn (Builder $hotelQuery) => $hotelQuery->whereIn('city_id', $destinationCityIds),
                    )
                    ->exists()) {
                    $validator->errors()->add("itinerary.{$index}.selection_id", $isCustomPackage
                        ? 'Choose a priced hotel room from one of this custom package’s selected destination cities.'
                        : 'Choose a priced hotel room from one of this package’s destination cities.');
                } elseif ($item['kind'] === 'inclusion' && ! ($isCustomPackage
                    ? PackageInclusion::query()->whereKey($item['selection_id'])->exists()
                    : $package->inclusionItems()->whereKey($item['selection_id'])->exists())) {
                    $validator->errors()->add("itinerary.{$index}.selection_id", $isCustomPackage
                        ? 'Choose an available package service.'
                        : 'Choose a service included in this package.');
                }
            }
        });
        $validated = $validator->validate();
        $package = TourPackage::query()->findOrFail($validated['package_id'] ?? $booking->package_id);
        $isCustomPackage = $validated['package_mode'] === 'custom';
        $submittedItems = array_values($validated['itinerary'] ?? []);

        $placeIds = collect($submittedItems)->where('kind', 'place')->pluck('selection_id')->unique()->all();
        $hotelRoomIds = collect($submittedItems)->where('kind', 'hotel')->pluck('selection_id')->unique()->all();
        $inclusionItemIds = collect($submittedItems)->where('kind', 'inclusion')->pluck('selection_id')->unique()->all();
        $places = CityPlace::query()->whereKey($placeIds)->get()->keyBy('id');
        $hotelRooms = HotelRoom::query()
            ->with(['hotel.city', 'roomType'])
            ->whereKey($hotelRoomIds)
            ->get()
            ->keyBy('id');
        $inclusionItems = $isCustomPackage
            ? PackageInclusion::query()->whereKey($inclusionItemIds)->get()->keyBy('id')
            : PackageInclusionPackage::query()
                ->with('inclusion')
                ->whereBelongsTo($package, 'package')
                ->whereKey($inclusionItemIds)
                ->get()
                ->keyBy('id');
        $updatedItems = collect($submittedItems)->map(function (array $input, int $index) use ($places, $hotelRooms, $inclusionItems, $isCustomPackage): array {
            $itemType = $input['kind'];
            $selectionId = (int) $input['selection_id'];
            $item = [
                'item_type' => $itemType,
                'day_number' => (int) $input['day_number'],
                'start_time' => $input['start_time'],
                'nights' => $itemType === 'hotel' ? (int) ($input['nights'] ?? 1) : null,
                'city_place_id' => null,
                'hotel_room_id' => null,
                'package_inclusion_item_id' => null,
                'package_inclusion_id' => null,
                'additional_price' => (float) ($input['additional_price'] ?? 0),
                'position' => $index,
                'place' => null,
                'hotelRoom' => null,
            ];

            if ($itemType === 'place') {
                $place = $places->get($selectionId);
                abort_if($place === null, 422, 'The selected place is no longer available.');
                $item['city_place_id'] = $place->id;
                $item['title'] = $place->name;
                $item['place'] = $place;
            } elseif ($itemType === 'hotel') {
                $hotelRoom = $hotelRooms->get($selectionId);
                abort_if($hotelRoom === null, 422, 'The selected hotel room is no longer available.');
                $item['hotel_room_id'] = $hotelRoom->id;
                $item['title'] = $this->hotelRoomTitle($hotelRoom);
                $item['hotelRoom'] = $hotelRoom;
            } else {
                $inclusionItem = $inclusionItems->get($selectionId);
                abort_if($inclusionItem === null, 422, 'The selected package service is no longer available.');
                if ($isCustomPackage) {
                    $item['package_inclusion_id'] = $inclusionItem->id;
                    $item['title'] = $inclusionItem->name;
                } else {
                    $item['package_inclusion_item_id'] = $inclusionItem->id;
                    $item['title'] = $inclusionItem->custom_name ?: $inclusionItem->inclusion?->name ?: 'Included service';
                }
            }

            return $item;
        })->sortBy(fn (array $item): string => sprintf(
            '%03d-%s-%06d',
            (int) $item['day_number'],
            (string) $item['start_time'],
            (int) ($item['position'] ?? 0),
        ))->values();
        $currentHotelItem = $currentItineraryItems->first(fn (array $item): bool => $item['item_type'] === 'hotel');
        $hotelItem = $updatedItems->first(fn (array $item): bool => $item['item_type'] === 'hotel');
        $customPackage = $isCustomPackage ? [
            'name' => $validated['custom_package_name'],
            'base_price' => (float) $validated['custom_base_price'],
            'category' => $validated['custom_category'] ?? '',
            'tour_type' => $validated['custom_tour_type'],
            'description' => $validated['custom_description'] ?? '',
            'departure_city' => $validated['custom_departure_city'],
            'child_price' => isset($validated['custom_child_price']) ? (float) $validated['custom_child_price'] : null,
            'infant_price' => isset($validated['custom_infant_price']) ? (float) $validated['custom_infant_price'] : null,
            'discount_percentage' => (float) $validated['custom_discount_percentage'],
            'tax_percentage' => (float) $validated['custom_tax_percentage'],
            'departure_date' => $validated['custom_departure_date'],
            'destinations' => [
                'country' => array_map('intval', $validated['custom_destinations']['country'] ?? []),
                'state' => array_map('intval', $validated['custom_destinations']['state'] ?? []),
                'city' => array_map('intval', $validated['custom_destinations']['city'] ?? []),
            ],
        ] : null;
        $departureDate = $isCustomPackage
            ? $validated['custom_departure_date']
            : ($booking->check_in_date
                ? $booking->check_in_date->copy()->subDays(max(0, (int) ($currentHotelItem['day_number'] ?? 1) - 1))->toDateString()
                : null);
        $checkInDate = $departureDate && $hotelItem
            ? Carbon::parse($departureDate)->addDays(max(0, (int) $hotelItem['day_number'] - 1))->toDateString()
            : $departureDate;
        $hotelNights = $updatedItems
            ->where('item_type', 'hotel')
            ->sum(fn (array $item): int => (int) ($item['nights'] ?? 1));
        $checkOutDate = $checkInDate && $hotelNights > 0
            ? Carbon::parse($checkInDate)->addDays($hotelNights)->toDateString()
            : null;
        $itineraryOverride = $this->snapshotItinerary((int) $validated['days'], $updatedItems, $customPackage);
        $amount = $this->calculateBookingAmount(
            $package,
            $booking->country,
            (int) $booking->adult_count,
            (int) $booking->child_count,
            (int) $booking->student_count,
            (int) $booking->extra_bed_count,
            $updatedItems,
            $customPackage,
        );
        $hotelRoomId = $hotelItem['hotel_room_id'] ?? null;
        $hotelDetailsChanged = $booking->package_id !== $package->id
            || $booking->hotel_room_id !== $hotelRoomId
            || $booking->check_in_date?->toDateString() !== $checkInDate
            || $booking->check_out_date?->toDateString() !== $checkOutDate;
        $attributes = [
            'package_id' => $package->id,
            'itinerary_override' => $itineraryOverride,
            'hotel_room_id' => $hotelRoomId,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'amount' => $amount,
        ];

        if ($hotelDetailsChanged) {
            $attributes += [
                'hotel_request_status' => 'pending',
                'hotel_booking_requested_at' => null,
                'hotel_response' => null,
                'hotel_responded_at' => null,
            ];
        }

        $booking->update($attributes);
        $booking->refresh();

        try {
            Mail::mailer('smtp')->to($booking->email)->send(new BookingQuotationMail(
                $booking,
                (string) ($customPackage['name'] ?? $package->name),
                (int) $validated['days'],
                $updatedItems->map(fn (array $item): array => [
                    'day_number' => (int) $item['day_number'],
                    'start_time' => (string) $item['start_time'],
                    'title' => (string) $item['title'],
                    'nights' => isset($item['nights']) ? (int) $item['nights'] : null,
                    'additional_price' => (float) $item['additional_price'],
                ])->all(),
            ));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->route('admin.bookings.show', $booking)
                ->with('error', 'Booking #'.$booking->id.' was updated, but its quotation email could not be sent. Check the email settings and try again.');
        }

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Booking #'.$booking->id.' package, itinerary, and hotel have been updated, and the quotation was emailed. The booking remains pending.');
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        abort_if($booking->status !== 'pending', 422, 'Only pending bookings can be confirmed.');

        $booking->update(['status' => 'confirmed']);

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Booking #'.$booking->id.' has been confirmed.');
    }

    public function edit(Booking $booking): View
    {
        $packages = TourPackage::with(['availabilities', 'planItems.hotelRoom.hotel', 'planItems.hotelRoom.roomType'])
            ->orderBy('name')
            ->get();

        return view('admin.bookings.edit', compact('booking', 'packages'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'integer', 'exists:tour_packages,id'],
            'status' => ['required', 'in:pending,confirmed,cancelled'],
            'adult_count' => ['required', 'integer', 'min:1', 'max:99'],
            'child_count' => ['required', 'integer', 'min:0', 'max:99'],
            'student_count' => ['required', 'integer', 'min:0', 'lte:adult_count'],
            'departure_date' => ['nullable', 'date'],
            'room_count' => ['required', 'integer', 'min:1', 'max:20'],
            'extra_bed_count' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $package = TourPackage::with(['availabilities', 'planItems.place', 'planItems.hotelRoom'])
            ->findOrFail($validated['package_id']);
        if ($package->availabilities->isNotEmpty()) {
            $request->validate([
                'departure_date' => ['required', 'date', 'in:'.implode(',', $package->availabilities->map(fn ($availability): string => $availability->departure_date->format('Y-m-d'))->all())],
            ]);
        }

        $itineraryItems = $this->bookingItineraryItems(
            $package,
            $booking->package_id === $package->id ? $booking->itinerary_override : null,
        );
        $hotelItems = $itineraryItems
            ->where('item_type', 'hotel')
            ->filter(fn (array $item): bool => $item['hotelRoom'] !== null);
        $hotelPlanItem = $hotelItems->first();
        $hotelRoom = $hotelPlanItem['hotelRoom'] ?? null;
        if ($hotelRoom === null) {
            $validated['extra_bed_count'] = 0;
        } else {
            $requiredExtraBeds = Booking::requiredExtraBedCount(
                (int) $validated['adult_count'],
                (int) $validated['child_count'],
                (int) $validated['room_count'],
            );
            abort_if(
                $requiredExtraBeds > $validated['room_count'],
                422,
                'There are not enough rooms for these guests, even with one extra bed per room.',
            );
            abort_if(
                $validated['extra_bed_count'] < $requiredExtraBeds,
                422,
                "At least {$requiredExtraBeds} extra bed(s) are required for the selected room count.",
            );
            abort_if($validated['extra_bed_count'] > $validated['room_count'], 422, 'Extra beds cannot exceed the number of rooms.');
        }

        $customDepartureDate = $booking->package_id === $package->id
            ? ($booking->itinerary_override['custom_package']['departure_date'] ?? null)
            : null;
        $departureDate = $customDepartureDate
            ?? $validated['departure_date']
            ?? $package->availabilities->first()?->departure_date?->toDateString();
        $checkInDate = $departureDate && $hotelPlanItem
            ? Carbon::parse($departureDate)->addDays(max(0, (int) $hotelPlanItem['day_number'] - 1))->toDateString()
            : $departureDate;
        $hotelNights = $hotelItems->sum(fn (array $item): int => (int) ($item['nights'] ?? 1));
        $checkOutDate = $checkInDate && $hotelNights > 0
            ? Carbon::parse($checkInDate)->addDays($hotelNights)->toDateString()
            : null;

        $amount = $this->calculateBookingAmount(
            $package,
            $booking->country,
            (int) $validated['adult_count'],
            (int) $validated['child_count'],
            (int) $validated['student_count'],
            (int) $validated['extra_bed_count'],
            $itineraryItems,
            $booking->package_id === $package->id
                ? ($booking->itinerary_override['custom_package'] ?? null)
                : null,
        );

        $hotelDetailsChanged = $booking->package_id !== $package->id
            || $booking->adult_count !== (int) $validated['adult_count']
            || $booking->child_count !== (int) $validated['child_count']
            || $booking->room_count !== (int) $validated['room_count']
            || $booking->extra_bed_count !== (int) $validated['extra_bed_count']
            || $booking->check_in_date?->toDateString() !== $checkInDate;

        $booking->update([
            'status' => $validated['status'],
            'package_id' => $package->id,
            'itinerary_override' => $booking->package_id === $package->id ? $booking->itinerary_override : null,
            'adult_count' => $validated['adult_count'],
            'child_count' => $validated['child_count'],
            'student_count' => $validated['student_count'],
            'room_count' => $validated['room_count'],
            'extra_bed_count' => $validated['extra_bed_count'],
            'hotel_room_id' => $hotelRoom?->id,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'amount' => $amount,
            ...($hotelDetailsChanged ? [
                'hotel_request_status' => 'pending',
                'hotel_booking_requested_at' => null,
                'hotel_response' => null,
                'hotel_responded_at' => null,
            ] : []),
        ]);

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Booking #'.$booking->id.' has been updated.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function bookingItineraryItems(TourPackage $package, ?array $snapshot): Collection
    {
        if ($snapshot === null) {
            $package->loadMissing([
                'planItems.place',
                'planItems.hotelRoom.hotel',
                'planItems.hotelRoom.roomType',
                'planItems.packageInclusionItem.inclusion',
            ]);

            $items = $package->planItems->map(fn (PackagePlanItem $item): array => [
                'item_type' => $item->item_type,
                'day_number' => (int) $item->day_number,
                'start_time' => substr((string) $item->start_time, 0, 5),
                'nights' => $item->nights === null ? null : (int) $item->nights,
                'city_place_id' => $item->city_place_id === null ? null : (int) $item->city_place_id,
                'hotel_room_id' => $item->hotel_room_id === null ? null : (int) $item->hotel_room_id,
                'package_inclusion_item_id' => $item->package_inclusion_package_id === null ? null : (int) $item->package_inclusion_package_id,
                'package_inclusion_id' => $item->package_inclusion_id === null ? null : (int) $item->package_inclusion_id,
                'additional_price' => (float) $item->additional_price,
                'position' => (int) $item->position,
                'title' => $this->packagePlanItemTitle($item),
            ])->values();
        } else {
            $items = collect($snapshot['items'] ?? $snapshot)->values();
        }

        $placeIds = $items->pluck('city_place_id')->filter()->map(fn ($id): int => (int) $id)->unique()->all();
        $hotelRoomIds = $items->pluck('hotel_room_id')->filter()->map(fn ($id): int => (int) $id)->unique()->all();
        $inclusionIds = $items->pluck('package_inclusion_id')->filter()->map(fn ($id): int => (int) $id)->unique()->all();
        $places = CityPlace::query()->with('city')->whereKey($placeIds)->get()->keyBy('id');
        $hotelRooms = HotelRoom::query()
            ->with(['hotel.city', 'roomType'])
            ->whereKey($hotelRoomIds)
            ->get()
            ->keyBy('id');
        $inclusionItems = $package->inclusionItems()
            ->with('inclusion')
            ->get()
            ->keyBy('id');
        $inclusions = PackageInclusion::query()->whereKey($inclusionIds)->get()->keyBy('id');

        return $items->map(function (array $item) use ($places, $hotelRooms, $inclusionItems, $inclusions): array {
            $item['place'] = $places->get((int) ($item['city_place_id'] ?? 0));
            $item['hotelRoom'] = $hotelRooms->get((int) ($item['hotel_room_id'] ?? 0));
            $item['packageInclusionItem'] = $inclusionItems->get((int) ($item['package_inclusion_item_id'] ?? 0));
            $item['packageInclusion'] = $inclusions->get((int) ($item['package_inclusion_id'] ?? 0));
            if ($item['item_type'] === 'inclusion' && $item['packageInclusionItem'] === null && $item['packageInclusion'] === null) {
                $item['packageInclusionItem'] = $inclusionItems->first(function (PackageInclusionPackage $inclusionItem) use ($item): bool {
                    $title = $inclusionItem->custom_name ?: $inclusionItem->inclusion?->name;

                    return $title !== null && $title === ($item['title'] ?? null);
                });
                $item['package_inclusion_item_id'] = $item['packageInclusionItem']?->id;
            }
            if ($item['item_type'] === 'inclusion' && empty($item['package_inclusion_id'])) {
                $item['package_inclusion_id'] = $item['packageInclusionItem']?->package_inclusion_id;
            }
            $item['title'] = $item['title'] ?? match ($item['item_type']) {
                'place' => $item['place']?->name ?? 'Place',
                'hotel' => $item['hotelRoom'] ? $this->hotelRoomTitle($item['hotelRoom']) : 'Hotel',
                default => $item['packageInclusion']?->name
                    ?: $item['packageInclusionItem']?->custom_name
                    ?: $item['packageInclusionItem']?->inclusion?->name
                    ?: 'Included service',
            };

            return $item;
        })->sortBy(fn (array $item): string => sprintf(
            '%03d-%s-%06d',
            (int) $item['day_number'],
            (string) $item['start_time'],
            (int) ($item['position'] ?? 0),
        ))->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  array<string, mixed>|null  $customPackage
     * @return array{days: int, items: array<int, array<string, mixed>>, custom_package?: array{name: string, base_price: float}}
     */
    private function snapshotItinerary(int $days, Collection $items, ?array $customPackage = null): array
    {
        $snapshot = [
            'days' => $days,
            'items' => $items->map(fn (array $item): array => [
                'item_type' => (string) $item['item_type'],
                'day_number' => (int) $item['day_number'],
                'start_time' => (string) $item['start_time'],
                'nights' => isset($item['nights']) ? (int) $item['nights'] : null,
                'city_place_id' => isset($item['city_place_id']) ? (int) $item['city_place_id'] : null,
                'hotel_room_id' => isset($item['hotel_room_id']) ? (int) $item['hotel_room_id'] : null,
                'package_inclusion_item_id' => isset($item['package_inclusion_item_id']) ? (int) $item['package_inclusion_item_id'] : null,
                'package_inclusion_id' => isset($item['package_inclusion_id']) ? (int) $item['package_inclusion_id'] : null,
                'additional_price' => (float) ($item['additional_price'] ?? 0),
                'position' => (int) ($item['position'] ?? 0),
                'title' => (string) ($item['title'] ?? ''),
            ])->values()->all(),
        ];
        if ($customPackage !== null) {
            $snapshot['custom_package'] = $customPackage;
        }

        return $snapshot;
    }

    /**
     * @param  array<string|int, mixed>|null  $snapshot
     * @param  Collection<int, array<string, mixed>>  $items
     */
    private function bookingItineraryDays(TourPackage $package, ?array $snapshot, Collection $items): int
    {
        $savedDays = is_array($snapshot) && isset($snapshot['days']) ? (int) $snapshot['days'] : 0;
        $latestItemDay = (int) $items->max('day_number');

        return min(99, max(1, $savedDays ?: (int) $package->days, $latestItemDay));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $itineraryItems
     * @return array<int, int>
     */
    private function bookingDestinationCityIds(TourPackage $package, Collection $itineraryItems): array
    {
        $destinations = $package->destinations()->get(['destination_type', 'destination_id']);
        $idsFor = fn (string $type): array => $destinations
            ->where('destination_type', $type)
            ->pluck('destination_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $countryIds = $idsFor(Country::class);
        $stateIds = $idsFor(State::class);
        $cityIds = City::query()->whereKey($idsFor(City::class))->pluck('id')
            ->merge(City::query()->whereIn('state_id', $stateIds)->pluck('id'))
            ->merge(City::query()->whereHas('state', fn (Builder $query) => $query->whereIn('country_id', $countryIds))->pluck('id'));
        $cityIds = $cityIds->merge(
            CityPlace::query()->whereKey($idsFor(CityPlace::class))->pluck('city_id')
        );
        $itineraryCityIds = $itineraryItems->flatMap(function (array $item): array {
            return [
                $item['place']?->city_id,
                $item['hotelRoom']?->hotel?->city_id,
            ];
        })->filter();

        return $cityIds->merge($itineraryCityIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function packagePlanItemTitle(PackagePlanItem $item): string
    {
        if ($item->item_type === 'place') {
            return $item->place?->name ?? 'Place';
        }

        if ($item->item_type === 'hotel') {
            return $item->hotelRoom ? $this->hotelRoomTitle($item->hotelRoom) : 'Hotel';
        }

        return $item->packageInclusionItem?->custom_name
            ?? $item->packageInclusionItem?->inclusion?->name
            ?? 'Included service';
    }

    private function hotelRoomTitle(HotelRoom $hotelRoom): string
    {
        return collect([
            $hotelRoom->hotel?->name,
            $hotelRoom->roomType?->name,
            $hotelRoom->name,
        ])->filter()->unique()->implode(' · ');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $itineraryItems
     */
    private function calculateBookingAmount(
        TourPackage $package,
        string $country,
        int $adultCount,
        int $childCount,
        int $studentCount,
        int $extraBedCount,
        Collection $itineraryItems,
        ?array $customPackage = null,
    ): float {
        $hotelTotal = $itineraryItems
            ->where('item_type', 'hotel')
            ->sum(function (array $item) use ($adultCount, $childCount, $extraBedCount): float {
                $room = $item['hotelRoom'];

                if ($room === null) {
                    return 0;
                }

                $childRate = (float) ($room->customer_child_price ?? $room->customer_adult_price ?? 0);
                $nights = (int) ($item['nights'] ?? 1);

                return ((float) ($room->customer_adult_price ?? 0) * $adultCount
                    + $childRate * $childCount
                    + (float) ($room->customer_extra_bed_price ?? 0) * $extraBedCount) * $nights;
            });
        $placeTotal = $itineraryItems->sum(function (array $item) use ($adultCount, $childCount, $studentCount, $country): float {
            $place = $item['place'];
            $adultPlacePrice = $place?->adultPriceForCountry($country) ?? 0;
            $childPlacePrice = $place?->childPrice() ?? 0;
            $studentPlacePrice = $place?->studentPriceForCountry($country) ?? 0;

            return ($adultPlacePrice * ($adultCount - $studentCount))
                + ($childPlacePrice * $childCount)
                + ($studentPlacePrice * $studentCount)
                + (float) ($item['additional_price'] ?? 0);
        });
        $adultBasePrice = (float) ($customPackage['base_price'] ?? $package->base_price);
        $childBasePrice = (float) ($customPackage['child_price'] ?? $package->child_price ?? $package->base_price);
        $subtotal = $adultBasePrice * $adultCount
            + $childBasePrice * $childCount
            + $hotelTotal
            + $placeTotal;
        $discountPercentage = (float) ($customPackage['discount_percentage'] ?? $package->discount_percentage);
        $taxPercentage = (float) ($customPackage['tax_percentage'] ?? $package->tax_percentage);

        return $subtotal
            * (1 - ($discountPercentage / 100))
            * (1 + ($taxPercentage / 100));
    }

    public function indexHotelRequests(): View
    {
        $bookings = Booking::whereNotNull('hotel_room_id')
            ->with(['package', 'hotelRoom.roomType', 'hotelRoom.hotel.city'])
            ->latest('id')
            ->paginate(20);

        return view('admin.hotel-requests.index', compact('bookings'));
    }

    public function sendHotelRequest(Booking $booking): RedirectResponse
    {
        abort_if($booking->hotel_room_id === null, 422, 'This package booking has no hotel room assigned.');
        abort_if($booking->hotel_request_status === 'confirmed', 422, 'This hotel request is already confirmed.');

        $booking->update([
            'hotel_request_status' => 'requested',
            'hotel_booking_requested_at' => now(),
            'hotel_response' => null,
            'hotel_responded_at' => null,
        ]);

        session()->flash('hotel_request_success', true);
        session()->flash('hotel_request_message', "Booking request sent for #{$booking->id} - {$booking->name}");
        session()->flash('hotel_request_room', $booking->hotelRoom->name ?? 'Hotel Room');

        return redirect()->route('admin.hotel-booking-requests.index');
    }
}
