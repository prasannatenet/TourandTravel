<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cab;
use App\Models\CabAssociate;
use App\Models\City;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CabController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,inactive,maintenance'],
            'associate' => ['nullable', 'integer', 'exists:cab_associates,id'],
        ]);

        $cabs = Cab::with('associate')
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('registration_number', 'like', '%'.$search.'%')
                        ->orWhere('type', 'like', '%'.$search.'%');
                });
            })
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['associate'] ?? null, fn ($query, $associate) => $query->where('cab_associate_id', $associate))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $associates = CabAssociate::orderBy('name')->get();

        return view('admin.cabs.index', compact('cabs', 'associates', 'validated'));
    }

    public function create(): View
    {
        $cab = null;
        $associates = CabAssociate::orderBy('name')->get();
        $cities = City::orderBy('name')->get();

        return view('admin.cabs.create', compact('cab', 'associates', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cab_associate_id' => ['nullable', 'integer', 'exists:cab_associates,id'],
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

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cabs', 'public');
            $validated['image'] = $path;
        }

        Cab::create($validated);

        return redirect()->route('admin.cabs.index')
            ->with('success', 'Cab added successfully.');
    }

    public function show(Cab $cab): View
    {
        $cab->load('associate');

        return view('admin.cabs.show', compact('cab'));
    }

    public function availability(Request $request, Cab $cab): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $calendarStart = $month->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY);
        $calendarEnd = $month->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY);
        $unavailableDates = $cab->unavailableDates()
            ->whereBetween('date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
            ->pluck('date')
            ->map(static fn ($date): string => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $days = [];
        for ($date = $calendarStart; $date <= $calendarEnd; $date = $date->addDay()) {
            $days[] = $date;
        }

        return view('admin.cabs.availability', compact('cab', 'month', 'days', 'unavailableDates'));
    }

    public function edit(Cab $cab): View
    {
        $associates = CabAssociate::orderBy('name')->get();
        $cities = City::orderBy('name')->get();

        return view('admin.cabs.edit', compact('cab', 'associates', 'cities'));
    }

    public function update(Request $request, Cab $cab): RedirectResponse
    {
        $validated = $request->validate([
            'cab_associate_id' => ['nullable', 'integer', 'exists:cab_associates,id'],
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

        // Handle image upload if a new image is provided
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($cab->image) {
                Storage::disk('public')->delete($cab->image);
            }
            $path = $request->file('image')->store('cabs', 'public');
            $validated['image'] = $path;
        } else {
            // Keep existing image if not updated
            $validated['image'] = $cab->image;
        }

        $cab->update($validated);

        return redirect()->route('admin.cabs.index')
            ->with('success', 'Cab updated successfully.');
    }

    public function destroy(Cab $cab): RedirectResponse
    {
        $cab->delete();

        return redirect()->route('admin.cabs.index')
            ->with('success', 'Cab deleted successfully.');
    }
}
