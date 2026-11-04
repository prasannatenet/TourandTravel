@extends('layouts.cab-associate')
@section('title', 'Dashboard')
@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Associate overview</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $associate->name }}</h1>
        <p class="mt-2 text-slate-500">View your account details and manage your cabs and availability.</p>
    </div>
    <a href="{{ route('cab-associate.cabs.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-plus mr-2"></i>Add Cab</a>
</div>
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <section class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-2">
        <div class="mb-5 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid fa-address-card"></i></span>
            <div>
                <h2 class="font-bold text-slate-900">Your associate details</h2>
                <p class="text-sm text-slate-500">Account information registered by the administrator</p>
            </div>
        </div>
        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Username</dt><dd class="mt-1 font-semibold text-slate-800">{{ $associate->username }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phone</dt><dd class="mt-1 font-semibold text-slate-800">{{ $associate->phone }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</dt><dd class="mt-1 font-semibold text-slate-800">{{ $associate->email ?: 'Not provided' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">City</dt><dd class="mt-1 font-semibold text-slate-800">{{ $associate->city?->name ?? 'Not set' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Commission rate</dt><dd class="mt-1 font-semibold text-slate-800">{{ number_format((float) $associate->commission_rate, 2) }}%</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Account status</dt><dd class="mt-1 font-semibold capitalize text-slate-800">{{ $associate->status }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Address</dt><dd class="mt-1 leading-6 text-slate-700">{{ $associate->address ?: 'Not provided' }}</dd></div>
        </dl>
    </section>
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-car"></i></span>
            <div><h2 class="font-bold text-slate-900">Your fleet</h2><p class="text-sm text-slate-500">Cabs under your account</p></div>
        </div>
        <p class="text-4xl font-bold text-slate-900">{{ $associate->cabs_count }}</p>
        <a href="{{ route('cab-associate.cabs.index') }}" class="mt-5 inline-flex font-semibold text-indigo-600 hover:text-indigo-800">Manage your cabs <i class="fa-solid fa-arrow-right ml-2 mt-1 text-xs"></i></a>
    </section>
</div>
<section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="font-bold text-slate-900">Your cabs</h2><p class="text-sm text-slate-500">Update cab details and mark dates unavailable.</p></div>
        <a href="{{ route('cab-associate.cabs.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View all</a>
    </div>
    @forelse ($cabs as $cab)
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 py-4">
            <div><p class="font-semibold text-slate-900">{{ $cab->name }} <span class="font-normal text-slate-500">({{ $cab->registration_number }})</span></p><p class="mt-1 text-sm text-slate-500">{{ $cab->type }} · {{ $cab->city?->name ?? 'City not set' }} · {{ $cab->capacity }} seats</p></div>
            <a href="{{ route('cab-associate.cabs.availability', $cab) }}" class="rounded-lg border border-indigo-200 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Availability</a>
        </div>
    @empty
        <p class="border-t border-slate-100 py-6 text-sm text-slate-500">You have not added any cabs yet.</p>
    @endforelse
</section>
@endsection
