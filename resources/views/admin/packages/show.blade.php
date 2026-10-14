@extends('layouts.admin')
@section('title', $package->name)
@section('header', $package->name)
@section('subheader', 'View and manage package details.')

@section('content')
<div class="overflow-hidden bg-white shadow rounded-2xl">
    <div class="px-6 py-4 border-b border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">{{ $package->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Package details and destinations.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $package->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $package->is_active ? 'Active' : 'Draft' }}
                </span>
                <a href="{{ route('admin.packages.edit', $package) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-indigo-600 hover:bg-indigo-50">Edit</a>
            </div>
        </div>
    </div>

    <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="rounded-xl bg-slate-50 p-4">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Departure city</dt><dd class="text-slate-800 font-medium">{{ $package->departure_city ?? 'N/A' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Base price</dt><dd class="text-slate-800 font-medium">Rs.{{ number_format($package->base_price) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Days / Nights</dt><dd class="text-slate-800 font-medium">{{ $package->days }} / {{ $package->nights }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Views</dt><dd class="text-slate-800 font-medium">{{ $package->views }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Created</dt><dd class="text-slate-800 font-medium">{{ $package->created_at->format('Y-m-d H:i') ?? 'N/A' }}</dd></div>
                </dl>
            </div>
            @if($package->description)
            <div class="mt-3 rounded-xl bg-white border border-slate-200 p-4">
                <h4 class="text-xs font-semibold uppercase text-slate-400 mb-1">Description</h4>
                <p class="text-sm text-slate-600">{{ $package->description }}</p>
            </div>
            @endif
        </div>

        <div>
            <div class="rounded-xl bg-slate-50 p-4">
                <h4 class="text-xs font-semibold uppercase text-slate-400 mb-3">Destinations ({{ $package->destinations->count() }})</h4>
                <div class="space-y-2">
                    @forelse($package->destinations as $pd)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200">
                        <span class="text-sm font-medium text-slate-700">{{ $pd->destination ? $pd->destination->name : $pd->destination_id }}</span>
                        <span class="text-xs text-slate-400">{{ strtolower(class_basename($pd->destination_type)) }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400">No destinations selected.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($package->image)
    <div class="px-6 pb-6">
        <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="max-h-48 rounded-xl border border-slate-200 object-cover">
    </div>
    @endif
</div>
@endsection