@extends('layouts.app')

@section('title', $package->name . ' - Holiday Package')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">
<style>
    .mmt-holiday-page {
        font-family: 'Lato', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f2f5fa;
        color: #1a1a1a;
        min-height: 100vh;
    }

    .mmt-holiday-page .container-main {
        max-width: 1220px;
        margin: auto;
        padding: 18px 16px 60px;
    }

    .mmt-holiday-page .panel {
        background: #ffffff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    .mmt-blue {
        color: #008cff;
    }

    .mmt-bg-blue {
        background-color: #008cff;
    }

    .mmt-btn-primary {
        background: linear-gradient(93deg, #ff4f17, #ff6a00);
        color: white;
        border-radius: 10px;
        padding: 13px 20px;
        font-weight: 800;
        font-size: 14px;
        letter-spacing: .3px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        transition: all .2s ease;
        box-shadow: 0 4px 14px rgba(255, 79, 23, 0.3);
    }

    .mmt-btn-primary:hover {
        background: linear-gradient(93deg, #e63e07, #f05a00);
        box-shadow: 0 6px 18px rgba(255, 79, 23, 0.4);
        transform: translateY(-1px);
    }

    .mmt-btn-blue {
        background: linear-gradient(93deg, #008cff, #0073e6);
        color: white;
        border-radius: 10px;
        padding: 11px 18px;
        font-weight: 700;
        font-size: 13px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        transition: all .2s ease;
        box-shadow: 0 3px 10px rgba(0, 140, 255, 0.25);
    }

    .mmt-btn-blue:hover {
        background: linear-gradient(93deg, #0077db, #005fbf);
    }

    /* MakeMyTrip Asymmetric Hero Gallery */
    .mmt-gallery {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 10px;
        height: 380px;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }

    .mmt-gallery-main,
    .mmt-gallery-thumb {
        position: relative;
        overflow: hidden;
        background: #e2e8f0;
        cursor: pointer;
    }

    .mmt-gallery-main img,
    .mmt-gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .45s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .mmt-gallery-main:hover img,
    .mmt-gallery-thumb:hover img {
        transform: scale(1.03);
    }

    .mmt-gallery-side {
        display: grid;
        grid-template-rows: 1fr 1fr;
        gap: 10px;
        min-height: 0;
    }

    /* Sticky Navigation Tabs (MMT Anchor Bar) */
    .mmt-subnav {
        position: sticky;
        top: 0;
        z-index: 40;
        background: #ffffff;
        border-bottom: 1px solid #e5e9f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        margin-left: -16px;
        margin-right: -16px;
        padding: 0 16px;
    }

    .mmt-subnav-inner {
        max-width: 1220px;
        margin: auto;
        display: flex;
        gap: 28px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .mmt-subnav-inner::-webkit-scrollbar {
        display: none;
    }

    .mmt-nav-tab {
        padding: 15px 4px;
        font-size: 13px;
        font-weight: 800;
        color: #556271;
        letter-spacing: .4px;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
        cursor: pointer;
        transition: all .2s;
    }

    .mmt-nav-tab:hover {
        color: #008cff;
    }

    .mmt-nav-tab.active {
        color: #008cff;
        border-bottom-color: #008cff;
    }

    /* Highlights Strip (MMT Icons) */
    .mmt-highlight-card {
        background: #ffffff;
        border: 1px solid #e7ecf3;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: transform .2s ease, border-color .2s ease;
    }

    .mmt-highlight-card:hover {
        transform: translateY(-2px);
        border-color: #c9d8eb;
    }

    .mmt-highlight-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    /* Day Navigator Pills (MMT Day Bar) */
    .mmt-day-pills {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 6px;
        scrollbar-width: none;
    }

    .mmt-day-pills::-webkit-scrollbar {
        display: none;
    }

    .mmt-day-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #48566a;
        white-space: nowrap;
        cursor: pointer;
        transition: all .2s;
    }

    .mmt-day-pill:hover {
        border-color: #008cff;
        color: #008cff;
    }

    .mmt-day-pill.active {
        background: #008cff;
        border-color: #008cff;
        color: #ffffff;
        box-shadow: 0 3px 10px rgba(0, 140, 255, 0.3);
    }

    .mmt-day-pill.active .mmt-day-pill-city {
        color: #e0f2fe;
    }

    .mmt-day-pill-city {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
    }

    /* Timeline Stops (MMT Activity Card) */
    .mmt-timeline {
        position: relative;
        padding-left: 28px;
    }

    .mmt-timeline::before {
        content: "";
        position: absolute;
        left: 10px;
        top: 20px;
        bottom: 20px;
        width: 2px;
        background: #cbd5e1;
    }

    .mmt-timeline-stop {
        position: relative;
        margin-bottom: 24px;
    }

    .mmt-timeline-node {
        position: absolute;
        left: -28px;
        top: 18px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #008cff;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    /* MakeMyTrip Swap / Change Button */
    .mmt-change-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border: 1.5px solid #008cff;
        border-radius: 8px;
        background: #f0f7ff;
        color: #008cff;
        font-size: 12px;
        font-weight: 800;
        transition: all .2s;
        cursor: pointer;
    }

    .mmt-change-btn:hover {
        background: #008cff;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 140, 255, 0.25);
    }

    /* Photo Strip for Activities */
    .mmt-photo-strip {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
        margin-top: 12px;
    }

    .mmt-photo-thumb {
        flex-shrink: 0;
        width: 96px;
        height: 68px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 1.5px solid #e2e8f0;
        transition: all .2s;
    }

    .mmt-photo-thumb:hover {
        border-color: #008cff;
        transform: scale(1.03);
    }

    /* Right Sticky Price Card (MMT Signature Sidebar) */
    .mmt-price-sidebar {
        position: sticky;
        top: 75px;
    }

    .mmt-price-card {
        background: #ffffff;
        border: 1px solid #e5eaf2;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .mmt-price-highlight {
        animation: mmtPriceGlow 0.6s ease-out;
    }

    @keyframes mmtPriceGlow {
        0% {
            color: #008cff;
            transform: scale(1.08);
        }
        100% {
            color: #0f172a;
            transform: scale(1);
        }
    }

    .mmt-card-flash {
        animation: mmtCardFlash 1.1s ease-out;
    }

    @keyframes mmtCardFlash {
        0% {
            background-color: #eff6ff;
            border-color: #008cff;
        }
        100% {
            background-color: #ffffff;
            border-color: #e7ecf3;
        }
    }

    /* Inclusions vs Exclusions Cards */
    .mmt-inc-badge {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #ecfdf5;
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex-shrink: 0;
    }

    .mmt-exc-badge {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fef2f2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex-shrink: 0;
    }

    /* Modal Overlay & Dialog */
    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(10, 20, 35, 0.78);
        backdrop-filter: blur(4px);
    }

    .modal-overlay.open {
        display: flex;
    }

    .mmt-change-modal-content {
        width: min(720px, 95vw);
        height: min(650px, 86vh);
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.4);
        animation: mmtModalIn 0.22s ease-out;
    }

    @keyframes mmtModalIn {
        from {
            opacity: 0;
            transform: scale(0.96) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    #modalPlacesList {
        flex: 1 1 0%;
        min-height: 0;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    #modalPlacesList::-webkit-scrollbar {
        width: 7px;
    }

    #modalPlacesList::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 4px;
    }

    #modalPlacesList::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    #modalPlacesList::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Place Select Card inside Modal */
    .mmt-place-select-card {
        display: flex;
        gap: 14px;
        padding: 14px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.15s ease;
    }

    .mmt-place-select-card:hover {
        border-color: #008cff;
        box-shadow: 0 4px 14px rgba(0, 140, 255, 0.1);
    }

    .mmt-place-select-card.is-current {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .mmt-place-select-thumb {
        width: 86px;
        height: 86px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        background: #f1f5f9;
    }

    /* Mobile Sticky Bar */
    .mmt-mobile-bar {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        padding: 12px 18px;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
    }

    @media (max-width: 991px) {
        .mmt-mobile-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .mmt-price-sidebar {
            position: static;
        }
        .mmt-gallery {
            height: 280px;
        }
    }

    @media (max-width: 640px) {
        .mmt-gallery {
            height: 220px;
            grid-template-columns: 1fr;
        }
        .mmt-gallery-side {
            display: none;
        }
        .container-main {
            padding: 12px 10px 90px !important;
        }
    }
</style>
@endpush

@section('content')

@php
    $galleryImages = collect();

    if ($package->image) {
        $galleryImages->push($package->image);
    }

    foreach ($package->galleryImages as $galleryImage) {
        if ($galleryImage->image) {
            $galleryImages->push($galleryImage->image);
        }
    }

    $galleryImages = $galleryImages->unique()->values();

    $transferItems = $package->planItems->filter(function ($item) {
        $name = strtolower(
            ($item->packageInclusionItem?->inclusion?->name
            ?? $item->place?->name ?? '')
        );

        return str_contains($name, 'pickup')
            || str_contains($name, 'drop')
            || str_contains($name, 'transfer')
            || str_contains($name, 'car');
    })->count();

    $hotelCount = $package->planItems
        ->filter(fn ($item) => $item->item_type === 'hotel')
        ->count();

    $mealItems = $package->inclusionItems->filter(function ($item) {
        $name = strtolower($item->inclusion?->name ?? '');

        return str_contains($name, 'breakfast')
            || str_contains($name, 'lunch')
            || str_contains($name, 'dinner')
            || str_contains($name, 'meal');
    })->count();

    $discount = (float) ($package->discount_percentage ?? 0);
    $price = (float) $discountedTotal;
    $originalPrice = $discount > 0
        ? $price / (1 - $discount / 100)
        : (float) $estimatedTotal;

    $adultPrice = (float) $estimatedTotal;

    $planPricingItems = $package->planItems->map(fn ($item) => [
        'id' => $item->id,
        'day' => $item->day_number,
        'kind' => $item->item_type === 'hotel' ? 'hotel' : ($item->package_inclusion_package_id ? 'inclusion' : 'place'),
        'can_change' => (bool) $item->show_charge_button && (bool) $item->place,
        'current_place_id' => $item->place?->id,
        'original_place_id' => $item->place?->id,
        'place_price' => (float) ($item->place?->price_per_person ?? 0),
        'original_place_price' => (float) ($item->place?->price_per_person ?? 0),
        'additional_price' => (float) ($item->additional_price ?? 0),
        'hotel_adult_price' => (float) ($item->hotelRoom?->customer_adult_price ?? 0),
        'place_name' => $item->place?->name ?? '',
        'original_place_name' => $item->place?->name ?? '',
        'city_id' => $item->place?->city_id,
        'city_name' => $item->place?->city?->name ?? '',
        'original_description' => $item->place?->place_description ?? $item->place?->description ?? '',
        'original_primary_image' => $item->place?->primary_image ? asset('storage/'.$item->place->primary_image) : null,
        'original_secondary_images' => collect($item->place?->secondary_images ?? [])->map(fn ($img) => asset('storage/'.$img))->values()->all(),
    ])->values()->all();

    // Unique cities breakdown (e.g. "2N Jaipur · 2N Jodhpur")
    $citiesBreakdown = $itinerary->map(function ($dayItems) {
        $place = $dayItems->first(fn ($i) => $i->place)?->place;
        return $place?->city?->name;
    })->filter()->values();
@endphp

<div class="mmt-holiday-page">
    <div class="container-main">

        {{-- MakeMyTrip Breadcrumb --}}
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mb-3">
            <a href="/" class="hover:text-blue-600">Home</a>
            <i class="fa-solid fa-chevron-right text-[8px] text-slate-400"></i>
            <a href="{{ route('packages.index') }}" class="hover:text-blue-600">Holidays</a>
            <i class="fa-solid fa-chevron-right text-[8px] text-slate-400"></i>
            <span>{{ $package->category ?? 'India Tour Packages' }}</span>
            <i class="fa-solid fa-chevron-right text-[8px] text-slate-400"></i>
            <span class="text-slate-800 font-bold truncate max-w-xs">{{ $package->name }}</span>
        </div>

        {{-- MakeMyTrip Package Title & Meta Header --}}
        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="bg-slate-900 text-white font-extrabold text-[11px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                        {{ $package->nights }}N / {{ $package->days }}D
                    </span>

                    <span class="bg-blue-50 text-blue-700 font-bold text-xs px-2.5 py-0.5 rounded-full border border-blue-200">
                        <i class="fa-solid fa-route mr-1"></i>
                        {{ $citiesBreakdown->unique()->implode(' · ') ?: ($package->departure_city ?? 'Multi-City Tour') }}
                    </span>

                    <span class="bg-purple-50 text-purple-700 font-bold text-xs px-2.5 py-0.5 rounded-full border border-purple-200 flex items-center gap-1">
                        <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                        Customizable Package
                    </span>
                </div>

                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">
                    {{ $package->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 mt-2">
                    <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        <i class="fa-solid fa-star text-[10px] text-emerald-600"></i> 4.8 / 5
                    </span>
                    <span class="text-slate-400">•</span>
                    <span><i class="fa-solid fa-location-dot text-blue-500 mr-1"></i>Starts &amp; ends in <strong>{{ $package->departure_city ?? 'Jaipur' }}</strong></span>
                    <span class="text-slate-400">•</span>
                    <span><i class="fa-solid fa-car text-blue-500 mr-1"></i>Dedicated Private AC Cab</span>
                </div>
            </div>

            {{-- Share & Save Buttons --}}
            <div class="flex items-center gap-2">
                <button type="button" onclick="sharePackage()"
                    class="h-9 px-3.5 rounded-full border border-slate-300 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 flex items-center gap-1.5 shadow-sm transition"
                    title="Share Package">
                    <i class="fa-solid fa-share-nodes text-blue-600"></i>
                    <span>Share</span>
                </button>

                <button type="button" onclick="toggleWishlist(this)"
                    class="w-9 h-9 rounded-full border border-slate-300 bg-white hover:bg-red-50 text-slate-600 flex items-center justify-center shadow-sm transition"
                    title="Shortlist">
                    <i class="fa-regular fa-heart"></i>
                </button>
            </div>
        </div>

        {{-- MakeMyTrip 3-Photo Hero Grid --}}
        <div class="mmt-gallery mb-6">
            <div class="mmt-gallery-main" onclick="openGallery(0)">
                @if($galleryImages->isNotEmpty())
                    <img src="{{ asset('storage/'.$galleryImages[0]) }}" alt="{{ $package->name }}">
                @else
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 bg-slate-100">
                        <i class="fa-solid fa-mountain-sun text-5xl mb-2 text-slate-300"></i>
                        <span class="text-xs font-semibold">Explore {{ $package->name }}</span>
                    </div>
                @endif

                <div class="absolute bottom-4 left-4 bg-black/70 backdrop-blur-md text-white rounded-lg px-3.5 py-1.5 text-xs font-bold flex items-center gap-2 shadow-lg">
                    <i class="fa-regular fa-images"></i>
                    <span>View All Photos ({{ max(1, $galleryImages->count()) }})</span>
                </div>
            </div>

            <div class="mmt-gallery-side">
                @for($i = 1; $i <= 2; $i++)
                    <div class="mmt-gallery-thumb" onclick="openGallery({{ $i < $galleryImages->count() ? $i : 0 }})">
                        @if(isset($galleryImages[$i]))
                            <img src="{{ asset('storage/'.$galleryImages[$i]) }}" alt="Photo {{ $i + 1 }}">
                        @elseif(isset($galleryImages[0]))
                            <img src="{{ asset('storage/'.$galleryImages[0]) }}" alt="{{ $package->name }}">
                        @else
                            <div class="h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                <i class="fa-regular fa-image text-2xl text-slate-300"></i>
                            </div>
                        @endif

                        @if($i === 2 && $galleryImages->count() > 3)
                            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center text-white font-extrabold text-sm">
                                +{{ $galleryImages->count() - 3 }} More Photos
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        {{-- MakeMyTrip Key Inclusions Strip (Highlights) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="mmt-highlight-card">
                <div class="mmt-highlight-icon bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-hotel"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Hotel Stay</p>
                    <p class="text-xs md:text-sm font-bold text-slate-900 mt-0.5">{{ $package->nights }} Nights Accommodation</p>
                </div>
            </div>

            <div class="mmt-highlight-card">
                <div class="mmt-highlight-icon bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-car"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Transfers &amp; Cab</p>
                    <p class="text-xs md:text-sm font-bold text-slate-900 mt-0.5">Private AC Cab for Sightseeing</p>
                </div>
            </div>

            <div class="mmt-highlight-card">
                <div class="mmt-highlight-icon bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-monument"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Activities</p>
                    <p class="text-xs md:text-sm font-bold text-slate-900 mt-0.5">Key Forts &amp; Attractions</p>
                </div>
            </div>

            <div class="mmt-highlight-card">
                <div class="mmt-highlight-icon bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Meals</p>
                    <p class="text-xs md:text-sm font-bold text-slate-900 mt-0.5">Daily Breakfast Included</p>
                </div>
            </div>
        </div>

        {{-- Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT COLUMN: ITINERARY & DETAILS --}}
            <div class="lg:col-span-8 min-w-0 space-y-6">

                {{-- MakeMyTrip Sticky Sub-Navigation Bar --}}
                <div class="mmt-subnav">
                    <div class="mmt-subnav-inner">
                        <button type="button" class="mmt-nav-tab active" data-target="section-itinerary">
                            ITINERARY
                        </button>
                        <button type="button" class="mmt-nav-tab" data-target="section-inclusions">
                            INCLUSIONS &amp; EXCLUSIONS
                        </button>
                        <button type="button" class="mmt-nav-tab" data-target="section-overview">
                            ABOUT PACKAGE
                        </button>
                        <button type="button" class="mmt-nav-tab" data-target="section-policies">
                            POLICIES
                        </button>
                    </div>
                </div>

                {{-- SECTION 1: ITINERARY --}}
                <section id="section-itinerary" class="panel p-5 md:p-6 scroll-mt-20">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="text-lg md:text-xl font-black text-slate-900">
                                Detailed Day-wise Itinerary
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Customizable itinerary. You can change activities where indicated.
                            </p>
                        </div>
                        <span class="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                            {{ $itinerary->count() }} Days Schedule
                        </span>
                    </div>

                    {{-- Horizontal Day Selector Pills (MMT Day Bar) --}}
                    <div class="mmt-day-pills mb-6 pb-2 border-b border-slate-100">
                        @foreach($itinerary as $dayNumber => $items)
                            @php
                                $firstPlace = $items->first(fn ($i) => $i->place)?->place;
                                $firstHotel = $items->first(fn ($i) => $i->item_type === 'hotel')?->hotelRoom?->hotel;
                                $dayCity = $firstPlace?->city?->name ?? $firstHotel?->city?->name ?? 'Tour';
                            @endphp
                            <button type="button"
                                class="mmt-day-pill {{ $loop->first ? 'active' : '' }}"
                                data-day="{{ $dayNumber }}"
                                onclick="switchDay({{ $dayNumber }})">
                                <span>Day {{ $dayNumber }}</span>
                                <span class="mmt-day-pill-city">({{ $dayCity }})</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Day Panels --}}
                    <div id="itinerary-days-container">
                        @foreach($itinerary as $dayNumber => $items)
                            @php
                                $dayPlace = $items->first(fn ($i) => $i->place)?->place;
                                $dayHotel = $items->first(fn ($i) => $i->item_type === 'hotel')?->hotelRoom?->hotel;
                                $dayCityName = $dayPlace?->city?->name ?? $dayHotel?->city?->name ?? 'Sightseeing';
                            @endphp

                            <div id="day-panel-{{ $dayNumber }}" class="day-panel {{ $loop->first ? '' : 'hidden' }}">
                                {{-- Day Header Banner --}}
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50/40 border border-blue-100 rounded-xl p-4 mb-5 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-black text-sm flex items-center justify-center shadow-sm">
                                            D{{ $dayNumber }}
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-sm md:text-base text-slate-900">
                                                Day {{ $dayNumber }}: Experience {{ $dayCityName }}
                                            </h3>
                                            <p class="text-xs text-slate-600 mt-0.5">
                                                <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i>
                                                {{ $dayCityName }} Sightseeing &amp; Travel
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                        {{ $items->count() }} Planned Stops
                                    </span>
                                </div>

                                {{-- Vertical Timeline Stops --}}
                                <div class="mmt-timeline">
                                    @foreach($items as $item)
                                        @php
                                            $inc = $inclusionDetails[$item->package_inclusion_package_id] ?? [];
                                            $isHotel = $item->item_type === 'hotel';
                                            $incName = $inc['name']
                                                ?? $item->packageInclusionItem?->inclusion?->name
                                                ?? ($isHotel
                                                    ? ($item->hotelRoom
                                                        ? (($item->hotelRoom->hotel?->name ?? 'Hotel').' · '.($item->hotelRoom->roomType?->name ?? 'Room type').' · '.$item->hotelRoom->name)
                                                        : 'Hotel unavailable')
                                                    : null)
                                                ?? $item->place?->name
                                                ?? 'Activity';
                                            $isInclusion = (bool) $item->package_inclusion_package_id;
                                            $hotelRoomImages = collect($item->hotelRoom?->images ?? []);
                                            $image = $isHotel
                                                ? ($hotelRoomImages->first() ?? $item->hotelRoom?->hotel?->primary_image)
                                                : ($isInclusion
                                                ? ($inc['image_one'] ?? null)
                                                : ($item->place?->primary_image ?? null));
                                            $secondaryImages = $isHotel
                                                ? ($hotelRoomImages->isNotEmpty()
                                                    ? $hotelRoomImages->skip(1)->values()->all()
                                                    : ($item->hotelRoom?->hotel?->secondary_images ?? []))
                                                : ((!$isInclusion && $item->place)
                                                ? ($item->place->secondary_images ?? [])
                                                : []);
                                            $description = $isHotel
                                                ? ($item->hotelRoom?->description ?? null)
                                                : ($isInclusion
                                                ? ($inc['description'] ?? null)
                                                : ($item->place?->place_description ?? $item->place?->description ?? null));
                                        @endphp

                                        <div class="mmt-timeline-stop" id="plan-item-{{ $item->id }}" data-item-id="{{ $item->id }}" data-place-id="{{ $item->place?->id }}">
                                            <div class="mmt-timeline-node">
                                                <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                            </div>

                                            <div class="panel p-4 md:p-5 border border-slate-200/90 hover:border-blue-300 transition-all">
                                                {{-- Stop Top Row --}}
                                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                                    <div class="flex items-center gap-2">
                                                        @if($item->start_time)
                                                            <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md flex items-center gap-1">
                                                                <i class="fa-regular fa-clock text-blue-500 text-[11px]"></i>
                                                                {{ substr((string) $item->start_time, 0, 5) }}
                                                            </span>
                                                        @endif

                                                        @if($isHotel)
                                                            <span class="text-[10px] font-extrabold uppercase rounded px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200">
                                                                Hotel Check-in
                                                            </span>
                                                        @elseif($isInclusion)
                                                            <span class="text-[10px] font-extrabold uppercase rounded px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                Included Service
                                                            </span>
                                                        @else
                                                            <span class="text-[10px] font-extrabold uppercase rounded px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200">
                                                                Sightseeing Activity
                                                            </span>
                                                        @endif

                                                        <span class="plan-item-custom-tag hidden text-[10px] font-extrabold uppercase rounded px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                            ✓ Customized
                                                        </span>
                                                    </div>

                                                    {{-- MMT Change Button --}}
                                                    @if($item->show_charge_button && !$isInclusion && $item->place)
                                                        <div class="flex items-center gap-2">
                                                            <button type="button"
                                                                class="mmt-change-btn"
                                                                onclick="openChangePlaceModal({{ $item->id }})">
                                                                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                                                <span class="change-btn-label">Change Activity</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Stop Title & City --}}
                                                <h4 class="font-black text-base text-slate-900 plan-item-title leading-snug">
                                                    {{ $incName }}
                                                </h4>

                                                @if($isHotel && $item->hotelRoom?->hotel?->city)
                                                    <p class="text-xs text-slate-500 mt-0.5 plan-item-location">
                                                        <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i>
                                                        <span class="plan-item-city-name">{{ $item->hotelRoom->hotel->city->name }}</span>
                                                        @if($item->hotelRoom->hotel->address)
                                                            · {{ $item->hotelRoom->hotel->address }}
                                                        @endif
                                                    </p>
                                                @elseif($item->place?->city)
                                                    <p class="text-xs text-slate-500 mt-0.5 plan-item-location">
                                                        <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i>
                                                        <span class="plan-item-city-name">{{ $item->place->city->name }}</span>
                                                        @if($item->place->price_per_person > 0)
                                                            <span class="text-slate-400 mx-1">•</span>
                                                            <span class="text-slate-600 font-semibold">Attraction Fee: ₹{{ number_format((float) $item->place->price_per_person, 0) }}/person</span>
                                                        @endif
                                                    </p>
                                                @endif

                                                {{-- Description --}}
                                                <p class="text-xs md:text-sm text-slate-600 mt-2 leading-relaxed plan-item-desc">
                                                    {{ $description ?: ($isHotel
                                                        ? 'Check in to your selected hotel and room.'
                                                        : ($isInclusion
                                                            ? 'Enjoy this service as part of your customized holiday package.'
                                                            : 'Explore this destination and experience the rich local culture.')) }}
                                                </p>

                                                @if($isHotel && $item->hotelRoom)
                                                    @if($item->hotelRoom->hotel)
                                                        <p class="mt-1 text-xs font-semibold text-amber-700">{{ $item->hotelRoom->hotel->star_rating }}-star hotel</p>
                                                    @endif
                                                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                                        <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-semibold text-blue-800">₹{{ number_format((float) $item->hotelRoom->customer_adult_price, 2) }} per adult/night</span>
                                                        <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-semibold text-blue-800">₹{{ number_format((float) $item->hotelRoom->customer_child_price, 2) }} per child/night</span>
                                                        <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-semibold text-blue-800">₹{{ number_format((float) $item->hotelRoom->customer_extra_bed_price, 2) }} per extra bed/night</span>
                                                    </div>
                                                    <p class="mt-1 text-[11px] font-semibold text-blue-800">₹{{ number_format((float) $item->hotelRoom->customer_adult_price * (int) $package->nights, 2) }} per adult for {{ $package->nights }} nights is included in the package price (₹{{ number_format((float) $item->hotelRoom->customer_adult_price * (int) $package->nights * 2, 2) }} for 2 adults).</p>
                                                @endif

                                                {{-- Customization Status Indicator --}}
                                                <div class="plan-custom-status hidden items-center justify-between gap-2 mt-3 pt-2 border-t border-slate-100 bg-emerald-50/70 p-2.5 rounded-lg border border-emerald-200">
                                                    <span class="text-xs font-bold text-emerald-800">
                                                        <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i>
                                                        Replaced with: <strong class="custom-new-place-name"></strong>
                                                    </span>
                                                    <button type="button"
                                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 underline cursor-pointer"
                                                        onclick="resetPlanItem({{ $item->id }})">
                                                        Reset to Original
                                                    </button>
                                                </div>

                                                {{-- Place photo strip: primary + secondary images --}}
                                                <div class="mmt-photo-strip plan-item-photo-strip" @if(!$image && count($secondaryImages) === 0) style="display:none;" @endif>
                                                    @if($image)
                                                        <img
                                                            src="{{ asset('storage/'.$image) }}"
                                                            alt="{{ $incName }}"
                                                            class="mmt-photo-thumb"
                                                            loading="lazy"
                                                            onclick="openGalleryImage('{{ asset('storage/'.$image) }}')"
                                                            title="{{ $incName }} - Click to view">
                                                    @endif
                                                    @foreach($secondaryImages as $secImg)
                                                        <img
                                                            src="{{ asset('storage/'.$secImg) }}"
                                                            alt="{{ $incName }}"
                                                            class="mmt-photo-thumb"
                                                            loading="lazy"
                                                            onclick="openGalleryImage('{{ asset('storage/'.$secImg) }}')"
                                                            title="{{ $incName }} - Click to view">
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- SECTION 2: INCLUSIONS & EXCLUSIONS (MakeMyTrip 2-Column Standard) --}}
                <section id="section-inclusions" class="panel p-5 md:p-6 scroll-mt-20">
                    <h2 class="text-lg md:text-xl font-black text-slate-900 mb-4">
                        Inclusions &amp; Exclusions
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- What's Included --}}
                        <div class="border border-emerald-200 rounded-xl bg-emerald-50/30 p-4">
                            <h3 class="text-sm font-bold text-emerald-800 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                                What's Included
                            </h3>

                            <ul class="space-y-2.5 text-xs text-slate-700">
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-inc-badge"><i class="fa-solid fa-check"></i></span>
                                    <span>Hotel accommodation for <strong>{{ $package->nights }} Nights</strong> with selected room category.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-inc-badge"><i class="fa-solid fa-check"></i></span>
                                    <span>Daily fresh buffet breakfast at the hotel restaurant.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-inc-badge"><i class="fa-solid fa-check"></i></span>
                                    <span>Dedicated Private AC Sedan/SUV for all transfers and city sightseeing.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-inc-badge"><i class="fa-solid fa-check"></i></span>
                                    <span>Driver charges, fuel costs, state border permits, toll taxes and parking fees.</span>
                                </li>
                                @foreach($package->inclusionItems as $incItem)
                                    <li class="flex items-start gap-2.5">
                                        <span class="mmt-inc-badge"><i class="fa-solid fa-check"></i></span>
                                        <span>{{ $incItem->inclusion?->name ?? 'Included package service' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- What's Excluded --}}
                        <div class="border border-rose-200 rounded-xl bg-rose-50/30 p-4">
                            <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-circle-xmark text-rose-600 text-base"></i>
                                What's Not Included
                            </h3>

                            <ul class="space-y-2.5 text-xs text-slate-700">
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-exc-badge"><i class="fa-solid fa-xmark"></i></span>
                                    <span>Airfare or train fare from home city (can be added on request).</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-exc-badge"><i class="fa-solid fa-xmark"></i></span>
                                    <span>Monument entry tickets, guide charges &amp; camera fees unless specified.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-exc-badge"><i class="fa-solid fa-xmark"></i></span>
                                    <span>Personal expenses such as laundry, phone calls, beverages, room service.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-exc-badge"><i class="fa-solid fa-xmark"></i></span>
                                    <span>Early check-in or late check-out charges at hotels.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mmt-exc-badge"><i class="fa-solid fa-xmark"></i></span>
                                    <span>Any cost arising due to unforeseen circumstances like flight delay or roadblocks.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- SECTION 3: ABOUT PACKAGE --}}
                <section id="section-overview" class="panel p-5 md:p-6 scroll-mt-20">
                    <h2 class="text-lg md:text-xl font-black text-slate-900 mb-3">
                        About {{ $package->name }}
                    </h2>
                    <div class="text-xs md:text-sm text-slate-600 leading-relaxed space-y-3">
                        <p>
                            {{ $package->description ?: 'Embark on an unforgettable holiday with our thoughtfully designed itinerary. From royal heritage forts and magnificent palaces to vibrant local bazaars and traditional culinary delights, this package is designed to offer the most authentic experience with complete peace of mind.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                            <span class="text-xs text-slate-500 font-semibold">Total Duration</span>
                            <p class="text-sm font-black text-slate-900 mt-1">{{ $package->days }}D / {{ $package->nights }}N</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                            <span class="text-xs text-slate-500 font-semibold">Tour Type</span>
                            <p class="text-sm font-black text-slate-900 mt-1">{{ ucfirst($package->tour_type ?? 'Private') }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                            <span class="text-xs text-slate-500 font-semibold">Departure City</span>
                            <p class="text-sm font-black text-slate-900 mt-1">{{ $package->departure_city ?? 'Jaipur' }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                            <span class="text-xs text-slate-500 font-semibold">Customization</span>
                            <p class="text-sm font-black text-emerald-700 mt-1">Available</p>
                        </div>
                    </div>
                </section>

                {{-- SECTION 4: POLICIES --}}
                <section id="section-policies" class="panel p-5 md:p-6 scroll-mt-20">
                    <h2 class="text-lg md:text-xl font-black text-slate-900 mb-4">
                        Policies &amp; Important Terms
                    </h2>

                    <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-slate-900 mb-1 text-sm">Cancellation &amp; Refund Policy</h3>
                            <p>Cancellation requests made 30+ days before departure are subject to minimal administrative charges. Between 15-29 days, standard cancellation charges apply. Contact our holiday desk for complete details.</p>
                        </div>
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-slate-900 mb-1 text-sm">Hotel Check-in &amp; ID Proofs</h3>
                            <p>Standard check-in time is 12:00 PM and check-out is 10:00 AM. Government-issued photo ID (Aadhaar / Voter ID / Passport) is mandatory for all adult guests during hotel check-in.</p>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 mb-1 text-sm">Sightseeing &amp; Vehicle Usage</h3>
                            <p>The private cab is at your service as per the scheduled itinerary. Any additional or off-route transfers beyond the itinerary will be charged as per actual meter reading.</p>
                        </div>
                    </div>
                </section>

            </div>

            {{-- RIGHT COLUMN: MAKEMYTRIP STICKY PRICE & BOOKING CARD --}}
            <aside class="lg:col-span-4 min-w-0 mmt-price-sidebar">
                <div class="mmt-price-card">
                    {{-- Card Header with Deal Banner --}}
                    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-3.5 flex items-center justify-between text-xs font-bold">
                        <span class="flex items-center gap-1.5 text-amber-400">
                            <i class="fa-solid fa-bolt"></i> Best Price Guarantee
                        </span>
                        @if($discount > 0)
                            <span class="bg-emerald-500 text-white px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                {{ $discount }}% Instant Off
                            </span>
                        @endif
                    </div>

                    {{-- Pricing Section --}}
                    <div class="p-5 border-b border-slate-100">
                        <p class="text-xs text-slate-500 font-semibold mb-1">Starting from</p>

                        <div class="flex items-baseline gap-2.5">
                            <span id="package-display-price" class="text-3xl font-black text-slate-900 tracking-tight transition-all duration-300">
                                ₹{{ number_format($price, 0) }}
                            </span>
                            <span class="text-xs text-slate-500 font-medium">/ adult</span>
                        </div>

                        <div class="flex items-center gap-2 mt-1">
                            <p id="package-original-price" class="text-xs text-slate-400 line-through" @if($discount <= 0) style="display:none;" @endif>
                                ₹{{ number_format($originalPrice, 0) }}
                            </p>
                            <p id="package-savings-wrapper" class="text-xs font-bold text-emerald-700" @if($discount <= 0) style="display:none;" @endif>
                                Save <span id="package-savings-amount">₹{{ number_format(max(0, $originalPrice - $price), 0) }}</span>
                            </p>
                        </div>

                        <p class="text-[11px] text-slate-400 mt-1">
                            Excluding applicable taxes · Total for {{ $package->nights }}N / {{ $package->days }}D
                        </p>

                        {{-- Sidebar Customization Breakdown Indicator --}}
                        <div id="sidebar-customization-notice" class="hidden mt-3.5 pt-3 border-t border-slate-100 bg-indigo-50/70 p-2.5 rounded-lg border border-indigo-200">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-extrabold text-indigo-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-sliders text-indigo-600"></i>
                                    Customized Activities
                                </span>
                                <span id="sidebar-customization-diff" class="font-black text-indigo-900">₹0</span>
                            </div>
                            <div id="sidebar-customization-detail" class="text-[11px] text-indigo-700 mt-1"></div>
                        </div>
                    </div>

                    {{-- Travel Configuration Box --}}
                    <div class="p-4 bg-slate-50/70 border-b border-slate-100 text-xs space-y-2">
                        <div class="flex justify-between items-center text-slate-700">
                            <span class="text-slate-500"><i class="fa-regular fa-calendar blue mr-1.5"></i>Duration</span>
                            <span class="font-bold text-slate-900">{{ $package->nights }} Nights / {{ $package->days }} Days</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-700">
                            <span class="text-slate-500"><i class="fa-solid fa-location-dot blue mr-1.5"></i>From</span>
                            <span class="font-bold text-slate-900">{{ $package->departure_city ?? 'Jaipur' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-700">
                            <span class="text-slate-500"><i class="fa-solid fa-user-group blue mr-1.5"></i>Travelers</span>
                            <span class="font-bold text-slate-900">2 Adults, 1 Room</span>
                        </div>
                    </div>

                    {{-- Primary Booking Buttons --}}
                    <div class="p-5 space-y-3">
                        <a href="#booking-section"
                           class="mmt-btn-primary w-full text-center cursor-pointer"
                           onclick="document.getElementById('booking-section')?.scrollIntoView({behavior:'smooth'}); return false;">
                            PROCEED TO BOOK
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                        <button type="button"
                           class="w-full py-2.5 text-xs font-bold text-blue-600 bg-blue-50/70 hover:bg-blue-100 rounded-lg border border-blue-200 text-center transition cursor-pointer"
                           onclick="document.getElementById('booking-section')?.scrollIntoView({behavior:'smooth'});">
                            <i class="fa-solid fa-headset mr-1.5"></i>
                            TALK TO HOLIDAY EXPERT
                        </button>

                        {{-- MakeMyTrip Trust Badges --}}
                        <div class="pt-3 border-t border-slate-100 space-y-2 text-[11px] text-slate-500">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                                <span>100% Verified Partners &amp; Safe Cabs</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-phone-volume text-blue-600"></i>
                                <span>24x7 Dedicated On-Trip Holiday Support</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-rotate-left text-amber-600"></i>
                                <span>Flexible Rescheduling &amp; Refund Options</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

        </div>

        {{-- Booking Final Section --}}
        <div id="booking-section" class="panel p-5 md:p-6 mt-8">
            <div id="booking-customization-notice" class="hidden mb-5 rounded-xl border border-indigo-200 bg-indigo-50/80 p-4">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-600 text-lg mt-0.5"></i>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-indigo-950">Your Customized Itinerary Summary</h4>
                        <p class="text-xs text-indigo-800 mt-0.5">Your customized activities and recalculated total price will be saved with your reservation.</p>
                        <ul id="booking-customized-places-list" class="mt-2.5 space-y-1.5 text-xs text-indigo-900 font-medium"></ul>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Ready to Book Your Holiday?</h2>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">Select your travel dates, specify number of travelers, and confirm your holiday package instantly.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('packages.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Other Packages
                    </a>
                    <a href="tel:+919876543210" class="mmt-btn-blue text-xs">
                        <i class="fa-solid fa-phone mr-1"></i> Call Concierge
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- MakeMyTrip Mobile Bottom Bar --}}
    <div class="mmt-mobile-bar">
        <div>
            <div class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Package Price</div>
            <div class="flex items-baseline gap-1">
                <span id="mobile-display-price" class="text-lg font-black text-slate-900">
                    ₹{{ number_format($price, 0) }}
                </span>
                <span class="text-[10px] text-slate-500 font-medium">/ adult</span>
            </div>
        </div>

        <button type="button" class="mmt-btn-primary text-xs py-2.5 px-5"
            onclick="document.getElementById('booking-section')?.scrollIntoView({behavior:'smooth'});">
            BOOK NOW <i class="fa-solid fa-arrow-right text-[11px]"></i>
        </button>
    </div>

    {{-- MakeMyTrip Change Place Modal (Scrollable & City-Scoped) --}}
    <div id="changePlaceModal" class="modal-overlay"
         role="dialog" aria-modal="true"
         aria-labelledby="changePlaceModalTitle"
         onclick="if(event.target === this) closeChangePlaceModal()">
        <div class="mmt-change-modal-content">
            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-4 p-5 border-b border-slate-100 flex-shrink-0">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 mb-1">
                        <i class="fa-solid fa-location-dot text-[11px]"></i> Attractions in <span id="modalCityBadge"></span>
                    </span>
                    <h3 id="changePlaceModalTitle" class="text-lg font-black text-slate-900">
                        Change Activity: <span id="modalCurrentPlaceName" class="text-blue-600"></span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Select an alternative attraction in <strong id="modalCityNameSubtitle" class="text-slate-700"></strong>. The package price will update automatically.
                    </p>
                </div>
                <button type="button" onclick="closeChangePlaceModal()"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition cursor-pointer flex-shrink-0"
                    aria-label="Close modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Search box -->
            <div class="p-3.5 bg-slate-50 border-b border-slate-100 flex-shrink-0">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="modalPlaceSearch"
                        placeholder="Search attractions..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        oninput="filterPlacesList()">
                </div>
            </div>

            <!-- Places List (Scrollable) -->
            <div id="modalPlacesList" class="p-4 space-y-3">
                <!-- Populated dynamically with attractions from this city only -->
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between p-3.5 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex-shrink-0">
                <span id="modalPlacesCount"></span>
                <button type="button" onclick="closeChangePlaceModal()"
                    class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold transition cursor-pointer">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- Gallery popup --}}
    <div id="galleryModal" class="modal-overlay"
         role="dialog" aria-modal="true"
         aria-label="Package photo gallery"
         onclick="if(event.target === this) closeGallery()">

        <div class="bg-white rounded-2xl p-4 max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center gap-3 mb-4">
                <h2 class="font-black text-base text-slate-900">
                    {{ $package->name }} — Photos
                </h2>

                <button type="button" onclick="closeGallery()"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition"
                    aria-label="Close gallery">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @if($galleryImages->isNotEmpty())
                <div class="relative">
                    <img id="galleryModalImage"
                         src="{{ asset('storage/'.$galleryImages[0]) }}"
                         alt="{{ $package->name }}"
                         class="w-full h-[450px] object-contain rounded-xl bg-slate-100">

                    @if($galleryImages->count() > 1)
                        <button type="button" onclick="changeGallery(-1)"
                            class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full w-10 h-10 shadow flex items-center justify-center text-slate-800 transition"
                            aria-label="Previous photo">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <button type="button" onclick="changeGallery(1)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full w-10 h-10 shadow flex items-center justify-center text-slate-800 transition"
                            aria-label="Next photo">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    @endif
                </div>

                <p id="galleryCounter" class="text-xs text-slate-500 text-center mt-3 font-semibold">
                    1 / {{ $galleryImages->count() }}
                </p>
            @else
                <div class="py-16 text-center text-slate-500">
                    No package photos available.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Sub-navigation scroll & active tab handling
    document.querySelectorAll('.mmt-nav-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.mmt-nav-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const targetId = this.dataset.target;
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Day navigation switcher
    function switchDay(dayNumber) {
        document.querySelectorAll('.day-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        const activePanel = document.getElementById('day-panel-' + dayNumber);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        document.querySelectorAll('.mmt-day-pill').forEach(btn => {
            btn.classList.remove('active');
        });

        const activeBtn = document.querySelector('.mmt-day-pill[data-day="' + dayNumber + '"]');
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }

    // Gallery
    const galleryImages = @json(
        $galleryImages->map(fn ($image) => asset('storage/'.$image))->values()
    );

    let galleryIndex = 0;

    function openGalleryImage(url) {
        const modal = document.getElementById('galleryModal');
        const image = document.getElementById('galleryModalImage');
        const counter = document.getElementById('galleryCounter');

        if (image) image.src = url;
        if (counter) counter.textContent = '';

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function openGallery(index) {
        if (!galleryImages.length) return;

        galleryIndex = Math.max(0, Math.min(index, galleryImages.length - 1));
        updateGallery();

        document.getElementById('galleryModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeGallery() {
        document.getElementById('galleryModal').classList.remove('open');
        document.body.style.overflow = '';
    }

    function changeGallery(direction) {
        if (!galleryImages.length) return;
        galleryIndex = (galleryIndex + direction + galleryImages.length) % galleryImages.length;
        updateGallery();
    }

    function updateGallery() {
        const image = document.getElementById('galleryModalImage');
        const counter = document.getElementById('galleryCounter');

        if (image) image.src = galleryImages[galleryIndex];
        if (counter) counter.textContent = (galleryIndex + 1) + ' / ' + galleryImages.length;
    }

    document.addEventListener('keydown', function (event) {
        const galleryModal = document.getElementById('galleryModal');
        const changeModal = document.getElementById('changePlaceModal');

        if (event.key === 'Escape') {
            if (galleryModal && galleryModal.classList.contains('open')) closeGallery();
            if (changeModal && changeModal.classList.contains('open')) closeChangePlaceModal();
        }

        if (galleryModal && galleryModal.classList.contains('open')) {
            if (event.key === 'ArrowRight') changeGallery(1);
            if (event.key === 'ArrowLeft') changeGallery(-1);
        }
    });

    // Place customization and swapping functionality
    const swappablePlaces = @json($swappablePlaces ?? []);
    const packageConfig = {
        base_price: {{ (float) $package->base_price }},
        nights: {{ (int) $package->nights }},
        discount_percentage: {{ (float) ($package->discount_percentage ?? 0) }},
        tax_percentage: {{ (float) ($package->tax_percentage ?? 0) }}
    };
    const planItemsData = @json($planPricingItems ?? []);

    let activePlanItemId = null;

    function openChangePlaceModal(planItemId) {
        activePlanItemId = planItemId;
        const item = planItemsData.find(i => i.id === planItemId);
        if (!item) return;

        const modal = document.getElementById('changePlaceModal');
        const modalPlaceName = document.getElementById('modalCurrentPlaceName');
        const modalCityBadge = document.getElementById('modalCityBadge');
        const modalCitySubtitle = document.getElementById('modalCityNameSubtitle');
        const searchInput = document.getElementById('modalPlaceSearch');

        const cityName = item.city_name || 'this destination';

        if (modalPlaceName) modalPlaceName.textContent = item.place_name || item.original_place_name;
        if (modalCityBadge) modalCityBadge.textContent = cityName;
        if (modalCitySubtitle) modalCitySubtitle.textContent = cityName;
        if (searchInput) {
            searchInput.value = '';
            searchInput.placeholder = `Search attractions in ${cityName}...`;
        }

        renderPlacesList();

        const listContainer = document.getElementById('modalPlacesList');
        if (listContainer) listContainer.scrollTop = 0;

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';

        setTimeout(() => searchInput?.focus(), 150);
    }

    function closeChangePlaceModal() {
        const modal = document.getElementById('changePlaceModal');
        if (modal) modal.classList.remove('open');
        document.body.style.overflow = '';
        activePlanItemId = null;
    }

    function filterPlacesList() {
        renderPlacesList();
    }

    function renderPlacesList() {
        const listContainer = document.getElementById('modalPlacesList');
        const countContainer = document.getElementById('modalPlacesCount');
        const searchInput = document.getElementById('modalPlaceSearch');
        if (!listContainer) return;

        const currentItem = planItemsData.find(i => i.id === activePlanItemId);
        if (!currentItem) return;

        const cityName = currentItem.city_name || 'this destination';
        const searchTerm = (searchInput?.value || '').toLowerCase().trim();

        // STRICT FILTER: Only show attractions from the SAME city as the current activity
        const filteredPlaces = swappablePlaces.filter(place => {
            const matchesSameCity = currentItem.city_id ? place.city_id === currentItem.city_id : true;
            const matchesSearch = !searchTerm || place.name.toLowerCase().includes(searchTerm) || (place.description && place.description.toLowerCase().includes(searchTerm));
            return matchesSameCity && matchesSearch;
        });

        if (countContainer) {
            countContainer.textContent = `Showing ${filteredPlaces.length} attraction${filteredPlaces.length === 1 ? '' : 's'} in ${cityName}`;
        }

        if (filteredPlaces.length === 0) {
            listContainer.innerHTML = `
                <div class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-map-location-dot text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-700">No other attractions found in ${cityName}</p>
                    <p class="text-xs text-slate-400 mt-1">There are no additional attractions listed for ${cityName}.</p>
                </div>
            `;
            return;
        }

        let html = '';
        filteredPlaces.forEach(place => {
            const isCurrentlySelected = currentItem.current_place_id === place.id;
            const isOriginal = currentItem.original_place_id === place.id;
            const currentItemBasePrice = Number(currentItem.original_place_price || 0);
            const diff = Number(place.price || 0) - currentItemBasePrice;

            let diffBadge = '';
            if (isCurrentlySelected) {
                diffBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Selected in Plan</span>`;
            } else if (diff < 0) {
                diffBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-arrow-down mr-0.5"></i> Saves ₹${Math.abs(Math.round(diff)).toLocaleString('en-IN')}</span>`;
            } else if (diff > 0) {
                diffBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-black bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-arrow-up mr-0.5"></i> +₹${Math.round(diff).toLocaleString('en-IN')}</span>`;
            } else {
                diffBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600">Same price</span>`;
            }

            const imgHtml = place.primary_image
                ? `<img src="${place.primary_image}" alt="${place.name}" class="mmt-place-select-thumb" onerror="this.onerror=null; this.src=''; this.className='mmt-place-select-thumb flex items-center justify-center text-slate-400';">`
                : `<div class="mmt-place-select-thumb flex items-center justify-center text-slate-400"><i class="fa-solid fa-landmark text-2xl"></i></div>`;

            html += `
                <div class="mmt-place-select-card ${isCurrentlySelected ? 'is-current' : ''}">
                    ${imgHtml}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h4 class="font-black text-sm text-slate-900 leading-snug">
                                    ${place.name}
                                    ${isOriginal ? '<span class="ml-1 text-[10px] font-bold text-slate-400">(Original Itinerary)</span>' : ''}
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-blue-500 mr-1 text-[11px]"></i>
                                    ${place.city_name}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="font-black text-sm text-slate-900">
                                    ₹${Number(place.price || 0).toLocaleString('en-IN')}
                                </span>
                                <div class="mt-0.5">${diffBadge}</div>
                            </div>
                        </div>

                        ${place.description ? `<p class="text-xs text-slate-600 line-clamp-2 mt-2 leading-relaxed">${place.description}</p>` : ''}

                        <div class="mt-3 flex items-center justify-between gap-3 pt-2 border-t border-slate-100">
                            <span class="text-[11px] text-slate-400 font-medium">
                                ${place.secondary_images && place.secondary_images.length > 0 ? `<i class="fa-regular fa-images mr-1"></i>${place.secondary_images.length + 1} photos` : ''}
                            </span>

                            ${isCurrentlySelected
                                ? `<button type="button" disabled class="px-3.5 py-1.5 rounded-lg bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed">
                                    <i class="fa-solid fa-check text-[10px] mr-1"></i> Selected
                                   </button>`
                                : `<button type="button" onclick="selectReplacementPlace(${currentItem.id}, ${place.id})"
                                    class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-check text-[10px]"></i> Choose this Activity
                                   </button>`
                            }
                        </div>
                    </div>
                </div>
            `;
        });

        listContainer.innerHTML = html;
    }

    function selectReplacementPlace(planItemId, newPlaceId) {
        const item = planItemsData.find(i => i.id === planItemId);
        const newPlace = swappablePlaces.find(p => p.id === newPlaceId);
        if (!item || !newPlace) return;

        // Update item data
        item.current_place_id = newPlace.id;
        item.place_price = Number(newPlace.price || 0);
        item.place_name = newPlace.name;
        item.city_id = newPlace.city_id;
        item.city_name = newPlace.city_name;

        const isModified = item.current_place_id !== item.original_place_id;

        // Update DOM for this plan item
        const card = document.getElementById(`plan-item-${planItemId}`);
        if (card) {
            // Update Title
            const titleElem = card.querySelector('.plan-item-title');
            if (titleElem) titleElem.textContent = newPlace.name;

            // Update City
            const cityElem = card.querySelector('.plan-item-city-name');
            if (cityElem) cityElem.textContent = newPlace.city_name;

            // Update Description
            const descElem = card.querySelector('.plan-item-desc');
            if (descElem) descElem.textContent = newPlace.description || 'Explore this destination and experience the rich local culture.';

            // Update Photos
            const photoStrip = card.querySelector('.plan-item-photo-strip');
            if (photoStrip) {
                const photos = [];
                if (newPlace.primary_image) photos.push(newPlace.primary_image);
                if (newPlace.secondary_images && newPlace.secondary_images.length) {
                    photos.push(...newPlace.secondary_images);
                }

                if (photos.length > 0) {
                    photoStrip.style.display = 'flex';
                    photoStrip.innerHTML = photos.map(url => `
                        <img src="${url}" alt="${newPlace.name}" class="mmt-photo-thumb" loading="lazy" onclick="openGalleryImage('${url}')" title="${newPlace.name}">
                    `).join('');
                } else {
                    photoStrip.style.display = 'none';
                    photoStrip.innerHTML = '';
                }
            }

            // Update Change bar & tags
            const customTag = card.querySelector('.plan-item-custom-tag');
            const changeStatus = card.querySelector('.plan-custom-status');
            const changeBtnLabel = card.querySelector('.change-btn-label');
            const newPlaceLabel = card.querySelector('.custom-new-place-name');

            if (isModified) {
                if (customTag) customTag.classList.remove('hidden');
                if (changeStatus) {
                    changeStatus.classList.remove('hidden');
                    changeStatus.classList.add('flex');
                }
                if (newPlaceLabel) newPlaceLabel.textContent = newPlace.name;
                if (changeBtnLabel) changeBtnLabel.textContent = 'Change Again';
            } else {
                if (customTag) customTag.classList.add('hidden');
                if (changeStatus) {
                    changeStatus.classList.add('hidden');
                    changeStatus.classList.remove('flex');
                }
                if (changeBtnLabel) changeBtnLabel.textContent = 'Change Activity';
            }

            // Flash card to indicate update
            card.classList.remove('mmt-card-flash');
            void card.offsetWidth;
            card.classList.add('mmt-card-flash');
        }

        // Recalculate package pricing
        recalculatePackagePricing();

        // Close modal
        closeChangePlaceModal();

        // Scroll smoothly to the updated card
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function resetPlanItem(planItemId) {
        const item = planItemsData.find(i => i.id === planItemId);
        if (!item) return;

        // Restore original data
        item.current_place_id = item.original_place_id;
        item.place_price = Number(item.original_place_price || 0);
        item.place_name = item.original_place_name;
        item.city_id = item.city_id;
        item.city_name = item.city_name;

        const card = document.getElementById(`plan-item-${planItemId}`);
        if (card) {
            const titleElem = card.querySelector('.plan-item-title');
            if (titleElem) titleElem.textContent = item.original_place_name;

            const cityElem = card.querySelector('.plan-item-city-name');
            if (cityElem) cityElem.textContent = item.city_name;

            const descElem = card.querySelector('.plan-item-desc');
            if (descElem) descElem.textContent = item.original_description || 'Explore this destination and experience the rich local culture.';

            const photoStrip = card.querySelector('.plan-item-photo-strip');
            if (photoStrip) {
                const photos = [];
                if (item.original_primary_image) photos.push(item.original_primary_image);
                if (item.original_secondary_images && item.original_secondary_images.length) {
                    photos.push(...item.original_secondary_images);
                }

                if (photos.length > 0) {
                    photoStrip.style.display = 'flex';
                    photoStrip.innerHTML = photos.map(url => `
                        <img src="${url}" alt="${item.original_place_name}" class="mmt-photo-thumb" loading="lazy" onclick="openGalleryImage('${url}')" title="${item.original_place_name}">
                    `).join('');
                } else {
                    photoStrip.style.display = 'none';
                    photoStrip.innerHTML = '';
                }
            }

            const customTag = card.querySelector('.plan-item-custom-tag');
            const changeStatus = card.querySelector('.plan-custom-status');
            const changeBtnLabel = card.querySelector('.change-btn-label');

            if (customTag) customTag.classList.add('hidden');
            if (changeStatus) {
                changeStatus.classList.add('hidden');
                changeStatus.classList.remove('flex');
            }
            if (changeBtnLabel) changeBtnLabel.textContent = 'Change Activity';

            card.classList.remove('mmt-card-flash');
            void card.offsetWidth;
            card.classList.add('mmt-card-flash');
        }

        recalculatePackagePricing();
    }

    function recalculatePackagePricing() {
        let subtotal = Number(packageConfig.base_price || 0);
        const customizedItems = [];

        planItemsData.forEach(item => {
            if (item.kind === 'hotel') {
                subtotal += Number(item.hotel_adult_price || 0) * Number(packageConfig.nights || 0);
            } else {
                subtotal += Number(item.place_price || 0) + Number(item.additional_price || 0);
            }
            if (item.can_change && item.current_place_id !== item.original_place_id) {
                const diff = Number(item.place_price || 0) - Number(item.original_place_price || 0);
                customizedItems.push({
                    item: item,
                    diff: diff
                });
            }
        });

        const discountPercentage = Number(packageConfig.discount_percentage || 0);
        const discountedTotal = subtotal * (1 - discountPercentage / 100);
        const originalPrice = discountPercentage > 0 ? (discountedTotal / (1 - discountPercentage / 100)) : subtotal;
        const savings = Math.max(0, originalPrice - discountedTotal);

        // Update UI Prices
        const displayPriceElem = document.getElementById('package-display-price');
        if (displayPriceElem) {
            displayPriceElem.textContent = '₹' + Math.round(discountedTotal).toLocaleString('en-IN');
            displayPriceElem.classList.add('mmt-price-highlight');
            setTimeout(() => displayPriceElem.classList.remove('mmt-price-highlight'), 600);
        }

        const originalPriceElem = document.getElementById('package-original-price');
        if (originalPriceElem) {
            originalPriceElem.textContent = '₹' + Math.round(originalPrice).toLocaleString('en-IN');
        }

        const savingsElem = document.getElementById('package-savings-amount');
        if (savingsElem) {
            savingsElem.textContent = '₹' + Math.round(savings).toLocaleString('en-IN');
        }

        const mobilePriceElem = document.getElementById('mobile-display-price');
        if (mobilePriceElem) {
            mobilePriceElem.textContent = '₹' + Math.round(discountedTotal).toLocaleString('en-IN');
        }

        // Update Customization summaries
        updateCustomizationSummary(customizedItems);
    }

    function updateCustomizationSummary(customizedItems) {
        const sidebarNotice = document.getElementById('sidebar-customization-notice');
        const sidebarDiff = document.getElementById('sidebar-customization-diff');
        const sidebarDetail = document.getElementById('sidebar-customization-detail');

        const bookingNotice = document.getElementById('booking-customization-notice');
        const bookingList = document.getElementById('booking-customized-places-list');

        if (customizedItems.length > 0) {
            const totalDiff = customizedItems.reduce((acc, c) => acc + c.diff, 0);
            const diffFormatted = (totalDiff >= 0 ? '+' : '−') + '₹' + Math.abs(Math.round(totalDiff)).toLocaleString('en-IN');

            if (sidebarNotice) {
                sidebarNotice.classList.remove('hidden');
                if (sidebarDiff) sidebarDiff.textContent = diffFormatted;
                if (sidebarDetail) {
                    sidebarDetail.textContent = `${customizedItems.length} activity modified in plan`;
                }
            }

            if (bookingNotice && bookingList) {
                bookingNotice.classList.remove('hidden');
                bookingList.innerHTML = customizedItems.map(c => {
                    const itemDiffFormatted = (c.diff >= 0 ? '+' : '−') + '₹' + Math.abs(Math.round(c.diff)).toLocaleString('en-IN');
                    return `
                        <li class="flex items-center justify-between gap-2 border-b border-indigo-100/60 pb-1.5 last:border-0 last:pb-0">
                            <span>Day ${c.item.day}: <strong>${c.item.original_place_name}</strong> &rarr; <span class="text-indigo-700 font-bold">${c.item.place_name}</span></span>
                            <span class="font-extrabold text-[11px] ${c.diff < 0 ? 'text-emerald-700' : (c.diff > 0 ? 'text-amber-700' : 'text-slate-600')}">${itemDiffFormatted}</span>
                        </li>
                    `;
                }).join('');
            }
        } else {
            if (sidebarNotice) sidebarNotice.classList.add('hidden');
            if (bookingNotice) bookingNotice.classList.add('hidden');
        }
    }

    // Share package
    async function sharePackage() {
        const shareData = {
            title: @json($package->name),
            text: 'Check out this holiday package on MakeMyTrip style portal!',
            url: window.location.href
        };

        try {
            if (navigator.share) {
                await navigator.share(shareData);
            } else if (navigator.clipboard) {
                await navigator.clipboard.writeText(shareData.url);
                alert('Package link copied to clipboard!');
            } else {
                prompt('Copy this package link:', shareData.url);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Unable to share package:', error);
            }
        }
    }

    // Wishlist toggle
    function toggleWishlist(button) {
        const icon = button.querySelector('i');
        const saved = icon.classList.contains('fa-solid');

        icon.classList.toggle('fa-solid', !saved);
        icon.classList.toggle('fa-regular', saved);
        button.classList.toggle('text-red-500', !saved);
        button.classList.toggle('border-red-300', !saved);
    }
</script>
@endpush
