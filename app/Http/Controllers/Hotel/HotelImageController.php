<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HotelImageController extends Controller
{
    public function index(Request $request): View
    {
        $hotel = $this->hotel($request);

        return view('hotel.images.index', compact('hotel'));
    }

    public function update(Request $request): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $currentImages = $hotel->secondary_images ?? [];
        $requestedRemovals = $request->input('remove_secondary_images', []);
        $requestedRemovalPaths = is_array($requestedRemovals)
            ? array_filter($requestedRemovals, 'is_string')
            : [];
        $validRemovals = array_intersect($requestedRemovalPaths, $currentImages);
        $willHavePrimaryImage = $request->hasFile('primary_image')
            || (! $request->boolean('remove_primary_image') && filled($hotel->primary_image));
        $availableImageSlots = 10
            - (int) $willHavePrimaryImage
            - count($currentImages)
            + count(array_unique($validRemovals));

        $validated = $request->validate([
            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_primary_image' => ['nullable', 'boolean'],
            'secondary_images' => ['nullable', 'array', 'max:'.max(0, $availableImageSlots)],
            'secondary_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_secondary_images' => ['nullable', 'array'],
            'remove_secondary_images.*' => ['string', Rule::in($currentImages)],
        ], [
            'secondary_images.max' => 'A hotel can have up to 10 photos in total, including its primary photo.',
            'remove_secondary_images.*.in' => 'One of the selected secondary photos does not belong to this hotel.',
        ]);

        $remainingImages = array_values(array_diff($currentImages, $validated['remove_secondary_images'] ?? []));
        $newSecondaryImages = [];
        foreach ($request->file('secondary_images', []) as $image) {
            $imagePath = $image->store("hotels/{$hotel->id}/secondary", 'public');

            if ($imagePath === false) {
                abort(500, 'A secondary hotel photo could not be saved.');
            }

            $newSecondaryImages[] = $imagePath;
        }

        $currentPrimaryImage = $hotel->primary_image;
        $newPrimaryImage = $currentPrimaryImage;
        if ($request->hasFile('primary_image')) {
            $newPrimaryImage = $request->file('primary_image')->store("hotels/{$hotel->id}/primary", 'public');

            if ($newPrimaryImage === false) {
                abort(500, 'The primary hotel photo could not be saved.');
            }
        } elseif ($request->boolean('remove_primary_image')) {
            $newPrimaryImage = null;
        }

        $hotel->update([
            'primary_image' => $newPrimaryImage,
            'secondary_images' => array_merge($remainingImages, $newSecondaryImages),
        ]);

        if ($currentPrimaryImage !== $newPrimaryImage && $currentPrimaryImage !== null) {
            Storage::disk('public')->delete($currentPrimaryImage);
        }
        Storage::disk('public')->delete(array_diff($currentImages, $remainingImages));

        return redirect()->route('hotel.images.index')->with('success', 'Primary and secondary hotel photos updated successfully.');
    }

    private function hotel(Request $request): Hotel
    {
        return Hotel::query()->findOrFail((int) $request->session()->get('hotel_id'));
    }
}
