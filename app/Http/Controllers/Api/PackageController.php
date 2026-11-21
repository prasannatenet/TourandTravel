<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PackageIndexRequest;
use App\Models\CityPlace;
use App\Models\PackagePlanItem;
use App\Models\TourPackage;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    public function index(PackageIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $packages = TourPackage::query()
            ->where('is_active', true)
            ->with($this->packageRelations())
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

        $package->load($this->packageRelations());

        return response()->json([
            'success' => true,
            'data' => $this->packageSummary($package),
        ]);
    }

    /** @return array<string, mixed> */
    private function packageSummary(TourPackage $package): array
    {
        $basePrice = (float) $package->base_price;
        $hotelCost = $package->hotelCostPerAdult();
        $stateDailyCost = $package->stateDailyCostPerAdult();
        $itineraryCost = $package->planItems->sum(fn ($item): float => (float) ($item->place?->adultPriceForCountry('India') ?? 0)
            + (float) $item->additional_price);
        $preDiscountTotal = $basePrice + $hotelCost + $stateDailyCost + $itineraryCost;
        $discountPercentage = (float) ($package->discount_percentage ?? 0);
        $discountAmount = $preDiscountTotal * ($discountPercentage / 100);
        $discountedTotal = $preDiscountTotal - $discountAmount;
        $taxPercentage = (float) ($package->tax_percentage ?? 0);
        $taxAmount = $discountedTotal * ($taxPercentage / 100);
        $estimatedTotal = $discountedTotal + $taxAmount;

        return [
            'id' => $package->id,
            'name' => $package->name,
            'slug' => $package->slug,
            'category' => $package->category,
            'tour_type' => $package->tour_type,
            'description' => $package->description,
            'departure_city' => $package->departure_city,
            'price' => [
                'currency' => 'INR',
                'adult' => [
                    'base_price' => $basePrice,
                    'hotel_cost' => $hotelCost,
                    'state_daily_cost' => $stateDailyCost,
                    'itinerary_cost' => $itineraryCost,
                    'pre_discount_total' => $preDiscountTotal,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                    'discounted_total' => $discountedTotal,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'estimated_total' => $estimatedTotal,
                ],
                'child' => $package->child_price,
                'infant' => $package->infant_price,
            ],
            'duration' => ['days' => $package->days, 'nights' => $package->nights],
            'image' => $package->image ? asset('storage/'.$package->image) : null,
            'video' => $package->video ? (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|.*[?&]v=)|youtu\.be\/)/', $package->video) ? $package->video : asset('storage/'.$package->video)) : null,
            'gallery' => $package->galleryImages->map(fn ($image): string => asset('storage/'.$image->image))->values(),
            'views' => $package->views,
            'availability' => $package->availabilities->map(fn ($availability): array => [
                'departure_date' => $availability->departure_date->toDateString(),
                'seat_limit' => $availability->seat_limit,
                'adult_price_override' => $availability->adult_price_override,
            ])->values(),
            'destinations' => $package->destinations
                ->pluck('destination')
                ->filter(fn ($destination): bool => $destination instanceof CityPlace)
                ->map(fn (CityPlace $place): array => $this->placeData($place))
                ->values(),
            'places' => $package->destinations
                ->pluck('destination')
                ->merge($package->planItems->pluck('place'))
                ->filter(fn ($place): bool => $place instanceof CityPlace)
                ->unique('id')
                ->map(fn (CityPlace $place): array => $this->placeData($place))
                ->values(),
            'itinerary' => $package->planItems
                ->groupBy('day_number')
                ->map(fn ($items, int $dayNumber): array => [
                    'day' => $dayNumber,
                    'stops' => $items->map(fn ($item): array => $this->planItemData($item))->values(),
                ])
                ->values(),
        ];
    }

    /** @return array<int, string> */
    private function packageRelations(): array
    {
        return [
            'destinations.destination',
            'galleryImages',
            'availabilities',
            'planItems.place.city.state.country',
            'planItems.hotelRoom.hotel.city',
            'planItems.hotelRoom.roomType',
            'planItems.packageInclusion',
            'planItems.packageInclusionItem.inclusion',
        ];
    }

    /** @return array<string, mixed> */
    private function planItemData(PackagePlanItem $item): array
    {
        $inclusion = $item->packageInclusionItem?->inclusion ?? $item->packageInclusion;
        $customInclusion = $item->packageInclusionItem;

        return [
            'id' => $item->id,
            'type' => $item->item_type,
            'start_time' => $item->start_time,
            'additional_price' => $item->additional_price,
            'nights' => $item->nights,
            'place' => $item->place ? $this->placeData($item->place) : null,
            'hotel' => $item->hotelRoom ? [
                'id' => $item->hotelRoom->hotel?->id,
                'name' => $item->hotelRoom->hotel?->name,
                'city' => $item->hotelRoom->hotel?->city?->name,
                'room' => [
                    'id' => $item->hotelRoom->id,
                    'name' => $item->hotelRoom->name,
                    'type' => $item->hotelRoom->roomType?->name,
                    'description' => $item->hotelRoom->description,
                    'images' => collect($item->hotelRoom->images ?? [])->map(fn (string $image): string => asset('storage/'.$image))->values(),
                    'adult_price' => $item->hotelRoom->customer_adult_price,
                    'child_price' => $item->hotelRoom->customer_child_price,
                    'extra_bed_price' => $item->hotelRoom->customer_extra_bed_price,
                ],
            ] : null,
            'inclusion' => $inclusion ? [
                'name' => $customInclusion?->customized ? $customInclusion->custom_name : $inclusion->name,
                'place_from' => $customInclusion?->customized ? $customInclusion->custom_place_from : $inclusion->place_from,
                'place_to' => $customInclusion?->customized ? $customInclusion->custom_place_to : $inclusion->place_to,
                'description' => $customInclusion?->customized ? $customInclusion->custom_description : $inclusion->description,
                'scheduled_time' => $customInclusion?->customized ? $customInclusion->custom_scheduled_time : $inclusion->scheduled_time,
                'duration_hours' => $customInclusion?->custom_duration_hours,
                'image_one' => $customInclusion?->customized && $customInclusion->custom_image_one
                    ? asset('storage/'.$customInclusion->custom_image_one)
                    : ($inclusion->image_one ? asset('storage/'.$inclusion->image_one) : null),
                'image_two' => $customInclusion?->customized && $customInclusion->custom_image_two
                    ? asset('storage/'.$customInclusion->custom_image_two)
                    : ($inclusion->image_two ? asset('storage/'.$inclusion->image_two) : null),
            ] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function placeData(CityPlace $place): array
    {
        return [
            'id' => $place->id,
            'name' => $place->name,
            'slug' => $place->slug,
            'place_description' => $place->place_description,
            'description' => $place->description,
            'city' => [
                'id' => $place->city->id,
                'name' => $place->city->name,
                'state' => $place->city->state->name,
                'country' => $place->city->state->country->name,
            ],
            'images' => [
                'primary' => $place->primary_image ? asset('storage/'.$place->primary_image) : null,
                'secondary' => $place->secondaryImageUrls(),
            ],
            'visit_duration_hours' => $place->visit_duration_hours,
            'best_time_to_visit' => $place->best_time_to_visit,
            'prices' => [
                'per_person' => $place->price_per_person,
                'child' => $place->price_child,
                'indian' => $place->price_indian,
                'foreigner' => $place->price_foreigner,
                'student' => $place->price_student,
            ],
            'note' => $place->note,
        ];
    }
}
