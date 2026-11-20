<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CityPlace;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PackageFrontendController extends Controller
{
    public function index(Request $request): View
    {
        $validated = array_merge([
            'search' => null,
            'destination' => null,
            'duration' => null,
            'min_price' => null,
            'max_price' => null,
        ], $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'destination' => ['nullable', 'integer', 'exists:city_places,id'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:99'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ]));

        $packages = TourPackage::where('is_active', true)
            ->with(['destinations.destination', 'galleryImages'])
            ->when($validated['search'] ?? null, fn ($query) => $query->where('name', 'like', '%'.($validated['search'] ?? null).'%'))
            ->when($validated['destination'] ?? null, fn ($query) => $query->whereHas('destinations', fn ($query) => $query->where('destination_id', $validated['destination'] ?? null)))
            ->when($validated['duration'] ?? null, fn ($query) => $query->where('days', $validated['duration'] ?? null))
            ->when($validated['min_price'] ?? null, fn ($query) => $query->where('base_price', '>=', $validated['min_price'] ?? null))
            ->when($validated['max_price'] ?? null, fn ($query) => $query->where('base_price', '<=', $validated['max_price'] ?? null))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $destinations = CityPlace::with(['city.state.country'])->orderBy('name')->get();

        return view('packages.index', compact('packages', 'destinations', 'validated'));
    }

    public function show(string $slug): View
    {
        $package = TourPackage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $package->load([
            'destinations.destination',
            'galleryImages',
            'availabilities',
            'inclusionItems.inclusion',
            'planItems.place.city.state.country',
            'planItems.packageInclusionItem.inclusion',
            'planItems.hotelRoom.hotel.city',
            'planItems.hotelRoom.roomType',
        ]);

        $inclusionDetails = $package->inclusionItems->mapWithKeys(function ($item): array {
            $inclusion = $item->inclusion;
            $hasCustomization = (bool) $item->customized;

            return [$item->id => [
                'name' => $hasCustomization ? $item->custom_name : $inclusion->name,
                'place_from' => $hasCustomization ? $item->custom_place_from : null,
                'place_to' => $hasCustomization ? $item->custom_place_to : null,
                'description' => $hasCustomization ? $item->custom_description : $inclusion->description,
                'duration_hours' => $hasCustomization ? $item->custom_duration_hours : null,
                'image_one' => $hasCustomization && $item->custom_image_one ? $item->custom_image_one : $inclusion->image_one,
                'image_two' => $hasCustomization && $item->custom_image_two ? $item->custom_image_two : $inclusion->image_two,
            ]];
        });

        $itinerary = $package->planItems->groupBy('day_number');
        $planCityIds = $package->planItems
            ->filter(fn ($item) => $item->city_place_id && $item->place)
            ->map(fn ($item) => $item->place->city_id)
            ->unique()
            ->values();
        $destCityIds = $package->destinations
            ->map(fn ($destination) => $destination->destination?->city_id)
            ->filter()
            ->unique()
            ->values();
        $allRelevantCityIds = $planCityIds->merge($destCityIds)->unique()->values();

        $placesQuery = CityPlace::with('city.state');
        if ($allRelevantCityIds->isNotEmpty()) {
            $placesQuery->whereIn('city_id', $allRelevantCityIds);
        }
        $placesCollection = $placesQuery->get();
        if ($placesCollection->count() <= 1) {
            $placesCollection = CityPlace::with('city.state')->get();
        }

        $swappablePlaces = $placesCollection->map(fn (CityPlace $place): array => [
            'id' => $place->id,
            'name' => $place->name,
            'city_id' => $place->city_id,
            'city_name' => $place->city->name ?? '',
            'description' => $place->place_description ?? $place->description ?? '',
            'price' => $place->adultPriceForCountry('India'),
            'adult_india' => $place->adultPriceForCountry('India'),
            'adult_foreigner' => $place->adultPriceForCountry('USA'),
            'child_price' => $place->childPrice(),
            'student_india' => $place->studentPriceForCountry('India'),
            'student_foreigner' => $place->studentPriceForCountry('USA'),
            'state_id' => (int) ($place->city?->state?->id ?? 0),
            'state_daily_price' => (float) ($place->city?->state?->price_per_day ?? 0),
            'primary_image' => $place->primary_image ? asset('storage/'.$place->primary_image) : null,
            'secondary_images' => collect($place->secondary_images ?? [])->map(fn (string $path) => asset('storage/'.$path))->values()->all(),
        ])->values()->all();

        $subtotal = (float) $package->base_price + $package->hotelCostPerAdult() + $package->stateDailyCostPerAdult() + $package->planItems->sum(function ($item): float {
            return (float) ($item->place?->adultPriceForCountry('India') ?? 0) + (float) $item->additional_price;
        });
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));
        $estimatedTotal = $discountedTotal * (1 + ((float) $package->tax_percentage / 100));

        return view('packages.show', compact('package', 'inclusionDetails', 'itinerary', 'subtotal', 'discountedTotal', 'estimatedTotal', 'swappablePlaces'));
    }

    public function book(string $slug): View
    {
        $package = TourPackage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $package->load(['planItems.place.city.state', 'planItems.hotelRoom.hotel.city', 'availabilities']);
        $suggestedRoomCount = Booking::suggestedRoomCount(1, 0);
        $estimatedTotal = $this->calculateBookingTotal($package, 1, 0, 0, 0, 'India');

        return view('packages.book', compact('package', 'estimatedTotal', 'suggestedRoomCount'));
    }

    public function storeBook(Request $request, string $slug): View|RedirectResponse
    {
        $package = TourPackage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'adult_count' => ['required', 'integer', 'min:1', 'max:99'],
            'child_count' => ['required', 'integer', 'min:0', 'max:99'],
            'student_count' => ['required', 'integer', 'min:0', 'lte:adult_count'],
            'photo_id_proof' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'extra_bed_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'room_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'departure_date' => ['nullable', 'date'],
            'itinerary' => ['nullable', 'json', 'max:50000'],
        ]);

        $hotelRoomId = null;
        $hotelCheckInDay = null;
        $hotelCheckInNights = 0;

        $package->load(['planItems.place.city.state', 'planItems.hotelRoom', 'availabilities']);
        $itineraryOverride = null;
        if (! empty($validated['itinerary'])) {
            $requestedItinerary = json_decode($validated['itinerary'], true, 512, JSON_THROW_ON_ERROR);
            $itineraryOverride = $this->normalizeCustomerItinerary($package, $requestedItinerary);
        }
        if ($package->availabilities->isNotEmpty()) {
            $request->validate([
                'departure_date' => ['required', 'date', 'in:'.implode(',', $package->availabilities->map(fn ($availability): string => $availability->departure_date->format('Y-m-d'))->all())],
            ]);
        }
        foreach ($package->planItems->where('item_type', 'hotel') as $planItem) {
            if ($planItem->hotelRoom) {
                if ($hotelRoomId === null) {
                    $hotelRoomId = $planItem->hotelRoom->id;
                    $hotelCheckInDay = (int) $planItem->day_number;
                }

                if ($planItem->hotelRoom->id === $hotelRoomId) {
                    $hotelCheckInNights += (int) ($planItem->nights ?? 1);
                }
            }
        }

        $suggestedRoomCount = Booking::suggestedRoomCount((int) $validated['adult_count'], (int) $validated['child_count']);
        $validated['room_count'] = (int) ($validated['room_count'] ?? $suggestedRoomCount);
        abort_if($validated['room_count'] > 20, 422, 'The room count cannot exceed 20.');
        $requiredExtraBeds = Booking::requiredExtraBedCount(
            (int) $validated['adult_count'],
            (int) $validated['child_count'],
            $validated['room_count'],
        );
        $validated['extra_bed_count'] = (int) ($validated['extra_bed_count'] ?? $requiredExtraBeds);
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

        $checkInDate = $validated['departure_date'] ?? $package->availabilities->first()?->departure_date?->toDateString();
        if ($hotelRoomId !== null) {
            if ($checkInDate === null) {
                return back()->withErrors([
                    'package' => 'This package does not have a departure date configured yet. Please contact the travel team.',
                ])->withInput();
            }

            $validated['check_in_date'] = Carbon::parse($checkInDate)->addDays(max(0, ($hotelCheckInDay ?? 1) - 1))->toDateString();
            $validated['check_out_date'] = Carbon::parse($validated['check_in_date'])
                ->addDays(max(1, $hotelCheckInNights))->toDateString();
        } else {
            $validated['check_in_date'] = $checkInDate?->toDateString();
        }

        $booking = Booking::create([
            ...$validated,
            'package_id' => $package->id,
            'hotel_room_id' => $hotelRoomId,
            'hotel_request_status' => 'pending',
            'itinerary_override' => $itineraryOverride,
        ]);
        $booking->amount = $this->calculateBookingTotal(
            $package,
            (int) $validated['adult_count'],
            (int) $validated['child_count'],
            (int) $validated['student_count'],
            (int) $validated['extra_bed_count'],
            $validated['country'],
            $itineraryOverride,
        );
        $booking->save();

        return redirect()->route('packages.book', $package->slug)->with('booking_confirmation', [
            'id' => $booking->id,
            'name' => $booking->name,
            'email' => $booking->email,
            'mobile' => $booking->mobile,
            'amount' => (float) $booking->amount,
        ]);
    }

    private function normalizeCustomerItinerary(TourPackage $package, array $requestedItinerary): array
    {
        $validated = Validator::make($requestedItinerary, [
            'items' => ['present', 'array', 'max:200'],
            'items.*.plan_item_id' => ['nullable', 'integer'],
            'items.*.city_place_id' => ['required', 'integer', 'exists:city_places,id'],
            'items.*.day_number' => ['required', 'integer', 'min:1', 'max:'.$package->days],
            'items.*.position' => ['nullable', 'integer', 'min:0', 'max:999'],
        ])->validate();

        $planItems = $package->planItems;
        $placeActivities = $planItems->filter(fn ($item): bool => $item->item_type === 'place'
            && $item->package_inclusion_package_id === null
            && $item->place !== null)->keyBy('id');
        $dayCityIds = [];
        foreach ($placeActivities as $planItem) {
            $dayCityIds[(int) $planItem->day_number] ??= (int) $planItem->place->city_id;
        }
        foreach ($planItems->where('item_type', 'hotel') as $planItem) {
            $hotelCityId = $planItem->hotelRoom?->hotel?->city_id;
            if ($hotelCityId && ! isset($dayCityIds[(int) $planItem->day_number])) {
                $dayCityIds[(int) $planItem->day_number] = (int) $hotelCityId;
            }
        }

        $requestedRows = collect($validated['items']);
        $placeIds = $requestedRows->pluck('city_place_id')->map(fn ($id): int => (int) $id)->unique();
        $places = CityPlace::query()->with('city')->whereKey($placeIds)->get()->keyBy('id');
        $seenDayPlaces = [];
        $seenPlanItems = [];
        $itineraryItems = $requestedRows->map(function (array $row) use ($placeActivities, $places, $dayCityIds, &$seenDayPlaces, &$seenPlanItems): array {
            $dayNumber = (int) $row['day_number'];
            $place = $places->get((int) $row['city_place_id']);
            $planItem = isset($row['plan_item_id']) ? $placeActivities->get((int) $row['plan_item_id']) : null;

            abort_if(isset($row['plan_item_id']) && $planItem === null, 422, 'An itinerary activity does not belong to this package.');
            abort_if($planItem && (int) $planItem->day_number !== $dayNumber, 422, 'An activity must remain on its original itinerary day.');
            abort_if($planItem && isset($seenPlanItems[$planItem->id]), 422, 'An itinerary activity cannot be submitted more than once.');
            abort_if(! isset($dayCityIds[$dayNumber]) || (int) $place->city_id !== $dayCityIds[$dayNumber], 422, 'Choose an activity in the same city as the selected itinerary day.');

            if ($planItem) {
                $seenPlanItems[$planItem->id] = true;
            }

            $uniqueKey = $dayNumber.'-'.$place->id;
            abort_if(isset($seenDayPlaces[$uniqueKey]), 422, 'An activity can only appear once in each day.');
            $seenDayPlaces[$uniqueKey] = true;

            return [
                'item_type' => 'place',
                'day_number' => $dayNumber,
                'start_time' => $planItem ? substr((string) $planItem->start_time, 0, 5) : '',
                'nights' => null,
                'city_place_id' => (int) $place->id,
                'hotel_room_id' => null,
                'package_inclusion_item_id' => null,
                'package_inclusion_id' => null,
                'additional_price' => (float) ($planItem?->additional_price ?? 0),
                'position' => (int) ($row['position'] ?? 0),
                'title' => $place->name,
            ];
        })->all();

        foreach ($planItems->filter(fn ($item): bool => $item->item_type !== 'place' || $item->package_inclusion_package_id !== null) as $planItem) {
            $isInclusion = $planItem->package_inclusion_package_id !== null;
            $itineraryItems[] = [
                'item_type' => $isInclusion ? 'inclusion' : $planItem->item_type,
                'day_number' => (int) $planItem->day_number,
                'start_time' => substr((string) $planItem->start_time, 0, 5),
                'nights' => $planItem->nights === null ? null : (int) $planItem->nights,
                'city_place_id' => $isInclusion ? null : ($planItem->city_place_id === null ? null : (int) $planItem->city_place_id),
                'hotel_room_id' => $planItem->hotel_room_id === null ? null : (int) $planItem->hotel_room_id,
                'package_inclusion_item_id' => $planItem->package_inclusion_package_id === null ? null : (int) $planItem->package_inclusion_package_id,
                'package_inclusion_id' => $planItem->package_inclusion_id === null ? null : (int) $planItem->package_inclusion_id,
                'additional_price' => (float) $planItem->additional_price,
                'position' => (int) $planItem->position,
                'title' => null,
            ];
        }

        return [
            'days' => (int) $package->days,
            'items' => $itineraryItems,
        ];
    }

    private function calculateBookingTotal(TourPackage $package, int $adultCount, int $childCount, int $studentCount, int $extraBedCount, string $country, ?array $itineraryOverride = null): float
    {
        $package->load('planItems.place.city.state', 'planItems.hotelRoom');

        $adultPrice = (float) $package->base_price;
        $childPrice = $package->child_price !== null ? (float) $package->child_price : $adultPrice;
        $hotelTotal = $package->planItems->where('item_type', 'hotel')->sum(function ($item) use ($adultCount, $childCount, $extraBedCount): float {
            $nights = (int) ($item->nights ?? 1);
            $adultRate = (float) ($item->hotelRoom?->customer_adult_price ?? 0);
            $childRate = $item->hotelRoom?->customer_child_price !== null
                ? (float) $item->hotelRoom->customer_child_price
                : $adultRate;
            $extraBedRate = (float) ($item->hotelRoom?->customer_extra_bed_price ?? 0);

            return (($adultRate * $adultCount) + ($childRate * $childCount) + ($extraBedRate * $extraBedCount)) * $nights;
        });
        if ($itineraryOverride !== null) {
            $snapshotItems = collect($itineraryOverride['items'] ?? []);
            $placeMap = CityPlace::query()
                ->with('city.state')
                ->whereKey($snapshotItems->pluck('city_place_id')->filter()->unique())
                ->get()
                ->keyBy('id');
            $placeTotal = $snapshotItems->sum(function (array $item) use ($adultCount, $childCount, $studentCount, $country, $placeMap): float {
                $place = $placeMap->get((int) ($item['city_place_id'] ?? 0));
                $adultPlacePrice = $place?->adultPriceForCountry($country) ?? 0;
                $childPlacePrice = $place?->childPrice() ?? 0;
                $studentPlacePrice = $place?->studentPriceForCountry($country) ?? 0;

                return ($adultPlacePrice * max(0, $adultCount - $studentCount))
                    + ($childPlacePrice * $childCount)
                    + ($studentPlacePrice * $studentCount)
                    + (float) ($item['additional_price'] ?? 0);
            });
        } else {
            $placeTotal = $package->planItems->sum(function ($item) use ($adultCount, $childCount, $studentCount, $country): float {
                $place = $item->place;
                $adultPlacePrice = $place?->adultPriceForCountry($country) ?? 0;
                $childPlacePrice = $place?->childPrice() ?? 0;
                $studentPlacePrice = $place?->studentPriceForCountry($country) ?? 0;

                return ($adultPlacePrice * max(0, $adultCount - $studentCount))
                    + ($childPlacePrice * $childCount)
                    + ($studentPlacePrice * $studentCount)
                    + (float) $item->additional_price;
            });
        }
        if ($itineraryOverride !== null) {
            $stateDailyTotalPerAdult = collect($itineraryOverride['items'] ?? [])
                ->filter(fn (array $item): bool => $item['item_type'] === 'place')
                ->unique(fn (array $item): string => $item['day_number'].'-'.($placeMap->get((int) $item['city_place_id'])?->city?->state?->id ?? 0))
                ->sum(fn (array $item): float => (float) ($placeMap->get((int) $item['city_place_id'])?->city?->state?->price_per_day ?? 0));
        } else {
            $stateDailyTotalPerAdult = $package->stateDailyCostPerAdult();
        }
        $stateDailyTotal = $stateDailyTotalPerAdult * ($adultCount + $childCount);
        $subtotal = ($adultPrice * $adultCount) + ($childPrice * $childCount) + $hotelTotal + $placeTotal + $stateDailyTotal;
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));

        return $discountedTotal * (1 + ((float) $package->tax_percentage / 100));
    }
}
