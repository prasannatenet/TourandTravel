@extends('layouts.app')

@section('title', 'Book ' . $package->name)

@section('content')
@php
    $pricingConfig = [
        'adultBase' => (float) $package->base_price,
        'childBase' => (float) ($package->child_price ?? $package->base_price),
        'hotels' => $package->planItems->where('item_type', 'hotel')->map(fn ($item): array => [
            'nights' => (int) ($item->nights ?? 1),
            'adult' => (float) ($item->hotelRoom?->customer_adult_price ?? 0),
            'child' => (float) ($item->hotelRoom?->customer_child_price ?? $item->hotelRoom?->customer_adult_price ?? 0),
        ])->values()->all(),
        'places' => (float) $package->planItems->sum(fn ($item): float => (float) ($item->place?->price_per_person ?? 0)),
        'fixed' => (float) $package->planItems->sum(fn ($item): float => (float) $item->additional_price),
        'discount' => (float) $package->discount_percentage,
        'tax' => (float) $package->tax_percentage,
    ];
@endphp
<div class="min-h-screen bg-slate-50 py-10 sm:py-14">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('packages.show', $package->slug) }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-indigo-600">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to package details
        </a>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 px-6 py-8 text-white sm:px-10">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-xl ring-1 ring-white/25"><i class="fa-solid fa-calendar-check"></i></div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-indigo-100">Start your journey</p>
                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Book your holiday</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-indigo-100 sm:text-base">Share your details and our travel team will contact you to confirm the itinerary and availability.</p>
                </div>

                <div class="px-6 py-7 sm:px-10 sm:py-9">
                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                            <p class="font-bold">Please check the highlighted details.</p>
                            <ul class="mt-1 list-inside list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('packages.book.store', $package->slug) }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Your contact details</h2>
                            <p class="mt-1 text-sm text-slate-500">We will use these details only to follow up on your booking.</p>
                        </div>

                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-4 sm:p-5">
                            <div class="mb-4">
                                <h3 class="text-sm font-extrabold text-indigo-950">Who is travelling?</h3>
                                <p class="mt-1 text-xs text-indigo-700">Children are guests aged 0–12 years.</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="adult_count" class="mb-2 block text-sm font-bold text-slate-700">Adults <span class="text-rose-500">*</span></label>
                                    <input type="number" name="adult_count" id="adult_count" value="{{ old('adult_count', 1) }}" min="1" max="99" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                                </div>
                                <div>
                                    <label for="child_count" class="mb-2 block text-sm font-bold text-slate-700">Children <span class="font-normal text-slate-400">(0–12 years)</span></label>
                                    <input type="number" name="child_count" id="child_count" value="{{ old('child_count', 0) }}" min="0" max="99" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Full name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" placeholder="Enter your full name">
                            </div>
                            <div>
                                <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email address <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" placeholder="you@example.com">
                            </div>
                            <div>
                                <label for="mobile" class="mb-2 block text-sm font-bold text-slate-700">Mobile number <span class="text-rose-500">*</span></label>
                                <input type="tel" name="mobile" id="mobile" value="{{ old('mobile') }}" required autocomplete="tel" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" placeholder="+91 98765 43210">
                            </div>
                            <div>
                                <label for="country" class="mb-2 block text-sm font-bold text-slate-700">Country <span class="text-rose-500">*</span></label>
                                <select name="country" id="country" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                                    @foreach (['India', 'USA', 'UK', 'Canada', 'Australia', 'UAE', 'Singapore', 'Others'] as $country)
                                        <option value="{{ $country }}" @selected(old('country', 'India') === $country)>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="photo_id_proof" class="mb-2 block text-sm font-bold text-slate-700">Photo ID proof <span class="font-normal text-slate-400">(optional)</span></label>
                            <input type="text" name="photo_id_proof" id="photo_id_proof" value="{{ old('photo_id_proof') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" placeholder="Passport, driving licence, etc.">
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs leading-5 text-slate-500"><i class="fa-solid fa-lock mr-1 text-emerald-600"></i> Your information is kept private.</p>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">Confirm booking <i class="fa-solid fa-arrow-right text-xs"></i></button>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:sticky lg:top-24">
                @if ($package->image)
                    <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="h-52 w-full object-cover">
                @else
                    <div class="flex h-52 items-center justify-center bg-gradient-to-br from-indigo-100 to-violet-100 text-indigo-400"><i class="fa-solid fa-mountain-sun text-5xl"></i></div>
                @endif
                <div class="p-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Selected package</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900">{{ $package->name }}</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3 border-y border-slate-100 py-4 text-sm">
                        <div><p class="text-xs text-slate-500">Duration</p><p class="mt-1 font-bold text-slate-800">{{ $package->days }}D / {{ $package->nights }}N</p></div>
                        <div><p class="text-xs text-slate-500">Base adult price</p><p class="mt-1 font-bold text-slate-800">₹{{ number_format((float) $package->base_price, 0) }}</p></div>
                    </div>
                    <div class="mt-5 flex items-end justify-between rounded-xl bg-indigo-50 px-4 py-3">
                        <span class="text-sm font-semibold text-indigo-900">Estimated total</span>
                        <span id="estimated-total" class="text-xl font-black text-indigo-700">₹{{ number_format((float) $estimatedTotal, 0) }}</span>
                    </div>
                    <p id="guest-summary" class="mt-2 text-right text-xs text-slate-500">1 adult · 0 children</p>
                    <p class="mt-4 text-sm leading-6 text-slate-500">Ready to explore? Submit your details and we will help you plan the next steps.</p>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const bookingPricing = @json($pricingConfig);
    const adultCountInput = document.getElementById('adult_count');
    const childCountInput = document.getElementById('child_count');
    const estimatedTotal = document.getElementById('estimated-total');
    const guestSummary = document.getElementById('guest-summary');
    const currency = new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 });

    function updateBookingTotal() {
        const adults = Math.max(1, Number(adultCountInput.value) || 1);
        const children = Math.max(0, Number(childCountInput.value) || 0);
        let subtotal = (bookingPricing.adultBase * adults) + (bookingPricing.childBase * children) + bookingPricing.fixed;

        bookingPricing.hotels.forEach((hotel) => {
            subtotal += ((hotel.adult * adults) + (hotel.child * children)) * hotel.nights;
        });

        subtotal += bookingPricing.places * (adults + children);
        const discountedTotal = subtotal * (1 - (bookingPricing.discount / 100));
        const total = discountedTotal * (1 + (bookingPricing.tax / 100));

        estimatedTotal.textContent = `₹${currency.format(Math.max(0, total))}`;
        guestSummary.textContent = `${adults} adult${adults === 1 ? '' : 's'} · ${children} child${children === 1 ? '' : 'ren'}`;
    }

    adultCountInput.addEventListener('input', updateBookingTotal);
    childCountInput.addEventListener('input', updateBookingTotal);
    updateBookingTotal();
</script>
@endpush
