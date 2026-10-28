<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HotelRoomTypeController extends Controller
{
    public function index(Request $request): View
    {
        $hotel = $this->hotel($request);
        $roomTypes = $hotel->roomTypes()
            ->withCount('rooms')
            ->orderBy('name')
            ->paginate(10);

        return view('hotel.room-types.index', compact('roomTypes'));
    }

    public function create(): View
    {
        return view('hotel.room-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('hotel_room_types', 'name')->where('hotel_id', $hotel->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.unique' => 'You already have a room type with this name.',
        ]);

        $hotel->roomTypes()->create($validated);

        return redirect()->route('hotel.room-types.index')->with('success', 'Room type created successfully.');
    }

    public function edit(Request $request, int $roomType): View
    {
        $hotel = $this->hotel($request);
        $roomType = $hotel->roomTypes()->findOrFail($roomType);

        return view('hotel.room-types.edit', compact('roomType'));
    }

    public function update(Request $request, int $roomType): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $roomType = $hotel->roomTypes()->findOrFail($roomType);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('hotel_room_types', 'name')
                    ->where('hotel_id', $hotel->id)
                    ->ignore($roomType->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.unique' => 'You already have a room type with this name.',
        ]);

        $roomType->update($validated);

        return redirect()->route('hotel.room-types.index')->with('success', 'Room type updated successfully.');
    }

    public function destroy(Request $request, int $roomType): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $roomType = $hotel->roomTypes()->findOrFail($roomType);

        if ($roomType->rooms()->exists()) {
            return back()->withErrors([
                'room_type' => 'This room type is assigned to rooms. Reassign or delete those rooms before deleting the type.',
            ]);
        }

        $roomType->delete();

        return redirect()->route('hotel.room-types.index')->with('success', 'Room type deleted successfully.');
    }

    private function hotel(Request $request): Hotel
    {
        return Hotel::query()->findOrFail((int) $request->session()->get('hotel_id'));
    }
}
