@extends('layouts.app')

@section('title', 'Booking Details')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('custom-packages.show', $customPackage) }}" class="text-sm font-semibold text-indigo-700 hover:underline">&larr; Back to your package</a>
    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Final step</p>
        <h1 class="mt-1 text-3xl font-black text-slate-900">Booking details</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Your custom package, places, and inclusions will be sent privately to the admin with these details. This is a booking request; the team will contact you to confirm the itinerary and price.</p>
        <div class="mt-5 rounded-xl bg-indigo-50 p-4">
            <p class="font-bold text-indigo-950">{{ $customPackage->name }}</p>
            <p class="mt-1 text-sm text-indigo-800">{{ $customPackage->departure_city }} &middot; {{ $customPackage->days }} days / {{ $customPackage->nights }} nights &middot; {{ count($customPackage->itinerary) }} itinerary days</p>
        </div>

        <div class="mt-4 rounded-xl border border-indigo-100 bg-white p-4" aria-live="polite">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Estimated place admission total</p>
            <p id="custom-booking-estimate" class="mt-1 text-2xl font-black text-indigo-950">₹0</p>
            <p class="mt-1 text-xs text-slate-600">Based on selected place admission rates and state daily rates. Hotels, travel, and custom inclusions are not included; the admin will confirm the final price.</p>
        </div>

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('custom-packages.booking.submit', $customPackage) }}" class="mt-6 space-y-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label for="customer_name" class="mb-1 block text-sm font-semibold text-slate-700">Full name *</label><input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="150" autocomplete="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email *</label><input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="mobile" class="mb-1 block text-sm font-semibold text-slate-700">Mobile number *</label><input id="mobile" name="mobile" type="tel" value="{{ old('mobile') }}" required maxlength="30" autocomplete="tel" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="departure_date" class="mb-1 block text-sm font-semibold text-slate-700">Preferred travel date <span class="font-normal text-slate-400">(optional)</span></label><input id="departure_date" name="departure_date" type="date" value="{{ old('departure_date') }}" min="{{ now()->toDateString() }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="adult_count" class="mb-1 block text-sm font-semibold text-slate-700">Adults *</label><input id="adult_count" name="adult_count" type="number" value="{{ old('adult_count', $customPackage->traveler_counts['adult_count'] ?? 1) }}" min="1" max="99" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="child_count" class="mb-1 block text-sm font-semibold text-slate-700">Children</label><input id="child_count" name="child_count" type="number" value="{{ old('child_count', $customPackage->traveler_counts['child_count'] ?? 0) }}" min="0" max="99" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            </div>
            <div><label for="message" class="mb-1 block text-sm font-semibold text-slate-700">Anything else we should know? <span class="font-normal text-slate-400">(optional)</span></label><textarea id="message" name="message" rows="4" maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Room preferences, accessibility needs, or questions">{{ old('message') }}</textarea></div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-extrabold text-white hover:bg-indigo-700">Send package and booking request</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const customItinerary = {{ \Illuminate\Support\Js::from($customPackage->itinerary) }};
    const adultsInput = document.getElementById('adult_count');
    const childrenInput = document.getElementById('child_count');
    const estimateOutput = document.getElementById('custom-booking-estimate');

    function updateCustomBookingEstimate() {
        const adults = Math.max(1, Number(adultsInput.value) || 1);
        const children = Math.max(0, Number(childrenInput.value) || 0);
        const total = customItinerary.reduce((itineraryTotal, day) => {
            const dayPlacesTotal = day.places.reduce((dayTotal, place) => dayTotal + (Number(place.adult_price || 0) * adults) + (Number(place.child_price || 0) * children), 0);
            const statePrices = new Map(day.places.map((place) => [String(place.state_id), Number(place.state_daily_price || 0)]));
            const dailyStateTotal = Array.from(statePrices.values()).reduce((dayTotal, price) => dayTotal + price, 0) * (adults + children);
            return itineraryTotal + dayPlacesTotal + dailyStateTotal;
        }, 0);
        estimateOutput.textContent = `₹${total.toLocaleString('en-IN', { maximumFractionDigits: 2 })}`;
    }

    adultsInput.addEventListener('input', updateCustomBookingEstimate);
    childrenInput.addEventListener('input', updateCustomBookingEstimate);
    updateCustomBookingEstimate();
</script>
@endpush
