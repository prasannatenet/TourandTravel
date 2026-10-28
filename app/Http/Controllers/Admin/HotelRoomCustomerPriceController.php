<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HotelRoomCustomerPriceController extends Controller
{
    public function redirectToHotelDetails(Hotel $hotel, int $room): RedirectResponse
    {
        $hotel->rooms()->findOrFail($room);

        return redirect()->route('admin.hotels.show', $hotel);
    }

    public function update(Request $request, Hotel $hotel, int $room): RedirectResponse
    {
        $validated = $request->validate([
            'customer_adult_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'customer_child_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'customer_extra_bed_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $hotel->rooms()->findOrFail($room)->update($validated);

        return redirect()->route('admin.hotels.show', $hotel)
            ->with('success', 'Customer prices updated successfully.');
    }
}
