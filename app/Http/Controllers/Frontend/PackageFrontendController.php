<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CityPlace;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'price' => (float) $place->price_per_person,
            'primary_image' => $place->primary_image ? asset('storage/'.$place->primary_image) : null,
            'secondary_images' => collect($place->secondary_images ?? [])->map(fn (string $path) => asset('storage/'.$path))->values()->all(),
        ])->values()->all();

        $subtotal = (float) $package->base_price + $package->hotelCostPerAdult() + $package->planItems->sum(function ($item): float {
            return (float) ($item->place?->price_per_person ?? 0) + (float) $item->additional_price;
        });
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));
        $estimatedTotal = $discountedTotal * (1 + ((float) $package->tax_percentage / 100));

        return view('packages.show', compact('package', 'inclusionDetails', 'itinerary', 'subtotal', 'discountedTotal', 'estimatedTotal', 'swappablePlaces'));
    }

    public function book(string $slug): View
    {
        $package = TourPackage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $estimatedTotal = $this->calculateBookingTotal($package, 1, 0);

        return view('packages.book', compact('package', 'estimatedTotal'));
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
            'photo_id_proof' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
        ]);

        $booking = Booking::create([...$validated, 'package_id' => $package->id]);
        $booking->load('package');

        return view('packages.book-success', compact('booking'));
    }

    private function calculateBookingTotal(TourPackage $package, int $adultCount, int $childCount): float
    {
        $package->load('planItems.place', 'planItems.hotelRoom');

        $adultPrice = (float) $package->base_price;
        $childPrice = $package->child_price !== null ? (float) $package->child_price : $adultPrice;
        $hotelTotal = $package->planItems->where('item_type', 'hotel')->sum(function ($item) use ($adultCount, $childCount): float {
            $nights = (int) ($item->nights ?? 1);
            $adultRate = (float) ($item->hotelRoom?->customer_adult_price ?? 0);
            $childRate = $item->hotelRoom?->customer_child_price !== null
                ? (float) $item->hotelRoom->customer_child_price
                : $adultRate;

            return (($adultRate * $adultCount) + ($childRate * $childCount)) * $nights;
        });
        $placeTotal = $package->planItems->sum(function ($item) use ($adultCount, $childCount): float {
            return (float) ($item->place?->price_per_person ?? 0) * ($adultCount + $childCount) + (float) $item->additional_price;
        });
        $subtotal = ($adultPrice * $adultCount) + ($childPrice * $childCount) + $hotelTotal + $placeTotal;
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));

        return $discountedTotal * (1 + ((float) $package->tax_percentage / 100));
    }
}
