<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\CityPlace;
use App\Models\Country;
use App\Models\PackageDestination;
use App\Models\State;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TourPackageController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'departure_city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $search = $validated['search'] ?? null;
        $departureCity = $validated['departure_city'] ?? null;
        $isActive = $validated['is_active'] ?? null;

        $query = TourPackage::query()
            ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when($departureCity, fn ($q) => $q->where('departure_city', 'like', '%'.$departureCity.'%'));

        if (is_bool($isActive)) {
            $query->where('is_active', $isActive);
        }

        $packages = $query->latest('id')->paginate(15)->withQueryString();

        $countries = Country::orderBy('name')->get();
        $states = State::with('country')->orderBy('name')->get();
        $cities = City::with('state')->orderBy('name')->get();

        $hasFilter = $search || $departureCity || is_bool($isActive);

        return view('admin.destinations.packages.index', compact('packages', 'countries', 'states', 'cities', 'search', 'departureCity', 'isActive', 'hasFilter'));
    }

    public function create(): View
    {
        return view('admin.destinations.packages.create', [
            'countries' => Country::orderBy('name')->get(),
            'states' => State::with('country')->orderBy('name')->get(),
            'cities' => City::with('state')->orderBy('name')->get(),
        ]);
    }
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tour_packages,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'departure_city' => ['required', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'days' => ['required', 'integer', 'min:1', 'max:99'],
            'nights' => ['required', 'integer', 'min:1', 'max:99'],
            'destinations' => ['nullable', 'array'],
            'destinations.country' => ['nullable', 'array'],
        ]);

        $image = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('packages/primary', 'public');
        }

        $package = TourPackage::create(array_merge(
            $validated,
            ['image' => $image, 'is_active' => $request->has('is_active')]
        ));

        $this->syncDestinations($package, $request->destinations ?? []);

        return redirect()->route('admin.packages.index')->with('success', 'Package created.');
    }
    public function show(TourPackage $package): View
    {
        $package->load('destinations');

        return view('admin.destinations.packages.show', compact('package'));
    }
    public function edit(TourPackage $package): View
    {
        $package->load('destinations');

        return view('admin.destinations.packages.edit', [
            'package' => $package,
            'countries' => Country::orderBy('name')->get(),
            'states' => State::with('country')->orderBy('name')->get(),
            'cities' => City::with('state')->orderBy('name')->get(),
        ]);
    }
    public function update(Request $request, TourPackage $package): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tour_packages,name,'.$package->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'departure_city' => ['required', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'days' => ['required', 'integer', 'min:1', 'max:99'],
            'nights' => ['required', 'integer', 'min:1', 'max:99'],
            'destinations' => ['nullable', 'array'],
            'destinations.country' => ['nullable', 'array'],
            'destinations.country.*' => ['integer', 'exists:countries,id'],
            'destinations.state' => ['nullable', 'array'],
            'destinations.state.*' => ['integer', 'exists:states,id'],
            'destinations.city' => ['nullable', 'array'],
            'destinations.city.*' => ['integer', 'exists:cities,id'],
            'destinations.place' => ['nullable', 'array'],
            'destinations.place.*' => ['integer', 'exists:city_places,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $image = $package->image;
        if ($request->hasFile('image')) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $image = $request->file('image')->store('packages/primary', 'public');
        }

        $package->update(array_merge(
            $validated,
            ['image' => $image, 'is_active' => $request->has('is_active')]
        ));

        $this->syncDestinations($package, $request->destinations ?? []);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }
    public function destroy(TourPackage $package): RedirectResponse
    {
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }

        PackageDestination::where('package_id', $package->id)->delete();

        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }

    private function destinationClass(string $type): string
    {
        return match ($type) {
            'country' => Country::class,
            'state' => State::class,
            'city' => City::class,
            'place' => CityPlace::class,
            default => throw new \InvalidArgumentException('Unknown destination type: ' . $type),
        };
    }

    private function syncDestinations(TourPackage $package, array $groups): void
    {
        PackageDestination::where('package_id', $package->id)->delete();

        $position = 1;
        foreach ($groups as $type => $ids) {
            foreach ((array) $ids as $id) {
                PackageDestination::create([
                    'package_id' => $package->id,
                    'destination_type' => $this->destinationClass($type),
                    'destination_id' => $id,
                    'position' => $position++,
                ]);
            }
        }
    }
}