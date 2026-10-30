@extends('layouts.admin')

@section('title', 'Bookings')

@section('header', 'Bookings Management')

@section('subheader', 'Manage and view all bookings')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-lg">All Bookings</h2>
    </div>

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
                    <td class="px-6 py-3">{{ $booking->package?->name ?? 'Package' }}</td>
                    <td class="px-6 py-3 break-words font-bold text-slate-800">{{ $booking->email }}</td>
                    <td class="px-6 py-3 font-bold text-slate-800">{{ $booking->mobile }}</td>
                    <td class="px-6 py-3">{{ $booking->country }}</td>
                    <td class="px-6 py-3">{{ $booking->adult_count }} adult{{ $booking->adult_count === 1 ? '' : 's' }}</td>
                    <td class="px-6 py-3">{{ $booking->child_count }} child{{ $booking->child_count === 1 ? '' : 'ren' }}</td>
                    <td class="px-6 py-3">Rs.{{ number_format($booking->amount ?? 0) }}</td>
                    <td class="px-6 py-3">
                        @if($booking->status === 'pending')
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
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