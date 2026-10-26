<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CityPlaceController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'best_time_to_visit' => ['nullable', Rule::in(CityPlace::BEST_TIMES)],
        ]);

        $search = $validated['search'] ?? null;
        $countryId = $validated['country_id'] ?? null;
        $stateId = $validated['state_id'] ?? null;
        $cityId = $validated['city_id'] ?? null;
        $bestTime = $validated['best_time_to_visit'] ?? null;

        // Guard against tampered chains: state must belong to country, city to state.
        $state = $stateId ? State::find($stateId) : null;
        if ($state && $countryId && (int) $state->country_id !== (int) $countryId) {
            $stateId = null;
        }
        $city = $cityId ? City::find($cityId) : null;
        if ($city && $stateId && (int) $city->state_id !== (int) $stateId) {
            $cityId = null;
        }

        $places = CityPlace::with(['city.state.country'])
            ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($cityId, fn ($query) => $query->where('city_id', $cityId))
            ->when($stateId && ! $cityId, fn ($query) => $query->whereHas('city', fn ($q) => $q->where('state_id', $stateId)))
            ->when($countryId && ! $stateId && ! $cityId, fn ($query) => $query->whereHas('city.state', fn ($q) => $q->where('country_id', $countryId)))
            ->when($bestTime, fn ($query) => $query->where('best_time_to_visit', $bestTime))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $countries = Country::orderBy('name')->get();
        $states = State::with('country')
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->orderBy('name')
            ->get();
        $cities = City::with('state')
            ->when($stateId, fn ($query) => $query->where('state_id', $stateId))
            ->orderBy('name')
            ->get();

        $hasFilters = $search || $countryId || $stateId || $cityId || $bestTime;

        // Count of places per city for the sidebar summary column.
        $cityPlaceCounts = CityPlace::select('city_id', \DB::raw('count(*) as total'))
            ->groupBy('city_id')
            ->pluck('total', 'city_id')
            ->toArray();

        // Group places by city so each city is shown only once with its total count.
        $groupedByCity = [];
        foreach ($places as $place) {
            $cityId = $place->city_id;
            if (! isset($groupedByCity[$cityId])) {
                $groupedByCity[$cityId] = [
                    'city' => $place->city,
                    'state' => $place->city->state,
                    'country' => $place->city->state->country,
                    'count' => 0,
                ];
            }
            $groupedByCity[$cityId]['count']++;
        }

        // Total number of cities that have at least one place.
        $cityPlaceCountsTotal = CityPlace::select('city_id')->distinct()->count('city_id');

        return view('admin.destinations.places.index', compact('places', 'countries', 'states', 'cities', 'search', 'countryId', 'stateId', 'cityId', 'bestTime', 'hasFilters', 'cityPlaceCounts', 'groupedByCity', 'cityPlaceCountsTotal'));
    }

    public function cityPlaces(City $city): View
    {
        $places = $city->places()
            ->with(['city.state.country'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.destinations.places.city.index', compact('city', 'places'));
    }

    public function show(CityPlace $place): View
    {
        $place->load(['city.state.country']);

        return view('admin.destinations.places.show', compact('place'));
    }

    public function statesByCountry(Country $country): JsonResponse
    {
        return response()->json(
            $country->states()->orderBy('name')->get(['id', 'name'])
        );
    }

    public function citiesByState(State $state): JsonResponse
    {
        return response()->json(
            $state->cities()->orderBy('name')->get(['id', 'name'])
        );
    }

    public function create(Request $request): View
    {
        $countries = Country::orderBy('name')->get();
        $city = City::with(['state.country'])->find($request->query('city_id'));
        $returnToCityId = $city?->id;

        return view('admin.destinations.places.create', compact('countries', 'city', 'returnToCityId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['required', 'exists:states,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'return_to_city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:150'],
            'place_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'primary_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'secondary_images' => ['nullable', 'array', 'max:8'],
            'secondary_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'visit_duration_hours' => ['required', 'numeric', 'min:0.5', 'max:72'],
            'best_time_to_visit' => ['required', Rule::in(CityPlace::BEST_TIMES)],
            'price_per_person' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $chainError = $this->validateCascadeChain((int) $validated['country_id'], (int) $validated['state_id'], (int) $validated['city_id']);

        if ($chainError !== null) {
            return back()->withErrors(['city_id' => $chainError])->withInput();
        }

        $returnToCityId = $validated['return_to_city_id'] ?? null;
        unset($validated['country_id'], $validated['state_id'], $validated['return_to_city_id']);

        if (CityPlace::where('city_id', $validated['city_id'])->where('name', $validated['name'])->exists()) {
            return back()->withErrors(['name' => 'This place already exists in the selected city.'])->withInput();
        }

        $validated['primary_image'] = $request->file('primary_image')->store('places/primary', 'public');
        $secondary = [];
        if ($request->hasFile('secondary_images')) {
            foreach ($request->file('secondary_images') as $img) {
                $secondary[] = $img->store('places/secondary', 'public');
            }
        }
        $validated['secondary_images'] = $secondary;

        CityPlace::create($validated);

        if ($returnToCityId !== null && (int) $returnToCityId === (int) $validated['city_id']) {
            return redirect()->route('admin.places.city.index', ['city' => $returnToCityId])
                ->with('success', 'Place created successfully.');
        }

        return redirect()->route('admin.places.index')->with('success', 'Place created successfully.');
    }

    public function edit(CityPlace $place): View
    {
        $place->load(['city.state.country']);
        $countries = Country::orderBy('name')->get();
        $states = State::where('country_id', $place->city->state->country_id)->orderBy('name')->get();
        $cities = City::where('state_id', $place->city->state_id)->orderBy('name')->get();

        return view('admin.destinations.places.edit', compact('place', 'countries', 'states', 'cities'));
    }

    public function update(Request $request, CityPlace $place): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['required', 'exists:states,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:150'],
            'place_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'secondary_images' => ['nullable', 'array', 'max:8'],
            'secondary_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'visit_duration_hours' => ['required', 'numeric', 'min:0.5', 'max:72'],
            'best_time_to_visit' => ['required', Rule::in(CityPlace::BEST_TIMES)],
            'price_per_person' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'note' => ['nullable', 'string', 'max:1000'],
            'remove_secondary' => ['nullable', 'array'],
            'remove_secondary.*' => ['string'],
        ]);

        $chainError = $this->validateCascadeChain((int) $validated['country_id'], (int) $validated['state_id'], (int) $validated['city_id']);

        if ($chainError !== null) {
            return back()->withErrors(['city_id' => $chainError])->withInput();
        }

        unset($validated['country_id'], $validated['state_id']);

        if (CityPlace::where('city_id', $validated['city_id'])->where('name', $validated['name'])->where('id', '!=', $place->id)->exists()) {
            return back()->withErrors(['name' => 'This place already exists in the selected city.'])->withInput();
        }

        if ($request->hasFile('primary_image')) {
            Storage::disk('public')->delete($place->primary_image);
            $validated['primary_image'] = $request->file('primary_image')->store('places/primary', 'public');
        } else {
            unset($validated['primary_image']);
        }

        $existing = $place->secondary_images ?? [];
        foreach ($validated['remove_secondary'] ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $existing = array_values(array_diff($existing, $validated['remove_secondary'] ?? []));

        if ($request->hasFile('secondary_images')) {
            foreach ($request->file('secondary_images') as $img) {
                $existing[] = $img->store('places/secondary', 'public');
            }
        }
        $validated['secondary_images'] = array_values($existing);
        unset($validated['remove_secondary']);

        $place->update($validated);

        return redirect()->route('admin.places.index')->with('success', 'Place updated successfully.');
    }

    public function destroy(CityPlace $place): RedirectResponse
    {
        Storage::disk('public')->delete($place->primary_image);
        foreach ($place->secondary_images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $place->delete();

        return redirect()->route('admin.places.index')->with('success', 'Place deleted.');
    }

    private function validateCascadeChain(int $countryId, int $stateId, int $cityId): ?string
    {
        $state = State::find($stateId);

        if ($state === null || (int) $state->country_id !== $countryId) {
            return 'The selected state does not belong to the selected country.';
        }

        $city = City::find($cityId);

        if ($city === null || (int) $city->state_id !== $stateId) {
            return 'The selected city does not belong to the selected state.';
        }

        return null;
    }
}
