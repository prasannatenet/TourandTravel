@extends('layouts.admin')
@section('title', 'Packages')
@section('header', 'Tour Packages')
@section('subheader', 'Manage tour packages and their destinations.')

@section('content')
<div class="overflow-hidden bg-white shadow rounded-2xl">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Packages</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage tour packages and their destinations.</p>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1.5"></i> New Package
        </a>
    </div>

    <div class="p-4 sm:p-6 overflow-x-auto">
        @if(session('success'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4">
            @forelse($packages as $package)
            <div class="border border-slate-200 rounded-xl p-4 sm:p-5 hover:shadow-md transition">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ $package->name }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $package->departure_city ?? 'N/A' }} &middot; {{ $package->base_price }} &middot; {{ $package->days }}D / {{ $package->nights }}N
                        </p>
                        @if($package->description)
                        <p class="text-xs text-slate-500 mt-1.5 line-clamp-2">{{ $package->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $package->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $package->is_active ? 'Active' : 'Draft' }}
                        </span>
                        <a href="{{ route('admin.packages.edit', $package) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-indigo-600 hover:bg-indigo-50">Edit</a>
                    </div>
                </div>

                <div class="mt-3">
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1.5">Destinations</p>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($package->destinations as $pd)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                            {{ $pd->destination ? $pd->destination->name : $pd->destination_id }}
                        </span>
                        @empty
                        <span class="text-xs text-slate-400">No destinations selected</span>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-slate-400">
                <p class="text-sm">No packages found.</p>
            </div>
            @endforelse
        </div>

        {{ $packages->links() }}
    </div>
</div>
@endsection