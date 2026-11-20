@extends('layouts.admin')

@section('title', 'Bookings')

@section('header', 'Bookings Management')

@section('subheader', 'Manage and view all bookings')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="font-bold text-lg">{{ $processing ? 'Bookings To Process' : 'All Bookings' }}</h2>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <nav class="flex flex-wrap gap-2" aria-label="Booking views">
                <a href="{{ route('admin.bookings.index') }}" @class([
                    'rounded-lg px-4 py-2 text-sm font-semibold',
                    'bg-indigo-600 text-white' => ! $processing,
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $processing,
                ])>All Bookings</a>
                <a href="{{ route('admin.bookings.processing') }}" @class([
                    'rounded-lg px-4 py-2 text-sm font-semibold',
                    'bg-indigo-600 text-white' => $processing,
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => ! $processing,
                ])>To Process</a>
            </nav>
            @unless ($processing)
                <a href="{{ route('admin.bookings.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <span aria-hidden="true">+</span>
                    Add a New Booking
                </a>
            @endunless
        </div>
    </div>

    <section class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="font-bold text-slate-900">{{ $processing ? 'Custom package requests to process' : 'Custom package booking requests' }}</h3>
                <p class="mt-1 text-xs text-slate-600">Requests submitted from the customer’s own itinerary builder.</p>
            </div>
            <a href="{{ route('admin.custom-package-requests.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">Open all custom requests</a>
        </div>
        <div class="space-y-2">
            @forelse($customPackageRequests as $customRequest)
                <a href="{{ route('admin.custom-package-requests.show', $customRequest) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 hover:border-indigo-300">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $customRequest->booking_details['customer_name'] ?? 'Visitor' }} · {{ $customRequest->name }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $customRequest->booking_details['email'] ?? '' }} · {{ $customRequest->booking_details['mobile'] ?? '' }} · {{ $customRequest->days }} days</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $customRequest->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($customRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') }}">{{ $customRequest->status }}</span>
                        <span class="text-xs font-semibold text-indigo-700">View request →</span>
                    </div>
                </a>
            @empty
                <p class="rounded-lg bg-white px-4 py-5 text-center text-sm text-slate-500">No custom package booking requests yet.</p>
            @endforelse
        </div>
        <div class="mt-3">{{ $customPackageRequests->links() }}</div>
    </section>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="text-left px-6 py-3">ID</th>
                    <th class="text-left px-6 py-3">Customer</th>
                    <th class="text-left px-6 py-3">Tour</th>
                    <th class="text-left px-6 py-3">Email</th>
                    <th class="text-left px-6 py-3">Mobile</th>
                    <th class="text-left px-6 py-3">Country</th>
                    <th class="text-left px-6 py-3">Adults</th>
                    <th class="text-left px-6 py-3">Children</th>
                    <th class="text-left px-6 py-3">Amount</th>
                    <th class="text-left px-6 py-3">Status</th>
                    <th class="text-left px-6 py-3">Created At</th>
                    <th class="text-left px-6 py-3 text-width">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($bookings as $booking)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-3 font-semibold">{{ $booking->id }}</td>
                    <td class="px-6 py-3">{{ $booking->name }}</td>
                    <td class="px-6 py-3">{{ $booking->itinerary_override['custom_package']['name'] ?? $booking->package?->name ?? 'Package' }}</td>
                    <td class="px-6 py-3 break-words font-bold text-slate-800">{{ $booking->email }}</td>
                    <td class="px-6 py-3 font-bold text-slate-800">{{ $booking->mobile }}</td>
                    <td class="px-6 py-3">{{ $booking->country }}</td>
                    <td class="px-6 py-3">{{ $booking->adult_count }} adult{{ $booking->adult_count === 1 ? '' : 's' }} ({{ $booking->student_count }} students)</td>
                    <td class="px-6 py-3">{{ $booking->child_count }} child{{ $booking->child_count === 1 ? '' : 'ren' }} (ages 3–15)</td>
                    <td class="px-6 py-3">Rs.{{ number_format($booking->amount ?? 0) }}</td>
                    <td class="px-6 py-3">
                        @if($booking->status === 'pending')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                        @elseif($booking->status === 'quotation_sent')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">Quotation Sent</span>
                        @elseif($booking->status === 'confirmed')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Confirmed</span>
                        @elseif($booking->status === 'completed')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Completed</span>
                        @elseif($booking->status === 'cancelled')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Cancelled</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-600">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-slate-500 text-sm">{{ $booking->created_at?->format('M d, Y') ?? '' }}</td>
                    <td class="px-6 py-3">
                        @if (in_array($booking->status, ['pending', 'quotation_sent'], true) && $booking->package_id !== null)
                            <a href="{{ route('admin.bookings.process', $booking) }}" class="mr-3 text-xs font-semibold text-indigo-600 hover:underline">Process</a>
                        @endif
                        @if ($booking->status === 'quotation_sent')
                            <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" class="mb-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg  px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#3d67bf]" style="background: #4b77d4 !important">
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    Confirm Booking 
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-xs text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{ $bookings->links() }}

@endsection
