<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelPolicyController extends Controller
{
    public function edit(Request $request): View
    {
        $hotel = $this->hotel($request);

        return view('hotel.policies.edit', compact('hotel'));
    }

    public function update(Request $request): RedirectResponse
    {
        $hotel = $this->hotel($request);
        $validated = $request->validate([
            'hotel_policy' => ['nullable', 'string', 'max:10000'],
            'room_policy' => ['nullable', 'string', 'max:10000'],
        ]);

        $hotel->update($validated);

        return redirect()->route('hotel.policies.edit')->with('success', 'Hotel and room policies saved successfully.');
    }

    private function hotel(Request $request): Hotel
    {
        return Hotel::query()->findOrFail((int) $request->session()->get('hotel_id'));
    }
}
