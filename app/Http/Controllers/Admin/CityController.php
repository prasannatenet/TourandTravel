<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
        ]);

        $search = $validated['search'] ?? null;
        $countryId = $validated['country_id'] ?? null;
        $stateId = $validated['state_id'] ?? null;

        $cities = City::with(['state.country'])
            ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($countryId, fn ($query) => $query->whereHas('state', fn ($q) => $q->where('country_id', $countryId)))
            ->when($stateId, fn ($query) => $query->where('state_id', $stateId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $countries = Country::orderBy('name')->get();
        $states = State::with('country')
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->orderBy('name')
            ->get();

        return view('admin.destinations.cities.index', compact('cities', 'countries', 'states', 'search', 'countryId', 'stateId'));
    }

    public function create(): View
    {
        $states = State::with('country')->orderBy('name')->get();

        return view('admin.destinations.cities.create', compact('states'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'state_id' => ['required', 'exists:states,id'],
            'name' => ['required', 'string', 'max:100'],
            'short_description' => ['required', 'string', 'max:500'],
            'primary_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
            'secondary_images' => ['nullable', 'array', 'max:8'],
            'secondary_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
        ]);

        if (City::where('state_id', $validated['state_id'])->where('name', $validated['name'])->exists()) {
            return back()->withErrors(['name' => 'This city already exists in the selected state.'])->withInput();
        }

        $validated['primary_image'] = $request->file('primary_image')->store('destinations/primary', 'public');
        $secondary = [];
        if ($request->hasFile('secondary_images')) {
            foreach ($request->file('secondary_images') as $img) {
                $secondary[] = $img->store('destinations/secondary', 'public');
            }
        }
        $validated['secondary_images'] = $secondary;

        City::create($validated);

        return redirect()->route('admin.cities.index')->with('success', 'City created successfully.');
    }

    public function edit(City $city): View
    {
        $states = State::with('country')->orderBy('name')->get();

        return view('admin.destinations.cities.edit', compact('city', 'states'));
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $validated = $request->validate([
            'state_id' => ['required', 'exists:states,id'],
            'name' => ['required', 'string', 'max:100'],
            'short_description' => ['required', 'string', 'max:500'],
            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
            'secondary_images' => ['nullable', 'array', 'max:8'],
            'secondary_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
            'remove_secondary' => ['nullable', 'array'],
            'remove_secondary.*' => ['string'],
        ]);

        if (City::where('state_id', $validated['state_id'])->where('name', $validated['name'])->where('id', '!=', $city->id)->exists()) {
            return back()->withErrors(['name' => 'This city already exists in the selected state.'])->withInput();
        }

        if ($request->hasFile('primary_image')) {
            Storage::disk('public')->delete($city->primary_image);
            $validated['primary_image'] = $request->file('primary_image')->store('destinations/primary', 'public');
        } else {
            unset($validated['primary_image']);
        }

        $existing = $city->secondary_images ?? [];
        foreach ($validated['remove_secondary'] ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $existing = array_values(array_diff($existing, $validated['remove_secondary'] ?? []));

        if ($request->hasFile('secondary_images')) {
            foreach ($request->file('secondary_images') as $img) {
                $existing[] = $img->store('destinations/secondary', 'public');
            }
        }
        $validated['secondary_images'] = array_values($existing);
        unset($validated['remove_secondary']);

        $city->update($validated);

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    public function destroy(City $city): RedirectResponse
    {
        Storage::disk('public')->delete($city->primary_image);
        foreach ($city->secondary_images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $city->delete();

        return redirect()->route('admin.cities.index')->with('success', 'City deleted.');
    }
}
