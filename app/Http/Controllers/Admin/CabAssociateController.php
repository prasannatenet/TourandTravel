<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CabAssociate;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CabAssociateController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $associates = CabAssociate::with('city')
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.cab-associates.index', compact('associates', 'validated'));
    }

    public function create(): View
    {
        $associate = null;
        $cities = City::orderBy('name')->get();

        return view('admin.cab-associates.create', compact('associate', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'unique:cab_associates'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', 'unique:cab_associates'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;

        CabAssociate::create($validated);

        return redirect()->route('admin.cab-associates.index')
            ->with('success', 'Cab associate added successfully.');
    }

    public function show(CabAssociate $associate): View
    {
        $associate->load(['city', 'cabs']);

        return view('admin.cab-associates.show', compact('associate'));
    }

    public function edit(CabAssociate $associate): View
    {
        $cities = City::orderBy('name')->get();

        return view('admin.cab-associates.edit', compact('associate', 'cities'));
    }

    public function update(Request $request, CabAssociate $associate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', Rule::unique('cab_associates')->ignore($associate)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('cab_associates')->ignore($associate)],
            'address' => ['nullable', 'string', 'max:2000'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password'], $validated['password_confirmation']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }
        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;

        $associate->update($validated);

        return redirect()->route('admin.cab-associates.index')
            ->with('success', 'Cab associate updated successfully.');
    }

    public function destroy(CabAssociate $associate): RedirectResponse
    {
        $associate->delete();

        return redirect()->route('admin.cab-associates.index')
            ->with('success', 'Cab associate deleted successfully.');
    }
}
