@extends('layouts.app')

@section('title', 'My Custom Packages')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-black text-slate-900">My custom packages</h1>
            <p class="mt-1 text-sm text-slate-600">Only packages created in this browser session are listed here.</p>
        </div>
        <a href="{{ route('custom-packages.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Build a package</a>
    </div>

    @forelse($customPackages as $customPackage)
        @php($statusClasses = ['draft' => 'bg-slate-100 text-slate-700', 'pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800'])
        <a href="{{ route('custom-packages.show', $customPackage) }}" class="mb-3 block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">{{ $customPackage->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $customPackage->departure_city }} &middot; {{ $customPackage->days }} days / {{ $customPackage->nights }} nights &middot; {{ count($customPackage->itinerary) }} itinerary days</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold capitalize {{ $statusClasses[$customPackage->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $customPackage->status }}</span>
            </div>
            @if($customPackage->submitted_at)<p class="mt-2 text-xs text-slate-400">Request sent {{ $customPackage->submitted_at->format('M j, Y g:i A') }}</p>@endif
        </a>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
            <i class="fa-solid fa-map-location-dot text-3xl text-indigo-400"></i>
            <h2 class="mt-3 text-lg font-bold text-slate-900">No custom packages yet</h2>
            <p class="mt-1 text-sm text-slate-500">Build a private package using places from our destination list.</p>
            <a href="{{ route('custom-packages.create') }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Start building</a>
        </div>
    @endforelse

    {{ $customPackages->links() }}
</div>
@endsection
