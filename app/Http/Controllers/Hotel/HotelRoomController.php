<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HotelRoomController extends Controller
{
    public function index(Request $request): View
    {
        $hotel = $this->hotel($request);
        $rooms = $hotel->rooms()
            ->with('roomType:id,name')
            ->latest()
            ->paginate(10);

        return view('hotel.rooms.index', compact('rooms'));
    }

    public function create(Request $request): View
    {
        $roomTypes = $this->hotel($request)->roomTypes()->orderBy('name')->get();

        return view('hotel.rooms.create', compact('roomTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $validated = $request->validate([
            'room_type_id' => [
                'required',
                'integer',
                Rule::exists('hotel_room_types', 'id')->where('hotel_id', $hotel->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'adult_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'child_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'extra_bed_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'images' => ['required', 'array', 'min:1', 'max:8'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description' => ['required', 'string', 'max:5000'],
            'highlights' => ['nullable', 'string', 'max:5000'],
            'key_points' => ['nullable', 'string', 'max:5000'],
            'amenities' => ['nullable', 'string', 'max:5000'],
        ], [
            'room_type_id.exists' => 'Select one of your hotel’s room types.',
            'images.required' => 'Upload at least one room image.',
            'images.min' => 'Upload at least one room image.',
            'images.max' => 'A room can have up to 8 images.',
        ]);

        $images = [];
        foreach ($request->file('images') as $image) {
            $images[] = $image->store("hotel-rooms/{$hotel->id}", 'public');
        }

        unset($validated['images']);
        $validated['images'] = $images;
        $hotel->rooms()->create($validated);

        return redirect()->route('hotel.rooms.index')->with('success', 'Room created successfully.');
    }

    public function edit(Request $request, int $room): View
    {
        $hotel = $this->hotel($request);
        $room = $hotel->rooms()->with('roomType:id,name')->findOrFail($room);
        $roomTypes = $hotel->roomTypes()->orderBy('name')->get();

        return view('hotel.rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, int $room): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $room = $hotel->rooms()->findOrFail($room);
        $currentImages = $room->images ?? [];
        $requestedRemovals = $request->input('remove_images', []);
        $validRemovals = is_array($requestedRemovals)
            ? array_intersect($requestedRemovals, $currentImages)
            : [];
        $availableImageSlots = 8 - count($currentImages) + count(array_unique($validRemovals));

        $validated = $request->validate([
            'room_type_id' => [
                'required',
                'integer',
                Rule::exists('hotel_room_types', 'id')->where('hotel_id', $hotel->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'adult_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'child_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'extra_bed_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'images' => ['nullable', 'array', 'max:'.max(0, $availableImageSlots)],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string', Rule::in($currentImages)],
            'description' => ['required', 'string', 'max:5000'],
            'highlights' => ['nullable', 'string', 'max:5000'],
            'key_points' => ['nullable', 'string', 'max:5000'],
            'amenities' => ['nullable', 'string', 'max:5000'],
        ], [
            'room_type_id.exists' => 'Select one of your hotel’s room types.',
            'images.max' => 'A room can have up to 8 images.',
            'remove_images.*.in' => 'One of the selected images does not belong to this room.',
        ]);

        $remainingImages = array_values(array_diff($currentImages, $validated['remove_images'] ?? []));
        $newImages = [];
        foreach ($request->file('images', []) as $image) {
            $newImages[] = $image->store("hotel-rooms/{$hotel->id}", 'public');
        }

        if (count($remainingImages) + count($newImages) < 1) {
            return back()
                ->withErrors(['images' => 'A room must have at least one image.'])
                ->withInput();
        }

        unset($validated['remove_images'], $validated['images']);
        $validated['images'] = array_merge($remainingImages, $newImages);
        $room->update($validated);

        foreach (array_diff($currentImages, $remainingImages) as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('hotel.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Request $request, int $room): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $room = $hotel->rooms()->findOrFail($room);
        $images = $room->images ?? [];
        $room->delete();

        Storage::disk('public')->delete($images);

        return redirect()->route('hotel.rooms.index')->with('success', 'Room deleted successfully.');
    }

    private function hotel(Request $request): Hotel
    {
        return Hotel::query()->findOrFail((int) $request->session()->get('hotel_id'));
    }
}
