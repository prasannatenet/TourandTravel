<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CityIndexRequest;
use App\Http\Requests\Api\CountryIndexRequest;
use App\Http\Requests\Api\StateIndexRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function countries(CountryIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $countries = Country::query()
            ->withCount('states')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 12));

        return $this->paginatedResponse($countries, fn (Country $country): array => $this->countryData($country));
    }

    public function country(Country $country): JsonResponse
    {
        $country->loadCount('states');

        return response()->json(['success' => true, 'data' => $this->countryData($country)]);
    }

    public function states(StateIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $states = State::query()
            ->with('country')
            ->withCount('cities')
            ->when($filters['country_id'] ?? null, fn ($query, int $countryId) => $query->where('country_id', $countryId))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 12));

        return $this->paginatedResponse($states, fn (State $state): array => $this->stateData($state));
    }

    public function state(State $state): JsonResponse
    {
        $state->load('country')->loadCount('cities');

        return response()->json(['success' => true, 'data' => $this->stateData($state)]);
    }

    public function cities(CityIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $cities = City::query()
            ->with('state.country')
            ->withCount('places')
            ->when($filters['state_id'] ?? null, fn ($query, int $stateId) => $query->where('state_id', $stateId))
            ->when($filters['country_id'] ?? null, fn ($query, int $countryId) => $query->whereHas('state', fn ($stateQuery) => $stateQuery->where('country_id', $countryId)))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 12));

        return $this->paginatedResponse($cities, fn (City $city): array => $this->cityData($city));
    }

    public function city(City $city): JsonResponse
    {
        $city->load('state.country')->loadCount('places');

        return response()->json(['success' => true, 'data' => $this->cityData($city)]);
    }

    private function paginatedResponse(LengthAwarePaginator $records, callable $serialize): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $records->getCollection()->map($serialize)->values(),
            'links' => [
                'first' => $records->url(1),
                'last' => $records->url($records->lastPage()),
                'prev' => $records->previousPageUrl(),
                'next' => $records->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'last_page' => $records->lastPage(),
            ],
        ]);
    }

    /** @return array{id: int, name: string, states_count: int} */
    private function countryData(Country $country): array
    {
        return [
            'id' => $country->id,
            'name' => $country->name,
            'states_count' => (int) $country->states_count,
        ];
    }

    /** @return array{id: int, name: string, price_per_day: string|null, cities_count: int, country: array{id: int, name: string}} */
    private function stateData(State $state): array
    {
        return [
            'id' => $state->id,
            'name' => $state->name,
            'price_per_day' => $state->price_per_day,
            'cities_count' => (int) $state->cities_count,
            'country' => [
                'id' => $state->country->id,
                'name' => $state->country->name,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function cityData(City $city): array
    {
        return [
            'id' => $city->id,
            'name' => $city->name,
            'slug' => $city->slug,
            'description' => $city->short_description,
            'images' => [
                'primary' => $city->primary_image ? $city->primaryImageUrl() : null,
                'secondary' => $city->secondaryImageUrls(),
            ],
            'places_count' => (int) $city->places_count,
            'state' => [
                'id' => $city->state->id,
                'name' => $city->state->name,
                'country' => [
                    'id' => $city->state->country->id,
                    'name' => $city->state->country->name,
                ],
            ],
        ];
    }
}
