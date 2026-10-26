<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CityPlace;
use App\Models\TourPackage;
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
            ->when($validated['search'] ?? null, fn ($q) => $q->where('name', 'like', '%'.($validated['search'] ?? null).'%'))
            ->when($validated['destination'] ?? null, fn ($q) => $q->whereHas('destinations', fn ($q) => $q->where('destination_id', $validated['destination'] ?? null)))
            ->when($validated['duration'] ?? null, fn ($q) => $q->where('days', $validated['duration'] ?? null))
            ->when($validated['min_price'] ?? null, fn ($q) => $q->where('base_price', '>=', $validated['min_price'] ?? null))
            ->when($validated['max_price'] ?? null, fn ($q) => $q->where('base_price', '<=', $validated['max_price'] ?? null))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $destinations = CityPlace::with(['city.state.country'])->orderBy('name')->get();

        return view('packages.index', compact(
            'packages',
            'destinations',
            'validated'
        ));
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

        $subtotal = (float) $package->base_price + $package->planItems->sum(function ($item): float {
            return (float) ($item->place?->price_per_person ?? 0) + (float) $item->additional_price;
        });
        $discountedTotal = $subtotal * (1 - ((float) $package->discount_percentage / 100));
        $estimatedTotal = $discountedTotal * (1 + ((float) $package->tax_percentage / 100));

        return view('packages.show', compact(
            'package',
            'inclusionDetails',
            'itinerary',
            'subtotal',
            'discountedTotal',
            'estimatedTotal'
        ));
    }
}
