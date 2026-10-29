@extends('layouts.app')

@section('title', 'Booking Confirmed')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 sm:py-16">
    <div class="mx-auto max-w-2xl px-4 sm:px-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white text-center shadow-sm">
            <div class="bg-gradient-to-br from-emerald-600 to-teal-600 px-6 py-10 text-white sm:px-10">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/15 ring-8 ring-white/10"><i class="fa-solid fa-check text-3xl"></i></div>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.2em] text-emerald-100">Booking received</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight">You’re all set!</h1>
                <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-emerald-50">Thank you, {{ $booking->name }}. Our travel team will contact you soon to confirm your holiday.</p>
            </div>

            <div class="p-6 text-left sm:p-10">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Booking summary</p>
                    <h2 class="mt-2 text-lg font-black text-slate-900">{{ $booking->package->name }}</h2>
                    <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">Name</dt><dd class="mt-1 font-bold text-slate-800">{{ $booking->name }}</dd></div>
                        <div><dt class="text-slate-500">Country</dt><dd class="mt-1 font-bold text-slate-800">{{ $booking->country }}</dd></div>
                        <div><dt class="text-slate-500">Travellers</dt><dd class="mt-1 font-bold text-slate-800">{{ $booking->adult_count }} adult{{ $booking->adult_count === 1 ? '' : 's' }} · {{ $booking->child_count }} child{{ $booking->child_count === 1 ? '' : 'ren' }}</dd></div>
                        <div><dt class="text-slate-500">Email</dt><dd class="mt-1 break-words font-bold text-slate-800">{{ $booking->email }}</dd></div>
                        <div><dt class="text-slate-500">Mobile</dt><dd class="mt-1 font-bold text-slate-800">{{ $booking->mobile }}</dd></div>
                    </dl>
                </div>
                <p class="mt-5 text-center text-sm leading-6 text-slate-500">We’ll reach you at <span class="font-semibold text-slate-700">{{ $booking->email }}</span> or <span class="font-semibold text-slate-700">{{ $booking->mobile }}</span>.</p>
                <a href="{{ route('packages.index') }}" class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-extrabold text-white transition hover:bg-indigo-700">Explore more packages <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </div>
        </div>
    </div>
</div>
@endsection
