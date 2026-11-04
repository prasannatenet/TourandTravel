<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CityPlace;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $placesQuery = CityPlace::with('city');
        if ($allRelevantCityIds->isNotEmpty()) {
            $placesQuery->whereIn('city_id', $allRelevantCityIds);
        }
        $placesCollection = $placesQuery->get();
        if ($placesCollection->count() <= 1) {
            $placesCollection = CityPlace::with('city')->get();
        }

        $swappablePlaces = $placesCollection->map(fn (CityPlace $place): array => [
            'id' => $place->id,
            'name' => $place->name,
            'city_id' => $place->city_id,
            'city_name' => $place->city->name ?? '',
            'description' => $place->place_description ?? $place->description ?? '',
            'price' => $place->adultPriceForCountry('India'),
            'primary_image' => $place->primary_image ? asset('storage/'.$place->primary_image) : null,
            'secondary_images' => collect($place->secondary_images ?? [])->map(fn (string $path) => asset('storage/'.$path))->values()->all(),
        ])->values()->all();

        $subtotal = (float) $package->base_price + $package->hotelCostPerAdult() + $package->planItems->sum(function ($item): float {
            return (float) ($item->place?->adultPriceForCountry('India') ?? 0) + (float) $item->additional_price;
        });
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));
        $estimatedTotal = $discountedTotal * (1 + ((float) $package->tax_percentage / 100));

        return view('packages.show', compact('package', 'inclusionDetails', 'itinerary', 'subtotal', 'discountedTotal', 'estimatedTotal', 'swappablePlaces'));
    }

    public function book(string $slug): View
    {
        $package = TourPackage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $package->load(['planItems.place', 'planItems.hotelRoom', 'availabilities']);
        $suggestedRoomCount = Booking::suggestedRoomCount(2, 1);
        $estimatedTotal = $this->calculateBookingTotal($package, 2, 1, 0, 0, 'India');

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
        ]);

        $hotelRoomId = null;
        $hotelCheckInDay = null;
        $hotelCheckInNights = 0;

        $package->load(['planItems.hotelRoom', 'availabilities']);
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
        if ($hotelRoomId === null) {
            $validated['extra_bed_count'] = 0;
        } else {
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
        }

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
        ]);
        $booking->amount = $this->calculateBookingTotal(
            $package,
            (int) $validated['adult_count'],
            (int) $validated['child_count'],
            (int) $validated['student_count'],
            (int) $validated['extra_bed_count'],
            $validated['country'],
        );
        $booking->save();
        $booking->load('package');

        return view('packages.book-success', compact('booking'));
    }

    private function calculateBookingTotal(TourPackage $package, int $adultCount, int $childCount, int $studentCount, int $extraBedCount, string $country): float
    {
        $package->load('planItems.place', 'planItems.hotelRoom');

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
        $subtotal = ($adultPrice * $adultCount) + ($childPrice * $childCount) + $hotelTotal + $placeTotal;
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));

        return $discountedTotal * (1 + ((float) $package->tax_percentage / 100));
    }
}
