<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = $validated['search'] ?? null;
        $hotels = Hotel::with(['city.state.country'])
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhereHas('city', fn ($cityQuery) => $cityQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.hotels.index', compact('hotels', 'search'));
    }

    public function create(): View
    {
        $hotel = null;
        $countries = Country::orderBy('name')->get();
        $countryId = old('country_id');
        $stateId = old('state_id');
        $cityId = old('city_id');
        $states = $countryId
            ? State::where('country_id', $countryId)->orderBy('name')->get()
            : collect();
        $cities = $stateId
            ? City::where('state_id', $stateId)->orderBy('name')->get()
            : collect();

        return view('admin.hotels.create', compact('hotel', 'countries', 'states', 'cities', 'countryId', 'stateId', 'cityId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateHotel($request);
        $chainError = $this->validateLocationChain(
            (int) $validated['country_id'],
            (int) $validated['state_id'],
            (int) $validated['city_id'],
        );

        if ($chainError !== null) {
            return back()->withErrors(['city_id' => $chainError])->withInput();
        }

        unset($validated['country_id'], $validated['state_id'], $validated['password_confirmation']);
        $validated['adult_cost'] = 0;
        $validated['child_cost'] = 0;
        $validated['extra_bed_cost'] = 0;
        $hotel = DB::transaction(function () use ($validated): Hotel {
            $hotel = Hotel::create($validated);
            $hotel->hotel_code = 'HTL-'.str_pad((string) $hotel->id, 6, '0', STR_PAD_LEFT);
            $hotel->save();

            return $hotel;
        });

        return redirect()->route('admin.hotels.edit', $hotel)
            ->with('success', 'Hotel created. Hotel code: '.$hotel->hotel_code.'. Save the username, code, and password securely.');
    }

    public function edit(Hotel $hotel): View
    {
        $hotel->load('city.state.country');
        $countries = Country::orderBy('name')->get();
        $countryId = old('country_id', $hotel->city->state->country_id);
        $stateId = old('state_id', $hotel->city->state_id);
        $cityId = old('city_id', $hotel->city_id);
        $states = State::where('country_id', $countryId)->orderBy('name')->get();
        $cities = City::where('state_id', $stateId)->orderBy('name')->get();

        return view('admin.hotels.edit', compact('hotel', 'countries', 'states', 'cities', 'countryId', 'stateId', 'cityId'));
    }

    public function show(Hotel $hotel): View
    {
        $hotel->load([
            'city.state.country',
            'roomTypes' => fn ($query) => $query->orderBy('name'),
            'roomTypes.rooms' => fn ($query) => $query->orderBy('name'),
        ]);

        return view('admin.hotels.show', compact('hotel'));
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        $validated = $this->validateHotel($request, $hotel);
        $chainError = $this->validateLocationChain(
            (int) $validated['country_id'],
            (int) $validated['state_id'],
            (int) $validated['city_id'],
        );

        if ($chainError !== null) {
            return back()->withErrors(['city_id' => $chainError])->withInput();
        }

        unset($validated['country_id'], $validated['state_id'], $validated['password_confirmation']);
        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }
        $hotel->update($validated);

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel updated successfully.');
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $hotel->delete();

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateHotel(Request $request, ?Hotel $hotel = null): array
    {
        return $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/\A[a-z0-9._-]+\z/',
                $hotel
                    ? Rule::unique('hotels', 'username')->ignore($hotel)
                    : Rule::unique('hotels', 'username'),
            ],
            'password' => [
                $hotel?->password ? 'nullable' : 'required',
                'string',
                'min:12',
                'confirmed',
            ],
            'password_confirmation' => ['required_with:password', 'string'],
            'star_rating' => ['required', 'integer', 'between:1,5'],
            'address' => ['required', 'string', 'max:2000'],
        ]);
    }

    private function validateLocationChain(int $countryId, int $stateId, int $cityId): ?string
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
