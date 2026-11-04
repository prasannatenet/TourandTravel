@extends('layouts.admin')

@section('title', 'Hotel Booking Requests')

@section('header', 'Hotel Booking Requests')

@section('subheader', 'Manage hotel room booking confirmation requests')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-lg">Hotel Booking Requests</h2>
        <span class="text-xs text-slate-500">Bookings awaiting hotel room confirmation</span>
    </div>

    @if(session('hotel_request_success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('hotel_request_message') }}
        </div>
    @endif

    @if($bookings->isEmpty())
        <div class="text-center py-12">
            <i class="fa-solid fa-inbox text-4xl text-slate-300 mb-4"></i>
            <p class="text-slate-500">No hotel booking requests at the moment.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="text-left px-6 py-3">ID</th>
                        <th class="text-left px-6 py-3">Guest</th>
                        <th class="text-left px-6 py-3">Package</th>
                        <th class="text-left px-6 py-3">Hotel</th>
                        <th class="text-left px-6 py-3">Room</th>
                        <th class="text-left px-6 py-3">Check-in</th>
                        <th class="text-left px-6 py-3">Check-out</th>
                        <th class="text-left px-6 py-3">Rooms</th>
                        <th class="text-left px-6 py-3">Guests / Extra beds</th>
                        <th class="text-left px-6 py-3">Request Status</th>
                        <th class="text-left px-6 py-3">Requested At</th>
                        <th class="text-left px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($bookings as $booking)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-semibold">#{{ $booking->id }}</td>
                            <td class="px-6 py-3 font-medium">{{ $booking->name }}</td>
                            <td class="px-6 py-3">{{ $booking->itinerary_override['custom_package']['name'] ?? $booking->package?->name ?? 'Package' }}</td>
                            <td class="px-6 py-3">{{ $booking->hotelRoom->hotel->name ?? 'Hotel' }}</td>
                            <td class="px-6 py-3">{{ $booking->hotelRoom->roomType->name ?? 'Room' }}</td>
                            <td class="px-6 py-3">{{ $booking->check_in_date ? \Illuminate\Support\Carbon::parse($booking->check_in_date)->format('M d, Y') : '—' }}</td>
                            <td class="px-6 py-3">{{ $booking->check_out_date ? \Illuminate\Support\Carbon::parse($booking->check_out_date)->format('M d, Y') : '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                    {{ $booking->room_count }} room{{ $booking->room_count > 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">{{ $booking->adult_count }} adults, {{ $booking->child_count }} children<div class="text-xs text-slate-500">{{ $booking->extra_bed_count }} extra beds</div></td>
                            <td class="px-6 py-3">
                                @if($booking->hotel_request_status === 'requested')
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">Requested</span>
                                @elseif($booking->hotel_request_status === 'confirmed')
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Confirmed</span>
                                @elseif($booking->hotel_request_status === 'cancelled')
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-rose-100 text-rose-800">Declined</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-sm">
                                {{ $booking->hotel_booking_requested_at ? \Illuminate\Support\Carbon::parse($booking->hotel_booking_requested_at)->format('M d, Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @if ($booking->hotel_response)
                                    <p class="mb-2 max-w-xs text-xs text-slate-500">{{ $booking->hotel_response }}</p>
                                @endif
                                @if ($booking->hotel_request_status === 'pending' || $booking->hotel_request_status === 'cancelled')
                                <button
                                    class="text-xs text-blue-600 hover:underline focus:ring-2 focus:ring-blue-500 rounded"
                                    onclick="event.stopPropagation(); sendHotelRequest({{ $booking->id }})"
                                >
                                    <i class="fa-solid fa-paper-plane mr-1"></i> {{ $booking->hotel_request_status === 'cancelled' ? 'Resend Request' : 'Send Request' }}
                                </button>
                                @elseif ($booking->hotel_request_status === 'requested')
                                    <span class="text-xs font-semibold text-blue-700">Request sent</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    function sendHotelRequest(bookingId) {
        if (confirm('Send this booking request to the hotel?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('admin.hotel-booking-requests.send', ['booking' => '__BOOKING__'])).replace('__BOOKING__', bookingId);
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = @json(csrf_token());
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

{{ $bookings->links() }}

@endsection
