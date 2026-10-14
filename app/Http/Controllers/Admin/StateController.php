<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StateController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
        ]);

        $search = $validated['search'] ?? null;
        $countryId = $validated['country_id'] ?? null;

        $states = State::with(['country'])
            ->withCount('cities')
            ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $countries = Country::orderBy('name')->get();

        return view('admin.destinations.states.index', compact('states', 'countries', 'search', 'countryId'));
    }

    public function create(): View
    {
        $countries = Country::orderBy('name')->get();

        return view('admin.destinations.states.create', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        if (State::where('country_id', $validated['country_id'])->where('name', $validated['name'])->exists()) {
            return back()->withErrors(['name' => 'This state already exists in the selected country.'])->withInput();
        }

        State::create($validated);

        return redirect()->route('admin.states.index')->with('success', 'State created. Now add cities under it.');
    }

    public function edit(State $state): View
    {
        $countries = Country::orderBy('name')->get();

        return view('admin.destinations.states.edit', compact('state', 'countries'));
    }

    public function update(Request $request, State $state): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        if (State::where('country_id', $validated['country_id'])->where('name', $validated['name'])->where('id', '!=', $state->id)->exists()) {
            return back()->withErrors(['name' => 'This state already exists in the selected country.'])->withInput();
        }

        $state->update($validated);

        return redirect()->route('admin.states.index')->with('success', 'State updated.');
    }

    public function destroy(State $state): RedirectResponse
    {
        $state->delete();

        return redirect()->route('admin.states.index')->with('success', 'State and its cities deleted.');
    }
}
