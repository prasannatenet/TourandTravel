@extends('layouts.app')
@section('title', $package->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700">
            <i class="fa-solid fa-arrow-left"></i> Back to packages
        </a>
    </div>

    <!-- Hero Section -->
    <section class="relative rounded-2xl overflow-hidden mb-8 shadow-lg border border-slate-200">
        @if($package->image)
            <img src="{{ asset('storage/'.$package->image) }}" alt="{{ $package->name }}" class="w-full h-80 object-cover">
        @else
            <div class="w-full h-80 bg-slate-100 flex items-center justify-center">
                <i class="fa-solid fa-image text-4xl text-slate-400"></i>
            </div>
        @endif

        <!-- Badges Overlay -->
        <div class="absolute top-4 left-4 flex flex-col gap-2">
            @if($package->category)
                <span class="inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-violet-700 shadow-sm">{{ $package->category }}</span>
            @endif
            <span class="inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">{{ $package->tour_type }} Tour</span>
        </div>

        <!-- Duration Badge -->
        <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur px-4 py-2 rounded-xl shadow-sm border border-slate-200">
            <span class="flex items-center gap-1 text-sm font-bold text-slate-900">
                <i class="fa-solid fa-calendar-days text-indigo-600"></i>
                {{ $package->nights }} Night{{ $package->nights != 1 ? 's' : '' }} / {{ $package->days }} Day{{ $package->days != 1 ? 's' : '' }}
            </span>
        </div>
    </section>

    <!-- Gallery Thumbnails -->
    @if($package->galleryImages->isNotEmpty())
        <div class="grid grid-cols-4 gap-2 mb-8">
            @foreach($package->galleryImages->take(4) as $img)
                <img src="{{ asset('storage/'.$img->image) }}" alt="Gallery" class="w-full h-20 rounded-lg object-cover cursor-pointer hover:opacity-80 transition">
            @endforeach
        </div>
    @endif

    <!-- Package Header -->
    <div class="mb-8">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Tour Package</p>
                <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">{{ $package->name }}</h2>
                @if($package->description)
                    <p class="mt-3 max-w-4xl whitespace-pre-line text-sm leading-7 text-slate-600">{{ $package->description }}</p>
    <!-- Inclusions -->
    @if($package->inclusionItems->isNotEmpty())
        <section class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-900">Inclusions</h3>
                <span class="text-sm text-indigo-600 font-medium">{{ $package->inclusionItems->count() }} included</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($package->inclusionItems as $item)
                    @php
                        $inc = $inclusionDetails[$item->id] ?? [];
                        $incName = $inc['name'] ?? $item->inclusion?->name ?? 'Inclusion';
                    @endphp
                    <div class="flex items-center gap-3 rounded-lg bg-white border border-slate-200 p-3 shadow-sm">
                        @if(isset($inc['image_one']))
                            <img src="{{ asset('storage/'.$inc['image_one']) }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 line-clamp-1">{{ $incName }}</p>
                            @if(isset($inc['place_from']) && $inc['place_from'])
                                <p class="text-xs text-slate-500">{{ $inc['place_from'] }}{{ isset($inc['place_to']) && $inc['place_to'] ? ' → '.$inc['place_to'] : '' }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Day-by-Day Itinerary -->
    <section class="mb-8">
        <h3 class="text-xl font-bold text-slate-900 mb-6">Day-by-Day Itinerary</h3>

        @if($itinerary->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500">
                <i class="fa-solid fa-calendar text-3xl mb-3"></i>
                <p>No detailed itinerary available.</p>
            </div>
        @else
            <div class="space-y-5">
                @foreach($itinerary as $dayNumber => $items)
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-100">
                            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg">{{ $dayNumber }}</div>
                            <h4 class="text-lg font-bold text-slate-900">Day {{ $dayNumber }}</h4>
                        </div>
                        <div class="space-y-3">
                            @foreach($items as $item)
                                @php
                                    $inc = $inclusionDetails[$item->package_inclusion_package_id ?? $item->id] ?? [];
                                    $incName = $inc['name'] ?? ($item->packageInclusionItem?->inclusion?->name ?? ($item->place?->name ?? 'Activity'));
                                @endphp
                                <article class="flex gap-3 rounded-lg p-3 {{ $item->package_inclusion_package_id ? 'bg-emerald-50/60 border border-emerald-100' : 'bg-slate-50 border border-slate-100' }}">
                                    <span class="w-16 shrink-0 pt-0.5 text-xs font-semibold {{ $item->package_inclusion_package_id ? 'text-emerald-700' : 'text-indigo-700' }}">
                                        {{ substr((string) $item->start_time, 0, 5) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <h5 class="text-sm font-semibold text-slate-800 leading-snug">{{ $incName }}</h5>
                                        @if(isset($inc['place_from']) && $inc['place_from'])
                                            <p class="mt-1 text-xs text-slate-500">{{ $inc['place_from'] }}{{ isset($inc['place_to']) && $inc['place_to'] ? ' → '.$inc['place_to'] : '' }}</p>
                                        @elseif($item->place?->city)
                                            <p class="mt-1 text-xs text-slate-500">{{ $item->place->city->name }}</p>
                                        @endif
                                        @if((float) $item->additional_price > 0)
                                            <p class="mt-1 text-xs text-slate-500">Extra cost: ₹{{ number_format((float) $item->additional_price, 0) }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
                @endif
            </div>
            <div class="text-right hidden sm:block">
                <div class="text-2xl font-bold text-slate-900">₹{{ number_format((float) $package->base_price, 0) }}</div>
                <div class="text-sm text-slate-500">per adult</div>
                @if($package->child_price)
                    <div class="text-sm text-slate-500 mt-1">Child: ₹{{ number_format((float) $package->child_price, 0) }}</div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-xl bg-slate-50 p-4">
                <dt class="text-xs text-slate-500 uppercase font-semibold">Departure City</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $package->departure_city ?: 'Not specified' }}</dd>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
                <dt class="text-xs text-slate-500 uppercase font-semibold">Duration</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $package->days }} Days / {{ $package->nights }} Nights</dd>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
                <dt class="text-xs text-slate-500 uppercase font-semibold">Adult Price</dt>
                <dd class="mt-1 font-semibold text-slate-800">₹{{ number_format((float) $package->base_price, 0) }}</dd>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
                <dt class="text-xs text-slate-500 uppercase font-semibold">Child Price</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $package->child_price ? '₹'.number_format((float) $package->child_price, 0) : 'N/A' }}</dd>
            </div>
        </div>
    </div>
    <!-- Pricing Summary -->
    <section class="mb-8 rounded-2xl border border-slate-200 bg-slate-50 p-6 md:p-8 shadow-sm">
        <h3 class="text-xl font-bold text-slate-900 mb-6">Pricing Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Package Base Price (Adult)</span>
                    <span class="font-semibold text-slate-900">₹{{ number_format((float) $package->base_price, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Plan Activities &amp; Places</span>
                    <span class="text-slate-500">₹{{ number_format($subtotal - (float) $package->base_price, 2) }}</span>
                </div>
                <div class="border-t border-slate-200 pt-2 flex justify-between text-sm font-semibold">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($subtotal, 2) }}</span>
                </div>
                @if((float) $package->discount_percentage > 0)
                    <div class="flex justify-between text-sm text-emerald-600">
                        <span>Discount ({{ (float) $package->discount_percentage }}%)</span>
                        <span>-₹{{ number_format($subtotal * ((float) $package->discount_percentage / 100), 2) }}</span>
                    </div>
                @endif
                @if((float) $package->tax_percentage > 0)
                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Tax ({{ (float) $package->tax_percentage }}%)</span>
                        <span>₹{{ number_format($discountedTotal * ((float) $package->tax_percentage / 100), 2) }}</span>
                    </div>
                @endif
                <div class="border-t border-slate-300 pt-3 flex justify-between text-xl font-bold text-slate-900">
                    <span>Estimated Total</span>
                    <span>₹{{ number_format($estimatedTotal, 2) }}</span>
                </div>
            </div>
            <div class="space-y-3">
                <div class="p-4 rounded-xl bg-indigo-600 text-white">
                    <div class="text-xs text-indigo-100 uppercase font-semibold mb-2">Per Adult (Estimated)</div>
                    <div class="text-3xl font-bold">₹{{ number_format($estimatedTotal / max($package->days, 1), 0) }} / day</div>
                </div>
                <div class="text-xs text-slate-500 text-center">
                    Prices are indicative and may vary based on travel date, availability, and taxes.
                </div>
                <a href="#booking" class="w-full block text-center py-3 rounded-lg bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 transition">Book This Package</a>
            </div>
        </div>
    </section>

    <!-- Availability -->
    @if($package->availabilities->isNotEmpty())
        <section class="mb-8" id="booking">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Available Departure Dates</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($package->availabilities as $availability)
                    <div class="rounded-lg border p-4 text-center {{ $availability->seat_limit > 0 ? 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm cursor-pointer transition' : 'border-red-200 bg-red-50 text-red-600' }}">
                        <p class="text-xs text-slate-500 uppercase font-semibold">{{ $availability->departure_date->format('M d') }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $availability->departure_date->format('Y') }}</p>
                        @if($availability->seat_limit > 0)
                            <p class="text-sm text-emerald-600 font-medium mt-1">{{ $availability->seat_limit }} seats left</p>
                            @if($availability->adult_price_override)
                                <p class="text-xs text-slate-500 mt-1">Special price: ₹{{ number_format((float) $availability->adult_price_override, 0) }}</p>
                            @endif
                        @else
                            <p class="text-sm text-red-600 font-medium mt-1">Fully booked</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection