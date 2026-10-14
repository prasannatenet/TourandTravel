<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $countries = Country::withCount(['states'])->latest()->paginate(15);

        return view('admin.destinations.countries.index', compact('countries'));
    }

    public function create(): View
    {
        return view('admin.destinations.countries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:countries,name'],
        ]);

        Country::create($validated);

        return redirect()->route('admin.countries.index')->with('success', 'Country created. Now add states under it.');
    }

    public function edit(Country $country): View
    {
        return view('admin.destinations.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:countries,name,'.$country->id],
        ]);

        $country->update($validated);

        return redirect()->route('admin.countries.index')->with('success', 'Country updated.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->route('admin.countries.index')->with('success', 'Country and its states/cities deleted.');
    }
}
