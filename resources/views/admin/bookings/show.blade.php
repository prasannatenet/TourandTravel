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
                    <dd class="font-semibold">{{ $booking->package ? $booking->package->name : 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Adults</dt>
                    <dd class="font-semibold">{{ $booking->adult_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Children</dt>
                    <dd class="font-semibold">{{ $booking->child_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Status</dt>
                    <dd class="font-semibold">
                        @if($booking->status === 'pending')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
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

        <!-- Actions -->
        <div class="bg-slate-50 rounded-xl p-6">
            <h3 class="font-semibold text-sm text-slate-500 uppercase mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.bookings.index') }}" class="w-full px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">Back to Bookings</a>
            </div>
        </div>
    </div>
</div>
@endsection