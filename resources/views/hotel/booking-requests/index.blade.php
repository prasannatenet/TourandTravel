@extends('layouts.hotel')

@section('title', 'Booking Requests')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-slate-900">Booking Requests</h1>
    <p class="mt-2 text-slate-500">Review room availability and respond to requests from the travel administrator.</p>
</div>

<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    @if ($bookings->isEmpty())
        <div class="py-12 text-center text-slate-500">No hotel booking requests yet.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-5 py-3">Booking</th><th class="px-5 py-3">Package / Guest</th><th class="px-5 py-3">Room</th><th class="px-5 py-3">Dates</th><th class="px-5 py-3">Rooms / Guests</th><th class="px-5 py-3">Status / Response</th><th class="px-5 py-3">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($bookings as $booking)
                        <tr>
                            <td class="px-5 py-4 font-semibold">#{{ $booking->id }}<div class="text-xs font-normal text-slate-500">{{ $booking->hotel_booking_requested_at?->format('M d, Y H:i') }}</div></td>
                            <td class="px-5 py-4">{{ $booking->itinerary_override['custom_package']['name'] ?? $booking->package?->name ?? 'Package' }}<div class="text-xs text-slate-500">{{ $booking->name }} · {{ $booking->email }}</div></td>
                            <td class="px-5 py-4">{{ $booking->hotelRoom?->roomType?->name }}<div class="text-xs text-slate-500">{{ $booking->hotelRoom?->name }}</div></td>
                            <td class="px-5 py-4 whitespace-nowrap">{{ $booking->check_in_date?->format('M d, Y') }}<div class="text-xs text-slate-500">to {{ $booking->check_out_date?->format('M d, Y') }}</div></td>
                            <td class="px-5 py-4">{{ $booking->room_count }} room{{ $booking->room_count === 1 ? '' : 's' }}<div class="text-xs text-slate-500">{{ $booking->adult_count }} adults · {{ $booking->child_count }} children · {{ $booking->extra_bed_count }} extra beds</div></td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $booking->hotel_request_status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : ($booking->hotel_request_status === 'cancelled' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') }}">{{ ucfirst($booking->hotel_request_status) }}</span>@if ($booking->hotel_response)<div class="mt-2 max-w-xs text-xs text-slate-600">{{ $booking->hotel_response }}</div>@endif</td>
                            <td class="px-5 py-4">
                                @if ($booking->hotel_request_status === 'requested')
                                    <form method="POST" action="{{ route('hotel.booking-requests.respond', $booking) }}" class="min-w-52 space-y-2">@csrf @method('PATCH')
                                        <textarea name="hotel_response" rows="2" maxlength="1000" placeholder="Optional note to the administrator" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs"></textarea>
                                        <div class="flex gap-2"><button name="hotel_request_status" value="confirmed" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Confirm rooms</button><button name="hotel_request_status" value="cancelled" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Decline</button></div>
                                    </form>
                                @elseif ($booking->hotel_responded_at)
                                    <span class="text-xs text-slate-500">Replied {{ $booking->hotel_responded_at->format('M d, Y H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
