@extends('layouts.admin')

@section('title', 'Booking Details')

@section('header', 'Booking Details')

@section('subheader', 'View booking information')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-bold text-lg">Booking Details</h2>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-blue-600 hover:underline">← Back to All Bookings</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 flex items-center gap-2">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-slate-50 rounded-xl p-6">
            <h3 class="font-semibold text-sm text-slate-500 uppercase mb-4">Basic Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-slate-600">Booking ID</dt>
                    <dd class="font-semibold">#{{ $booking->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Customer Name</dt>
                    <dd class="font-semibold">{{ $booking->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Customer ID</dt>
                    <dd class="font-semibold">{{ $booking->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Email</dt>
                    <dd class="font-semibold break-all">{{ $booking->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Mobile</dt>
                    <dd class="font-semibold">{{ $booking->mobile }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Country</dt>
                    <dd class="font-semibold">{{ $booking->country }}</dd>
                </div>
            </dl>
        </div>

        <!-- Package Information -->
        <div class="bg-slate-50 rounded-xl p-6">
            <h3 class="font-semibold text-sm text-slate-500 uppercase mb-4">Package Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-slate-600">Package</dt>
                    <dd class="font-semibold">{{ $displayPackageName }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Adults</dt>
                    <dd class="font-semibold">{{ $booking->adult_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Students (included in adults)</dt>
                    <dd class="font-semibold">{{ $booking->student_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Children (ages 3–15)</dt>
                    <dd class="font-semibold">{{ $booking->child_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Total Amount</dt>
                    <dd class="font-semibold">Rs.{{ number_format($booking->amount ?? 0, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Status</dt>
                    <dd class="font-semibold">
                        @if($booking->status === 'pending')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                        @elseif($booking->status === 'quotation_sent')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">Quotation Sent</span>
                        @elseif($booking->status === 'confirmed')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Confirmed</span>
                        @elseif($booking->status === 'cancelled')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Cancelled</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-600">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Created At</dt>
                    <dd class="font-semibold">{{ $booking->created_at->format('F d, Y h:i A') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Updated At</dt>
                    <dd class="font-semibold">{{ $booking->updated_at->format('F d, Y h:i A') }}</dd>
                </div>
            </dl>
        </div>

        <div class="md:col-span-2 rounded-xl bg-slate-50 p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold text-sm text-slate-500 uppercase">Booking Itinerary</h3>
                @if (in_array($booking->status, ['pending', 'quotation_sent'], true) && $booking->package)
                    <a href="{{ route('admin.bookings.process', $booking) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Process Booking</a>
                @endif
            </div>
            @if ($itineraryItems->isNotEmpty())
                <ol class="divide-y divide-slate-200">
                    @foreach ($itineraryItems as $item)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $item['title'] }}</p>
                                <p class="text-sm text-slate-500">Day {{ $item['day_number'] }} · {{ $item['start_time'] }} · {{ ucfirst($item['item_type']) }}</p>
                            </div>
                            @if ($item['item_type'] === 'hotel')
                                <span class="text-sm text-slate-600">{{ $item['nights'] ?? 1 }} night{{ ($item['nights'] ?? 1) === 1 ? '' : 's' }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="text-sm text-slate-500">No itinerary items are configured for this package.</p>
            @endif
        </div>

        <!-- ID Proof -->
        <div class="bg-slate-50 rounded-xl p-6">
            <h3 class="font-semibold text-sm text-slate-500 uppercase mb-4">ID Proof</h3>
            <div class="space-y-3">
                @if($booking->photo_id_proof)
                    <div class="flex items-center gap-3">
                        <span class="text-slate-600">ID Proof File:</span>
                        <a href="{{ asset('storage/'.$booking->photo_id_proof) }}" target="_blank" class="text-blue-600 hover:underline text-sm">
                            View File
                        </a>
                    </div>
                @else
                    <p class="text-slate-500">No ID proof uploaded</p>
                @endif
            </div>
        </div>

        <!-- Hotel Check-in Information -->
        <div class="bg-slate-50 rounded-xl p-6">
            <h3 class="font-semibold text-sm text-slate-500 uppercase mb-4">Hotel Check-in Details</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-slate-600">Hotel</dt>
                    <dd class="font-semibold">{{ $booking->hotelRoom->hotel->name ?? 'Hotel N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Room Type</dt>
                    <dd class="font-semibold">{{ $booking->hotelRoom->roomType->name ?? 'Room N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Check-in Date</dt>
                    <dd class="font-semibold">{{ $booking->check_in_date ? \Illuminate\Support\Carbon::parse($booking->check_in_date)->format('M d, Y') : 'Not set' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Check-out Date</dt>
                    <dd class="font-semibold">{{ $booking->check_out_date ? \Illuminate\Support\Carbon::parse($booking->check_out_date)->format('M d, Y') : 'Not set' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Room Count</dt>
                    <dd class="font-semibold">{{ $booking->room_count }} room{{ $booking->room_count > 1 ? 's' : '' }}</dd>
                </div>
                <div class="flex justify-between"><dt class="text-slate-600">Extra beds</dt><dd class="font-semibold">{{ $booking->extra_bed_count }}</dd></div>
            </dl>
        </div>

        <!-- Hotel Booking Request Status -->
        <div class="bg-slate-50 rounded-xl p-6">
            <h3 class="font-semibold text-sm text-slate-500 uppercase mb-4">Hotel Booking Request</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-slate-600">Status</dt>
                    <dd class="font-semibold">
                        @if($booking->hotel_request_status === 'requested')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">Requested</span>
                        @elseif($booking->hotel_request_status === 'confirmed')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Confirmed</span>
                        @elseif($booking->hotel_request_status === 'cancelled')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Cancelled</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-600">Pending</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Requested At</dt>
                    <dd class="font-semibold text-sm">{{ $booking->hotel_booking_requested_at ? $booking->hotel_booking_requested_at->format('M d, Y h:i A') : '—' }}</dd>
                </div>
                @if ($booking->hotel_responded_at)
                    <div class="flex justify-between"><dt class="text-slate-600">Hotel responded</dt><dd class="font-semibold text-sm">{{ $booking->hotel_responded_at->format('M d, Y h:i A') }}</dd></div>
                @endif
                @if ($booking->hotel_response)
                    <div><dt class="text-slate-600">Hotel note</dt><dd class="mt-1 font-medium">{{ $booking->hotel_response }}</dd></div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-slate-50 rounded-xl p-6">
            <h3 class="font-semibold text-sm text-slate-500 uppercase mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.bookings.index') }}" class="w-full px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">Back to Bookings</a>
                <a href="{{ route('admin.bookings.edit', $booking) }}" class="block w-full rounded-lg bg-indigo-600 px-4 py-2 text-center text-white transition hover:bg-indigo-700"><i class="fa-solid fa-pen-to-square mr-2"></i>Update Booking</a>
                @if($booking->status === 'quotation_sent')
                    <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fa-solid fa-check mr-2"></i> Confirm Booking
                        </button>
                    </form>
                @endif
                @if($booking->hotel_room_id && $booking->hotel_request_status !== 'requested' && $booking->hotel_request_status !== 'confirmed')
                    <form action="{{ route('admin.hotel-booking-requests.send', $booking->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Send Request to Hotel
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
