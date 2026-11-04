<?php

namespace App\Http\Controllers\CabAssociate;

use App\Http\Controllers\Controller;
use App\Models\CabAssociate;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CabController extends Controller
{
    public function index(Request $request): View
    {
        $associate = $this->associate($request);
        $cabs = $associate->cabs()->with('city')->latest()->paginate(12);

        return view('cab-associate.cabs.index', compact('cabs'));
    }

    public function create(Request $request): View
    {
        $cab = null;
        $associate = $this->associate($request);
        $associateCityId = $associate->city_id;
        $cities = City::orderBy('name')->get();

        return view('cab-associate.cabs.create', compact('cab', 'cities', 'associateCityId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:cabs'],
            'type' => ['required', 'string', 'max:50'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'rate_per_km' => ['required', 'numeric', 'min:0'],
            'base_fare' => ['nullable', 'numeric', 'min:0'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:2048'],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
        ]);

        $validated['base_fare'] = $validated['base_fare'] ?? 0;
        $validated['image'] = $request->file('image')->store('cabs', 'public');
        $this->associate($request)->cabs()->create($validated);

        return redirect()->route('cab-associate.cabs.index')
            ->with('success', 'Cab added successfully.');
    }

    public function edit(Request $request, int $cab): View
    {
        $associate = $this->associate($request);
        $associateCityId = $associate->city_id;
        $cab = $associate->cabs()->findOrFail($cab);
        $cities = City::orderBy('name')->get();

        return view('cab-associate.cabs.edit', compact('cab', 'cities', 'associateCityId'));
    }

    public function update(Request $request, int $cab): RedirectResponse
    {
        $cab = $this->associate($request)->cabs()->findOrFail($cab);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('cabs')->ignore($cab)],
            'type' => ['required', 'string', 'max:50'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'rate_per_km' => ['required', 'numeric', 'min:0'],
            'base_fare' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'extensions:jpg,jpeg,png,webp,avif', 'max:2048'],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
        ]);

        $validated['base_fare'] = $validated['base_fare'] ?? 0;
        $oldImage = $cab->image;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('cabs', 'public');
        }

        $cab->update($validated);

        if (isset($validated['image']) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('cab-associate.cabs.index')
            ->with('success', 'Cab updated successfully.');
    }

    public function destroy(Request $request, int $cab): RedirectResponse
    {
        $cab = $this->associate($request)->cabs()->findOrFail($cab);
        $image = $cab->image;
        $cab->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('cab-associate.cabs.index')
            ->with('success', 'Cab deleted successfully.');
    }

    private function associate(Request $request): CabAssociate
    {
        return CabAssociate::findOrFail($request->session()->get('cab_associate_id'));
    }
}
