@extends('layouts.admin')
@section('title', 'Custom Package Requests')
@section('header', 'Custom Package Requests')
@section('subheader', 'Review private package and booking requests submitted by visitors.')

@section('content')
<div class="overflow-hidden rounded-2xl bg-white shadow">
    <div class="border-b border-slate-200 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-800">Visitor requests</h2>
        <p class="mt-1 text-xs text-slate-500">These custom packages are private and do not appear in the public package catalog.</p>
    </div>
    <div class="p-4 sm:p-6">
        <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
            <div><label for="status" class="mb-1 block text-xs font-semibold uppercase text-slate-500">Status</label><select id="status" name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">All statuses</option>@foreach(['pending', 'approved', 'rejected'] as $status)<option value="{{ $status }}" @selected(($validated['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">Filter</button>
        </form>

        <div class="space-y-3">
            @forelse($requests as $customPackageRequest)
                @php($statusClasses = ['draft' => 'bg-slate-100 text-slate-700', 'pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800'])
                <a href="{{ route('admin.custom-package-requests.show', $customPackageRequest) }}" class="block rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><h3 class="font-bold text-slate-900">{{ $customPackageRequest->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ $customPackageRequest->departure_city }} &middot; {{ $customPackageRequest->days }} days / {{ $customPackageRequest->nights }} nights</p></div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold capitalize {{ $statusClasses[$customPackageRequest->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $customPackageRequest->status }}</span>
                    </div>
                    @if($customPackageRequest->booking_details)
                        <p class="mt-2 text-xs text-slate-600">{{ $customPackageRequest->booking_details['customer_name'] ?? 'Visitor' }} &middot; {{ $customPackageRequest->booking_details['email'] ?? '' }} &middot; {{ $customPackageRequest->booking_details['adult_count'] ?? 0 }} adults, {{ $customPackageRequest->booking_details['child_count'] ?? 0 }} children</p>
                    @endif
                    <p class="mt-2 text-xs text-slate-400">{{ $customPackageRequest->submitted_at?->format('M j, Y g:i A') ?? 'Draft' }}</p>
                </a>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">No custom package requests found.</div>
            @endforelse
        </div>

        {{ $requests->links() }}
    </div>
</div>
@endsection
