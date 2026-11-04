@extends('layouts.cab-associate')
@section('title', 'Cab Availability')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Cab schedule</p><h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $cab->name }} availability</h1><p class="mt-2 text-slate-500">{{ $cab->registration_number }} · Click a date to toggle its availability.</p></div>
    <a href="{{ route('cab-associate.cabs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white">Back to My Cabs</a>
</div>
@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
@endif
<section class="rounded-2xl bg-white p-5 shadow-sm sm:p-6">
    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('cab-associate.cabs.availability', ['cab' => $cab, 'month' => $month->subMonth()->format('Y-m')]) }}" aria-label="Previous month" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-left"></i></a>
        <h2 class="text-lg font-bold text-slate-900">{{ $month->format('F Y') }}</h2>
        <a href="{{ route('cab-associate.cabs.availability', ['cab' => $cab, 'month' => $month->addMonth()->format('Y-m')]) }}" aria-label="Next month" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="mb-2 grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)<div class="py-2">{{ $weekday }}</div>@endforeach
    </div>
    <div class="grid grid-cols-7 gap-2">
        @foreach ($days as $date)
            @php($isUnavailable = in_array($date->toDateString(), $unavailableDates, true))
            @php($isPast = $date->toDateString() < now()->toDateString())
            <div class="min-h-20 rounded-lg border p-1.5 {{ $date->month === $month->month ? 'border-slate-200' : 'border-slate-100 bg-slate-50 text-slate-400' }}">
                <span class="px-1 text-xs font-semibold">{{ $date->day }}</span>
                @if (!$isPast)
                    <form method="POST" action="{{ route('cab-associate.cabs.availability.update', $cab) }}" class="mt-1">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                        <input type="hidden" name="is_available" value="{{ $isUnavailable ? '1' : '0' }}">
                        <button type="submit" class="w-full rounded-md px-1 py-1.5 text-[10px] font-semibold leading-tight sm:text-xs {{ $isUnavailable ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                            {{ $isUnavailable ? 'Unavailable' : 'Available' }}
                        </button>
                    </form>
                @else
                    <p class="mt-2 px-1 text-[10px] text-slate-400">Past</p>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-5 flex flex-wrap gap-4 text-xs text-slate-600"><span><span class="mr-1 inline-block h-3 w-3 rounded bg-emerald-200"></span>Available</span><span><span class="mr-1 inline-block h-3 w-3 rounded bg-red-200"></span>Unavailable</span></div>
</section>
@endsection
