<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PackageIndexRequest;
use App\Models\CityPlace;
use App\Models\TourPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PackageController extends Controller
{
    public function index(PackageIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $packages = TourPackage::query()
            ->where('is_active', true)
            ->with(['destinations.destination', 'galleryImages'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($filters['destination'] ?? null, fn ($query, int $placeId) => $query->whereHas('destinations', fn ($destinationQuery) => $destinationQuery->where('destination_id', $placeId)->where('destination_type', 'App\\Models\\CityPlace')))
            ->when($filters['duration'] ?? null, fn ($query, int $duration) => $query->where('days', $duration))
            ->when($filters['min_price'] ?? null, fn ($query, float $price) => $query->where('base_price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn ($query, float $price) => $query->where('base_price', '<=', $price))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 12));

        return response()->json([
            'success' => true,
            'data' => $packages->getCollection()->map(fn (TourPackage $package): array => $this->packageSummary($package))->values(),
            'links' => [
                'first' => $packages->url(1),
                'last' => $packages->url($packages->lastPage()),
                'prev' => $packages->previousPageUrl(),
                'next' => $packages->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $packages->currentPage(),
                'per_page' => $packages->perPage(),
                'total' => $packages->total(),
                'last_page' => $packages->lastPage(),
            ],
        ]);
    }

    public function show(TourPackage $package): JsonResponse
    {
        abort_unless($package->is_active, 404);

        $package->load(['destinations.destination', 'galleryImages', 'availabilities']);

        return response()->json([
            'success' => true,
            'data' => [...$this->packageSummary($package),
                'infant_price' => $package->infant_price,
                'availability' => $package->availabilities->map(fn ($availability): string => $availability->departure_date->toDateString())->values(),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function packageSummary(TourPackage $package): array
    {
        return [
            'id' => $package->id,
            'name' => $package->name,
            'slug' => $package->slug,
            'category' => $package->category,
            'tour_type' => $package->tour_type,
            'description' => $package->description,
            'departure_city' => $package->departure_city,
            'price' => [
                'adult' => $package->base_price,
                'child' => $package->child_price,
                'discount_percentage' => $package->discount_percentage,
                'tax_percentage' => $package->tax_percentage,
            ],
            'duration' => ['days' => $package->days, 'nights' => $package->nights],
            'image' => $package->image ? asset('storage/'.$package->image) : null,
            'gallery' => $package->galleryImages->map(fn ($image): string => asset('storage/'.$image->image))->values(),
            'destinations' => $package->destinations->map(fn ($destination): ?array => $destination->destination instanceof CityPlace ? [
                'id' => $destination->destination->id,
                'name' => $destination->destination->name,
                'slug' => $destination->destination->slug,
            ] : null)->filter()->values(),
        ];
    }
}
