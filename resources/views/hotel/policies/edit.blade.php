@extends('layouts.hotel')
@section('title', 'Hotel & Room Policies')
@section('content')
<div class="mb-6">
    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Hotel information</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Hotel &amp; Room Policies</h1>
    <p class="mt-2 text-slate-500">Share the rules guests should know before booking or staying at {{ $hotel->name }}.</p>
</div>
<form method="POST" action="{{ route('hotel.policies.update') }}" class="grid gap-6">
    @csrf
    @method('PUT')
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    <section class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-5 flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid fa-hotel"></i></span>
            <div>
                <h2 class="font-bold text-slate-900">Hotel Policy</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">For example: check-in/check-out times, cancellation, identification, smoking, or general property rules.</p>
            </div>
        </div>
        <label for="hotel_policy" class="mb-2 block text-sm font-semibold text-slate-700">Hotel-wide rules</label>
        <textarea id="hotel_policy" name="hotel_policy" rows="8" maxlength="10000" placeholder="Enter your hotel-wide policies. You can put each rule on a separate line." class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('hotel_policy', $hotel->hotel_policy) }}</textarea>
        @error('hotel_policy')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>
    <section class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-5 flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-bed"></i></span>
            <div>
                <h2 class="font-bold text-slate-900">Room Policy</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">For example: occupancy, children, extra beds, room-specific restrictions, or housekeeping rules.</p>
            </div>
        </div>
        <label for="room_policy" class="mb-2 block text-sm font-semibold text-slate-700">Room rules</label>
        <textarea id="room_policy" name="room_policy" rows="8" maxlength="10000" placeholder="Enter your room policies. You can put each rule on a separate line." class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('room_policy', $hotel->room_policy) }}</textarea>
        @error('room_policy')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>
    <div class="flex justify-end">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-floppy-disk mr-2"></i>Save Policies</button>
    </div>
</form>
@endsection
