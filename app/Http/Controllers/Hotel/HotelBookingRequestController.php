<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\HotelRoomUnavailableDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HotelBookingRequestController extends Controller
{
    public function index(Request $request): View
    {
        $hotelId = (int) $request->session()->get('hotel_id');
        $bookings = Booking::query()
            ->whereHas('hotelRoom', fn ($query) => $query->where('hotel_id', $hotelId))
            ->whereIn('hotel_request_status', ['requested', 'confirmed', 'cancelled'])
            ->with(['package', 'hotelRoom.roomType'])
            ->latest('hotel_booking_requested_at')
            ->paginate(20);

        return view('hotel.booking-requests.index', compact('bookings'));
    }

    public function respond(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'hotel_request_status' => ['required', 'in:confirmed,cancelled'],
            'hotel_response' => ['nullable', 'string', 'max:1000'],
        ]);
        $hotelId = (int) $request->session()->get('hotel_id');

        abort_unless($booking->hotelRoom()->where('hotel_id', $hotelId)->exists(), 404);
        abort_unless($booking->hotel_request_status === 'requested', 422, 'Only sent requests can receive a response.');

        $booking->update([
            'hotel_request_status' => $validated['hotel_request_status'],
            'hotel_response' => $validated['hotel_response'] ?? null,
            'hotel_responded_at' => now(),
        ]);

        $this->syncBookingUnavailableDates($booking);

        return redirect()->route('hotel.booking-requests.index')->with('success', 'Your response has been sent to the travel administrator.');
    }

    /**
     * Create or remove calendar blocked dates for a booking based on its
     * confirmed status so the room calendar reflects admin-confirmed bookings.
     */
    private function syncBookingUnavailableDates(Booking $booking): void
    {
        // Remove any previously blocked calendar dates tied to this booking so
        // the calendar stays in sync if the request is cancelled or changed.
        HotelRoomUnavailableDate::query()
            ->where('booking_id', $booking->id)
            ->delete();

        if ($booking->hotel_request_status !== 'confirmed') {
            return;
        }

        $room = $booking->hotelRoom;
        $checkIn = $booking->check_in_date;
        $checkOut = $booking->check_out_date;

        if ($room === null || $checkIn === null || $checkOut === null) {
            return;
        }

        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        if ($nights < 1) {
            return;
        }

        for ($offset = 0; $offset < $nights; $offset++) {
            $date = Carbon::parse($checkIn)->addDays($offset)->format('Y-m-d');

            HotelRoomUnavailableDate::updateOrCreate(
                ['hotel_room_id' => $room->id, 'date' => $date],
                [
                    'reason' => 'booking',
                    'booking_id' => $booking->id,
                    'package_id' => $booking->package_id,
                    'notes' => 'Confirmed booking #'.$booking->id,
                ]
            );
        }
    }
}
