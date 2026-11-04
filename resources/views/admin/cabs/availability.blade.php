@extends('layouts.admin')
@section('title', 'Cab Availability')
@section('header', 'Cab Availability')
@section('subheader', 'Read-only availability schedule for this cab.')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Cab schedule</p>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $cab->name }} availability</h2>
        <p class="mt-2 text-slate-500">{{ $cab->registration_number }} · {{ $cab->associate?->name ?? 'Admin created' }}</p>
    </div>
    <a href="{{ route('admin.cabs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white">Back to Cabs</a>
</div>
<section class="rounded-2xl bg-white p-5 shadow-sm sm:p-6">
    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('admin.cabs.availability', ['cab' => $cab, 'month' => $month->subMonth()->format('Y-m')]) }}" aria-label="Previous month" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-left"></i></a>
        <h3 class="text-lg font-bold text-slate-900">{{ $month->format('F Y') }}</h3>
        <a href="{{ route('admin.cabs.availability', ['cab' => $cab, 'month' => $month->addMonth()->format('Y-m')]) }}" aria-label="Next month" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="mb-2 grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
            <div class="py-2">{{ $weekday }}</div>
        @endforeach
    </div>
    <div class="grid grid-cols-7 gap-2">
        @foreach ($days as $date)
            @php($isUnavailable = in_array($date->toDateString(), $unavailableDates, true))
            <div class="min-h-16 rounded-lg border p-2 {{ $date->month === $month->month ? 'border-slate-200' : 'border-slate-100 bg-slate-50 text-slate-400' }}">
                <span class="text-xs font-semibold">{{ $date->day }}</span>
                <p class="mt-1 rounded-md px-1 py-1 text-center text-[10px] font-semibold sm:text-xs {{ $isUnavailable ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $isUnavailable ? 'Unavailable' : 'Available' }}
                </p>
            </div>
        @endforeach
    </div>
    <div class="mt-5 flex flex-wrap gap-4 text-xs text-slate-600">
        <span><span class="mr-1 inline-block h-3 w-3 rounded bg-emerald-200"></span>Available</span>
        <span><span class="mr-1 inline-block h-3 w-3 rounded bg-red-200"></span>Unavailable</span>
    </div>
</section>
@endsection
