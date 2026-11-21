@extends('layouts.app')

@section('title', 'Book ' . $package->name)

@section('content')
    @php
        $pricingConfig = [
            'adultBase' => (float) $package->base_price,
            'childBase' => (float) ($package->child_price ?? $package->base_price),
            'hotels' => $package->planItems->where('item_type', 'hotel')->map(fn($item): array => [
                'name' => $item->hotelRoom?->hotel?->name ?? $item->hotelRoom?->roomType?->name ?? $item->hotelRoom?->name ?? 'Hotel',
                'nights' => (int) ($item->nights ?? 1),
                'adult' => (float) ($item->hotelRoom?->customer_adult_price ?? 0),
                'child' => (float) ($item->hotelRoom?->customer_child_price ?? $item->hotelRoom?->customer_adult_price ?? 0),
                'extraBed' => (float) ($item->hotelRoom?->customer_extra_bed_price ?? 0),
            ])->values()->all(),
            'hasHotel' => $package->planItems->contains(fn($item): bool => $item->item_type === 'hotel' && $item->hotel_room_id !== null),
            'places' => $package->planItems->map(fn($item): array => [
                'id' => $item->place?->id,
                'indian' => $item->place?->adultPriceForCountry('India') ?? 0,
                'foreigner' => $item->place?->adultPriceForCountry('USA') ?? 0,
                'child' => $item->place?->childPrice() ?? 0,
                'studentIndian' => $item->place?->studentPriceForCountry('India') ?? 0,
                'studentForeigner' => $item->place?->studentPriceForCountry('USA') ?? 0,
            ])->values()->all(),
            'stateDays' => $package->planItems
                ->filter(fn($item): bool => $item->place?->city?->state !== null)
                ->map(fn($item): array => [
                    'day' => (int) $item->day_number,
                    'stateId' => (int) $item->place->city->state->id,
                    'price' => (float) $item->place->city->state->price_per_day,
                ])
                ->unique(fn(array $stateDay): string => $stateDay['day'] . '-' . $stateDay['stateId'])
                ->values()
                ->all(),
            'fixed' => (float) $package->planItems->sum(fn($item): float => (float) $item->additional_price),
            'placeAdditional' => (float) $package->planItems
                ->filter(fn($item): bool => $item->item_type === 'place' && $item->package_inclusion_package_id === null)
                ->sum(fn($item): float => (float) $item->additional_price),
            'discount' => (float) $package->discount_percentage,
            'tax' => (float) $package->tax_percentage,
        ];
    @endphp
    <div class="booking-page-background">
        <div class="booking-page-container">
            <a href="{{ route('packages.show', $package->slug) }}"
                class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-orange-600">
                <i class="fa-solid fa-arrow-left text-xs"></i> Back to package details
            </a>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="px-6 py-8 text-slate-900 sm:px-10" style="background: linear-gradient(45deg, var(--accent-orange), var(--accent-yellow), var(--accent-orange), var(--accent-yellow))">
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/10 text-xl ring-1 ring-slate-900/15">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-800">Start your journey</p>
                        <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Book your holiday</h1>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-800 sm:text-base">Share your details and our
                            travel team will contact you to confirm the itinerary and availability.</p>
                    </div>

                    <div class="px-6 py-7 sm:px-10 sm:py-9">
                        @if ($errors->any())
                            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                                role="alert">
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
                            <input type="hidden" name="itinerary" id="booking-itinerary-override"
                                value="{{ old('itinerary') }}">
                            @if ($package->availabilities->isNotEmpty())
                                <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-4 sm:p-5">
                                    <label for="departure_date" class="mb-2 block text-sm font-extrabold text-orange-950">Package
                                        departure date <span class="text-red-500">*</span></label>
                                    <select id="departure_date" name="departure_date" required
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium">
                                        <option value="">Choose a departure date</option>
                                        @foreach ($package->availabilities as $availability)
                                            <option value="{{ $availability->departure_date->format('Y-m-d') }}"
                                                @selected(old('departure_date') === $availability->departure_date->format('Y-m-d'))>
                                                {{ $availability->departure_date->format('M d, Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-slate-500">Hotel dates follow the package itinerary for this
                                        departure.</p>
                                </div>
                            @endif
                            <div>
                                <h2 class="text-lg font-extrabold text-slate-900">Your contact details</h2>
                                <p class="mt-1 text-sm text-slate-500">We will use these details only to follow up on your
                                    booking.</p>
                            </div>

                            <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-4 sm:p-5">
                                <div class="mb-4">
                                    <h3 class="text-sm font-extrabold text-orange-950">Who is travelling?</h3>
                                    <p class="mt-1 text-xs text-orange-700">Children are guests aged 3–15 years. Students
                                        are included in the adult count.</p>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="adult_count" class="mb-2 block text-sm font-bold text-slate-700">Adults
                                            <span class="text-rose-500">*</span></label>
                                        <input type="number" name="adult_count" id="adult_count"
                                            value="{{ old('adult_count', 1) }}" min="1" max="99" required
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
                                    </div>
                                    <div>
                                        <label for="child_count"
                                            class="mb-2 block text-sm font-bold text-slate-700">Children <span
                                                class="font-normal text-slate-400">(3–15 years)</span></label>
                                        <input type="number" name="child_count" id="child_count"
                                            value="{{ old('child_count', 0) }}" min="0" max="99" required
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
                                    </div>
                                    <div>
                                        <label for="student_count"
                                            class="mb-2 block text-sm font-bold text-slate-700">Students</label>
                                        <input type="number" name="student_count" id="student_count"
                                            value="{{ old('student_count', 0) }}" min="0" max="99" required
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-4 sm:p-5">
                                <h3 class="text-sm font-extrabold text-orange-950">
                                    {{ $pricingConfig['hasHotel'] ? 'Hotel room plan' : 'Room recommendation' }}</h3>
                                {{-- <p class="mt-1 text-xs text-slate-600">Each room includes up to 2 adults and 1 child.
                                    You may request one extra bed per room; each extra bed is charged per night.</p> --}}
                                <p class="mt-3 text-sm font-extrabold text-orange-900">Rooms needed for your group: <span
                                        id="required-room-count" aria-live="polite"></span></p>
                                <p id="room-summary" class="mt-3 font-semibold text-orange-800" aria-live="polite"></p>
                                <p id="room-suggestion" class="mt-1 text-xs text-slate-600" aria-live="polite"></p>
                                <label for="room_count" class="mt-4 block text-sm font-semibold text-slate-700">Rooms
                                    requested (you can change this)</label>
                                <input type="number" id="room_count" name="room_count" min="1" max="20"
                                    value="{{ old('room_count', $suggestedRoomCount) }}"
                                    data-user-selected="{{ old('room_count') !== null ? 'true' : 'false' }}"
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium">
                                <button type="button" id="use-suggested-rooms"
                                    class="mt-2 text-xs font-bold text-orange-700 underline">Use suggested room count</button>
                                <label for="extra_bed_count" class="mt-4 block text-sm font-semibold text-slate-700">Extra
                                    beds (if needed)</label>
                                <input type="number" id="extra_bed_count" name="extra_bed_count" min="0" max="20"
                                    value="{{ old('extra_bed_count', 0) }}"
                                    data-user-selected="{{ old('extra_bed_count') !== null ? 'true' : 'false' }}"
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium">
                                <p id="extra-bed-summary" class="mt-2 text-xs text-slate-600"></p>
                                @if ($pricingConfig['hasHotel'])
                                    <p id="hotel-price-summary" class="mt-3 text-xs leading-5 text-slate-700"></p>
                                @else
                                    <p class="mt-3 text-xs leading-5 text-slate-600">No hotel room is linked to this package
                                        yet. Your room and extra-bed requests will be saved for follow-up; hotel charges are not
                                        included in this estimate.</p>
                                @endif
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Full name <span
                                            class="text-rose-500">*</span></label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                        autocomplete="name"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                                        placeholder="Enter your full name">
                                </div>
                                <div>
                                    <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email address
                                        <span class="text-rose-500">*</span></label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                        autocomplete="email"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                                        placeholder="you@example.com">
                                </div>
                                <div>
                                    <label for="mobile" class="mb-2 block text-sm font-bold text-slate-700">Mobile number
                                        <span class="text-rose-500">*</span></label>
                                    <input type="tel" name="mobile" id="mobile" value="{{ old('mobile') }}" required
                                        autocomplete="tel"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                                        placeholder="+91 98765 43210">
                                </div>
                                <div>
                                    <label for="country" class="mb-2 block text-sm font-bold text-slate-700">Country <span
                                            class="text-rose-500">*</span></label>
                                    <select name="country" id="country" required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
                                        @foreach (['India', 'USA', 'UK', 'Canada', 'Australia', 'UAE', 'Singapore', 'Others'] as $country)
                                            <option value="{{ $country }}" @selected(old('country', 'India') === $country)>
                                                {{ $country }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="photo_id_proof" class="mb-2 block text-sm font-bold text-slate-700">Photo ID
                                    proof <span class="font-normal text-slate-400">(optional)</span></label>
                                <input type="text" name="photo_id_proof" id="photo_id_proof"
                                    value="{{ old('photo_id_proof') }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                                    placeholder="Passport, driving licence, etc.">
                            </div>

                            <div
                                class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs leading-5 text-slate-500"><i
                                        class="fa-solid fa-lock mr-1 text-emerald-600"></i> Your information is kept
                                    private.</p>
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-600 px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5 hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200" style="background: #4b77d4">Confirm
                                    booking <i class="fa-solid fa-arrow-right text-xs"></i></button>
                            </div>
                        </form>
                    </div>
                </section>

                <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:sticky lg:top-24">
                    @if ($package->youtubeEmbedUrl())
                        <a href="{{ $package->youtubeWatchUrl() }}" target="_blank" rel="noopener noreferrer" class="group relative flex h-52 w-full items-center justify-center overflow-hidden bg-slate-950" aria-label="Watch {{ $package->name }} video on YouTube">
                            <img src="{{ $package->youtubeThumbnailUrl() }}" alt="{{ $package->name }} video thumbnail" class="absolute inset-0 h-full w-full object-cover opacity-75 transition group-hover:scale-105">
                            <span class="absolute inset-0 bg-slate-950/30"></span>
                            <span class="relative rounded-full bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-xl group-hover:bg-red-700"><i class="fa-brands fa-youtube mr-2"></i>Watch on YouTube</span>
                        </a>
                    @elseif ($package->image)
                        <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}"
                            class="h-52 w-full object-cover">
                    @else
                        <div
                            class="flex h-52 items-center justify-center bg-gradient-to-br from-orange-100 to-orange-100 text-orange-400">
                            <i class="fa-solid fa-mountain-sun text-5xl"></i>
                        </div>
                    @endif
                    <div class="p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-orange-600">Selected package</p>
                        <h2 class="mt-2 text-xl font-black text-slate-900">{{ $package->name }}</h2>
                        <div class="mt-4 grid grid-cols-2 gap-3 border-y border-slate-100 py-4 text-sm">
                            <div>
                                <p class="text-xs text-slate-500">Duration</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $package->days }}D / {{ $package->nights }}N</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Base adult price</p>
                                <p class="mt-1 font-bold text-slate-800">
                                    ₹{{ number_format((float) $package->base_price, 0) }}</p>
                            </div>
                        </div>
                        <div class="mt-5 flex items-end justify-between rounded-xl bg-orange-50 px-4 py-3">
                            <span class="text-sm font-semibold text-orange-900">Estimated total</span>
                            <span id="estimated-total"
                                class="text-xl font-black text-orange-700">₹{{ number_format((float) $estimatedTotal, 0) }}</span>
                        </div>
                        <p id="guest-summary" class="mt-2 text-right text-xs text-slate-500">1 adult · 0 students · 0
                            children</p>
                        <p class="mt-4 text-sm leading-6 text-slate-500">Ready to explore? Submit your details and we will
                            help you plan the next steps.</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    @if(session()->has('booking_confirmation'))
        @php($bookingConfirmation = session('booking_confirmation'))
        <div id="booking-confirmation-modal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="booking-confirmation-title" aria-describedby="booking-confirmation-message">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="bg-gradient-to-r from-orange-500 via-amber-400 to-orange-500 px-6 py-7 text-slate-900 sm:px-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/70 text-xl text-emerald-700"><i class="fa-solid fa-check"></i></span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-700">Booking received</p>
                                <h2 id="booking-confirmation-title" class="mt-1 text-xl font-black sm:text-2xl">Thank you, {{ $bookingConfirmation['name'] }}!</h2>
                            </div>
                        </div>
                        <button type="button" id="close-booking-confirmation" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900/10 text-slate-800 transition hover:bg-slate-900/20" aria-label="Close booking confirmation"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <p id="booking-confirmation-message" class="mt-4 text-sm leading-6 text-slate-800">Your booking request has been saved. Our travel team will contact you soon to confirm your holiday.</p>
                </div>
                <div class="space-y-4 p-6 sm:p-8">
                    <div class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm sm:grid-cols-2">
                        <div><p class="text-xs text-slate-500">Booking reference</p><p class="mt-1 font-bold text-slate-900">#{{ $bookingConfirmation['id'] }}</p></div>
                        <div><p class="text-xs text-slate-500">Estimated amount</p><p class="mt-1 font-bold text-slate-900">₹{{ number_format($bookingConfirmation['amount'], 2) }}</p></div>
                        <div class="min-w-0"><p class="text-xs text-slate-500">Email</p><p class="mt-1 break-words font-semibold text-slate-800">{{ $bookingConfirmation['email'] }}</p></div>
                        <div><p class="text-xs text-slate-500">Mobile</p><p class="mt-1 font-semibold text-slate-800">{{ $bookingConfirmation['mobile'] }}</p></div>
                    </div>
                    <button type="button" id="booking-confirmation-done" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold text-white shadow-md transition hover:brightness-95" style="background:#4b77d4">Continue planning <i class="fa-solid fa-arrow-right text-xs"></i></button>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .booking-page-background {
            min-height: 100%;
            background: #f1f5f9;
        }

        .booking-page-container {
            width: 100%;
            max-width: 94rem;
            margin: auto;
            padding: 18px 16px 60px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const bookingConfirmationModal = document.getElementById('booking-confirmation-modal');
        const closeBookingConfirmation = () => {
            bookingConfirmationModal?.remove();
            document.body.style.overflow = '';
        };
        document.getElementById('close-booking-confirmation')?.addEventListener('click', closeBookingConfirmation);
        document.getElementById('booking-confirmation-done')?.addEventListener('click', closeBookingConfirmation);
        bookingConfirmationModal?.addEventListener('click', (event) => {
            if (event.target === bookingConfirmationModal) closeBookingConfirmation();
        });
        if (bookingConfirmationModal) {
            document.body.style.overflow = 'hidden';
            document.getElementById('booking-confirmation-done')?.focus();
        }

        const bookingPricing = @json($pricingConfig);
        const itineraryField = document.getElementById('booking-itinerary-override');
        const itineraryStorageKey = `package-itinerary-{{ $package->slug }}`;
        if (itineraryField && !itineraryField.value) {
            const storedItinerary = sessionStorage.getItem(itineraryStorageKey);
            if (storedItinerary) itineraryField.value = storedItinerary;
        }
        if (itineraryField?.value) {
            try {
                const itinerary = JSON.parse(itineraryField.value);
                if (Array.isArray(itinerary.items)) {
                    bookingPricing.places = itinerary.items.map(item => ({
                        indian: Number(item.adult_india || 0),
                        foreigner: Number(item.adult_foreigner || 0),
                        child: Number(item.child_price || 0),
                        studentIndian: Number(item.student_india || 0),
                        studentForeigner: Number(item.student_foreigner || 0),
                    }));
                    bookingPricing.fixed = Number(bookingPricing.fixed || 0)
                        - Number(bookingPricing.placeAdditional || 0)
                        + itinerary.items.reduce((total, item) => total + Number(item.additional_price || 0), 0);
                    const stateDayMap = new Map();
                    itinerary.items.forEach(item => {
                        if (Number(item.state_id) && Number(item.state_daily_price)) {
                            stateDayMap.set(`${item.day_number}-${item.state_id}`, {
                                day: Number(item.day_number),
                                stateId: Number(item.state_id),
                                price: Number(item.state_daily_price),
                            });
                        }
                    });
                    bookingPricing.stateDays = [...stateDayMap.values()];
                }
            } catch (error) {
                itineraryField.value = '';
                sessionStorage.removeItem(itineraryStorageKey);
            }
        }
        document.querySelector('form[action="{{ route('packages.book.store', $package->slug) }}"]')?.addEventListener('submit', () => {
            sessionStorage.removeItem(itineraryStorageKey);
        });
        const adultCountInput = document.getElementById('adult_count');
        const childCountInput = document.getElementById('child_count');
        const studentCountInput = document.getElementById('student_count');
        const countryInput = document.getElementById('country');
        const estimatedTotal = document.getElementById('estimated-total');
        const guestSummary = document.getElementById('guest-summary');
        const roomCountInput = document.getElementById('room_count');
        const extraBedCountInput = document.getElementById('extra_bed_count');
        const roomSummary = document.getElementById('room-summary');
        const requiredRoomCount = document.getElementById('required-room-count');
        const roomSuggestion = document.getElementById('room-suggestion');
        const useSuggestedRoomsButton = document.getElementById('use-suggested-rooms');
        const extraBedSummary = document.getElementById('extra-bed-summary');
        const hotelPriceSummary = document.getElementById('hotel-price-summary');
        const currency = new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 });

        function suggestedRoomCount(adults, children) {
            return Math.max(1, Math.ceil(adults / 2), children, Math.ceil((adults + children) / 3));
        }

        function updateBookingTotal() {
            const adults = Math.max(1, Number(adultCountInput.value) || 1);
            const children = Math.max(0, Number(childCountInput.value) || 0);
            const students = Math.min(adults, Math.max(0, Number(studentCountInput.value) || 0));
            studentCountInput.max = adults;
            if (Number(studentCountInput.value) > adults) {
                studentCountInput.value = adults;
            }
            const country = countryInput.value === 'India' ? 'India' : 'Foreigner';
            const suggestedRooms = suggestedRoomCount(adults, children);
            if (roomCountInput && roomCountInput.dataset.userSelected !== 'true') {
                roomCountInput.value = suggestedRooms;
            }
            const rooms = Math.max(1, Number(roomCountInput?.value) || suggestedRooms);
            const requiredExtraBeds = Math.max(0, adults - (rooms * 2), children - rooms, adults + children - (rooms * 3));
            let extraBeds = requiredExtraBeds;
            if (roomCountInput && extraBedCountInput) {
                if (extraBedCountInput.dataset.userSelected === 'true') {
                    extraBeds = Math.max(requiredExtraBeds, Number(extraBedCountInput.value) || 0);
                }
                extraBedCountInput.min = requiredExtraBeds;
                extraBedCountInput.max = rooms;
                if (extraBeds > rooms && requiredExtraBeds <= rooms) {
                    extraBeds = rooms;
                }
                extraBedCountInput.value = extraBeds;
                extraBedCountInput.setCustomValidity(requiredExtraBeds > rooms
                    ? `This room count cannot fit all guests. Select at least ${suggestedRooms} rooms.`
                    : '');
            }
            let subtotal = (bookingPricing.adultBase * adults) + (bookingPricing.childBase * children) + bookingPricing.fixed;

            const roomAllocations = Array.from({ length: rooms }, () => ({ adults: 0, children: 0, extraBeds: 0 }));
            let adultsToPlace = adults;
            let childrenToPlace = children;
            let bedsToPlace = extraBeds;

            roomAllocations.forEach((room) => {
                room.adults = Math.min(2, adultsToPlace);
                adultsToPlace -= room.adults;
                room.children = Math.min(1, childrenToPlace);
                childrenToPlace -= room.children;
                room.extraBeds = Math.min(1, bedsToPlace);
                bedsToPlace -= room.extraBeds;
            });

            roomAllocations.forEach((room) => {
                if (room.extraBeds > 0 && adultsToPlace > 0) {
                    room.adults += 1;
                    adultsToPlace -= 1;
                } else if (room.extraBeds > 0 && childrenToPlace > 0) {
                    room.children += 1;
                    childrenToPlace -= 1;
                }
            });

            const hotelLines = [];
            bookingPricing.hotels.forEach((hotel) => {
                let hotelTotal = 0;

                roomAllocations.forEach((room, index) => {
                    const roomTotal = ((hotel.adult * room.adults) + (hotel.child * room.children) + (hotel.extraBed * room.extraBeds)) * hotel.nights;
                    hotelTotal += roomTotal;
                    hotelLines.push(`${hotel.name}, room ${index + 1}: Rs ${currency.format(roomTotal)} for ${hotel.nights} night${hotel.nights === 1 ? '' : 's'}`);
                });

                subtotal += hotelTotal;
            });

            bookingPricing.places.forEach((place) => {
                const adultRate = country === 'India' ? place.indian : place.foreigner;
                const studentRate = country === 'India' ? place.studentIndian : place.studentForeigner;
                subtotal += (adultRate * (adults - students)) + (place.child * children) + (studentRate * students);
            });
            subtotal += bookingPricing.stateDays.reduce((stateTotal, stateDay) => stateTotal + (stateDay.price * (adults + children)), 0);
            const discountedTotal = subtotal * (1 - (bookingPricing.discount / 100));
            const total = discountedTotal * (1 + (bookingPricing.tax / 100));

            estimatedTotal.textContent = `₹${currency.format(Math.max(0, total))}`;
            guestSummary.textContent = `${adults} adult${adults === 1 ? '' : 's'} · ${students} student${students === 1 ? '' : 's'} · ${children} child${children === 1 ? '' : 'ren'}`;
            if (roomCountInput) {
                const extraBedCost = bookingPricing.hotels.reduce((total, hotel) => total + (hotel.extraBed * extraBeds * hotel.nights), 0);
                requiredRoomCount.textContent = `${suggestedRooms} room${suggestedRooms === 1 ? '' : 's'}`;
                roomSummary.textContent = `${suggestedRooms} room${suggestedRooms === 1 ? '' : 's'} suggested for ${adults} adult${adults === 1 ? '' : 's'} and ${children} child${children === 1 ? '' : 'ren'}. You selected ${rooms}. Each room allows 2 adults, 1 child, and up to 1 extra bed.`;
                // roomSuggestion.textContent = `This recommendation assumes 2 adults and 1 child per room. You can change the selected room count; fewer rooms may require extra beds.`;
                if (extraBedSummary) {
                    extraBedSummary.textContent = extraBeds
                        ? `${extraBeds} extra bed${extraBeds === 1 ? '' : 's'} · ₹${currency.format(extraBedCost)} total extra-bed charge`
                        : 'No extra-bed charge';
                }
                if (hotelPriceSummary) {
                    hotelPriceSummary.textContent = hotelLines.length
                        ? `Estimated hotel cost by room: ${hotelLines.join(' | ')}`
                        : '';
                }
            }
        }

        adultCountInput.addEventListener('input', updateBookingTotal);
        childCountInput.addEventListener('input', updateBookingTotal);
        studentCountInput.addEventListener('input', updateBookingTotal);
        countryInput.addEventListener('change', updateBookingTotal);
        roomCountInput?.addEventListener('input', function () {
            roomCountInput.dataset.userSelected = 'true';
            updateBookingTotal();
        });
        useSuggestedRoomsButton?.addEventListener('click', function () {
            const adults = Math.max(1, Number(adultCountInput.value) || 1);
            const children = Math.max(0, Number(childCountInput.value) || 0);
            roomCountInput.value = suggestedRoomCount(adults, children);
            roomCountInput.dataset.userSelected = 'true';
            updateBookingTotal();
        });
        extraBedCountInput?.addEventListener('input', function () {
            extraBedCountInput.dataset.userSelected = 'true';
            updateBookingTotal();
        });

        updateBookingTotal();
    </script>
@endpush
