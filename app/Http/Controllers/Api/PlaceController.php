<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlaceIndexRequest;
use App\Models\CityPlace;
use Illuminate\Http\JsonResponse;

class PlaceController extends Controller
{
    public function index(PlaceIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $places = CityPlace::query()
            ->with('city.state.country')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($filters['city_id'] ?? null, fn ($query, int $cityId) => $query->where('city_id', $cityId))
            ->when($filters['best_time_to_visit'] ?? null, fn ($query, string $time) => $query->where('best_time_to_visit', $time))
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 12));

        return response()->json([
            'success' => true,
            'data' => $places->getCollection()->map(fn (CityPlace $place): array => $this->placeData($place))->values(),
            'links' => [
                'first' => $places->url(1),
                'last' => $places->url($places->lastPage()),
                'prev' => $places->previousPageUrl(),
                'next' => $places->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $places->currentPage(),
                'per_page' => $places->perPage(),
                'total' => $places->total(),
                'last_page' => $places->lastPage(),
            ],
        ]);
    }

    public function show(CityPlace $place): JsonResponse
    {
        $place->load('city.state.country');

        return response()->json([
            'success' => true,
            'data' => $this->placeData($place),
        ]);
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
