@extends('layouts.hotel')
@section('title', 'Hotel Dashboard')
@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Hotel overview</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $hotel->name }}</h1>
        <p class="mt-2 text-slate-500">Welcome to your hotel partner dashboard.</p>
    </div>
    <span class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800">
        <i class="fa-solid fa-star mr-1"></i>{{ $hotel->star_rating }} star{{ $hotel->star_rating === 1 ? '' : 's' }}
    </span>
</div>
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <section class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-2">
        <div class="mb-5 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid fa-address-card"></i></span>
            <div>
                <h2 class="font-bold text-slate-900">Hotel profile</h2>
                <p class="text-sm text-slate-500">Your registered hotel information</p>
            </div>
        </div>
        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hotel ID</dt>
                <dd class="mt-1 font-mono font-semibold text-slate-800">{{ $hotel->hotel_code }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Username</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $hotel->username }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">City</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $hotel->city->name }}, {{ $hotel->city->state->name }}, {{ $hotel->city->state->country->name }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Full address</dt>
                <dd class="mt-1 leading-6 text-slate-700">{{ $hotel->address }}</dd>
            </div>
        </dl>
    </section>
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-indian-rupee-sign"></i></span>
            <div>
                <h2 class="font-bold text-slate-900">Current rates</h2>
                <p class="text-sm text-slate-500">Rates saved by your administrator</p>
            </div>
        </div>
        <dl class="space-y-4">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <dt class="text-sm text-slate-500">Adult</dt>
                <dd class="font-semibold text-slate-900">{{ number_format((float) $hotel->adult_cost, 2) }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <dt class="text-sm text-slate-500">Child</dt>
                <dd class="font-semibold text-slate-900">{{ number_format((float) $hotel->child_cost, 2) }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-sm text-slate-500">Extra bed</dt>
                <dd class="font-semibold text-slate-900">{{ number_format((float) $hotel->extra_bed_cost, 2) }}</dd>
            </div>
        </dl>
    </section>
</div>
<div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900">
    <i class="fa-solid fa-circle-info mr-2"></i>
    Need to update your hotel details or rates? Please contact your Tour &amp; Travel administrator.
</div>
<div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-2">
    <a href="{{ route('hotel.images.index') }}" class="group rounded-2xl bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fa-solid fa-images"></i></span>
            <div>
                <h2 class="font-bold text-slate-900 group-hover:text-indigo-700">Hotel Photos</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Upload and manage photos of your hotel property.</p>
            </div>
        </div>
    </a>
    <a href="{{ route('hotel.room-types.index') }}" class="group rounded-2xl bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fa-solid fa-layer-group"></i></span>
            <div>
                <h2 class="font-bold text-slate-900 group-hover:text-indigo-700">Room Types</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Create and manage categories such as Standard, Deluxe, and Luxury.</p>
            </div>
        </div>
    </a>
    <a href="{{ route('hotel.rooms.index') }}" class="group rounded-2xl bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-bed"></i></span>
            <div>
                <h2 class="font-bold text-slate-900 group-hover:text-indigo-700">Rooms</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Add room photos, descriptions, highlights, key points, and amenities.</p>
            </div>
        </div>
    </a>
    <a href="{{ route('hotel.policies.edit') }}" class="group rounded-2xl bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md md:col-span-2">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-file-contract"></i></span>
            <div>
                <h2 class="font-bold text-slate-900 group-hover:text-indigo-700">Hotel &amp; Room Policies</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Add or update your hotel-wide rules and room-specific policies.</p>
            </div>
        </div>
    </a>
</div>
@endsection
