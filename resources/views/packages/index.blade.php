@extends('layouts.app')
@section('title', 'Holiday Packages')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-1">Holiday Packages</h1>
        <p class="text-slate-600">Explore our curated tour packages across India and beyond.</p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 mb-8 shadow-sm">
        <form method="GET" action="{{ route('packages.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Search by name</label>
                <input type="text" name="search" value="{{ $validated['search'] ?? '' }}" placeholder="e.g. Kerala, Goa, Rajasthan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Destination</label>
                <select name="destination" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All destinations</option>
                    @foreach($destinations as $place)
                        <option value="{{ $place->id }}" {{ ($validated['destination'] ?? null) == $place->id ? 'selected' : '' }}>
                            {{ $place->name }}, {{ $place->city->name }}, {{ $place->city->state->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Duration (days)</label>
                <select name="duration" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Any duration</option>
                    @for($i = 1; $i <= 15; $i++)
                        <option value="{{ $i }}" {{ ($validated['duration'] ?? null) == $i ? 'selected' : '' }}>{{ $i }} day{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition">Search</button>
            </div>
        </form>

        @if($validated['search'] ?? $validated['destination'] ?? $validated['duration'] ?? $validated['min_price'] ?? $validated['max_price'])
            <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-600">
                @if($validated['search'] ?? null)
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <span>{{ $validated['search'] }}</span>
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-indigo-600">×</a>
                    </span>
                @endif
                @if($validated['destination'] ?? null)
                    @php
                        $dest = $destinations->firstWhere('id', $validated['destination']);
                    @endphp
                    @if($dest)
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <span>{{ $dest->name }}</span>
                            <a href="{{ request()->fullUrlWithQuery(['destination' => null]) }}" class="hover:text-indigo-600">×</a>
                        </span>
                    @endif
                @endif
                @if($validated['duration'] ?? null)
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <span>{{ $validated['duration'] }} days</span>
                        <a href="{{ request()->fullUrlWithQuery(['duration' => null]) }}" class="hover:text-indigo-600">×</a>
                    </span>
                @endif
            </div>
        @endif
    </div>
    <!-- Results Count -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-900">{{ $packages->total() }} package{{ $packages->total() != 1 ? 's' : '' }} found</h2>
        <div class="text-sm text-slate-500">Sort by: Featured</div>
    </div>

    <!-- Package Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($packages as $package)
            <article class="group rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm hover:shadow-md transition">
                <!-- Package Image -->
                <div class="relative h-48 overflow-hidden">
                    @if($package->galleryImages->first())
                        <img src="{{ asset('storage/'.$package->galleryImages->first()->image) }}" alt="{{ $package->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @elseif($package->image)
                        <img src="{{ asset('storage/'.$package->image) }}" alt="{{ $package->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="w-full h-full bg-slate-100 flex items-center justify-center">
                            <i class="fa-solid fa-image text-2xl text-slate-400"></i>
                        </div>
                    @endif
                    <span class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded-full text-xs font-bold text-slate-700 border border-slate-200">
                        {{ $package->days }} Days
                    </span>
                </div>

                <div class="p-5">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-1">{{ $package->name }}</h3>
                        @if($package->category)
                            <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700">{{ $package->category }}</span>
                        @endif
                    </div>

                    <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $package->description ?? 'Explore this beautiful destination with our well-planned itinerary.' }}</p>

                    <!-- Quick Info -->
                    <div class="mt-4 flex items-center gap-3 text-sm text-slate-500">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-clock text-slate-400"></i> {{ $package->nights }} Night{{ $package->nights != 1 ? 's' : '' }} / {{ $package->days }} Day{{ $package->days != 1 ? 's' : '' }}</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-location-dot text-slate-400"></i> {{ $package->departure_city }}</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-user text-slate-400"></i> Adult: ₹{{ number_format((float) $package->base_price, 0) }}</span>
                    </div>

                    <!-- Destinations -->
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach($package->destinations->take(3) as $dest)
                            @php
                                $d = $dest->destination;
                                $dName = $d?->name ?? 'Destination';
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-xs text-slate-600">{{ $dName }}</span>
                        @endforeach
                        @if($package->destinations->count() > 3)
                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-xs text-slate-600">+{{ $package->destinations->count() - 3 }} more</span>
                        @endif
                    </div>

                    <!-- CTA -->
                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-xl font-bold text-slate-900">₹{{ number_format((float) $package->base_price, 0) }}</span>
                            @if($package->child_price)
                                <span class="text-xs text-slate-500 ml-1">Child: ₹{{ number_format((float) $package->child_price, 0) }}</span>
                            @endif
                        </div>
                        <a href="{{ route('packages.show', $package->slug) }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">View Details</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-12">
                <i class="fa-solid fa-suitcase text-4xl text-slate-300 mb-4"></i>
                <h3 class="text-lg font-medium text-slate-900">No packages found</h3>
                <p class="text-slate-600">Try adjusting your search or filter criteria.</p>
                <a href="{{ route('packages.index') }}" class="mt-3 inline-block text-indigo-600 text-sm font-medium hover:underline">Clear all filters</a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    {{ $packages->links() }}

</div>
@endsection