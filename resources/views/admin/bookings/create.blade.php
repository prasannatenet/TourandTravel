@extends('layouts.admin')

@section('title', 'Add Booking')
@section('header', 'Add a New Booking')
@section('subheader', 'Enter the customer and travel details, then process the itinerary for this booking.')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
            <p class="font-semibold">Please correct the following details:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @csrf

        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Customer details</h2>
                <p class="mt-1 text-sm text-slate-500">The quotation will be sent to this email after you save the processed booking.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Full name <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email <span class="text-rose-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="mobile" class="mb-1 block text-sm font-semibold text-slate-700">Mobile <span class="text-rose-500">*</span></label>
                    <input id="mobile" name="mobile" type="tel" value="{{ old('mobile') }}" required maxlength="30" autocomplete="tel" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="country" class="mb-1 block text-sm font-semibold text-slate-700">Country <span class="text-rose-500">*</span></label>
                    <input id="country" name="country" type="text" value="{{ old('country', 'India') }}" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div class="sm:col-span-2">
                    <label for="photo_id_proof" class="mb-1 block text-sm font-semibold text-slate-700">Photo ID proof <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="photo_id_proof" name="photo_id_proof" type="text" value="{{ old('photo_id_proof') }}" maxlength="255" placeholder="Passport, driving licence, etc." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>
        </section>

        <section class="space-y-4 border-t border-slate-100 pt-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Trip details</h2>
                <p class="mt-1 text-sm text-slate-500">The booking starts as pending. You can customize its package and day-by-day itinerary on the next screen.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="package_type" class="mb-1 block text-sm font-semibold text-slate-700">Package type <span class="text-rose-500">*</span></label>
                    <select id="package_type" name="package_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="existing" @selected(old('package_type', 'existing') === 'existing')>Use an existing package</option>
                        <option value="custom" @selected(old('package_type') === 'custom')>Create a fully custom package</option>
                    </select>
                </div>
                <div id="existing-package-fields" class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="package_id" class="mb-1 block text-sm font-semibold text-slate-700">Package <span class="text-rose-500">*</span></label>
                        <select id="package_id" name="package_id" required @disabled($packages->isEmpty()) class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="">Choose a package</option>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}" data-departures="{{ $package->availabilities->map(fn ($availability): string => $availability->departure_date->format('Y-m-d'))->implode(',') }}" @selected((string) old('package_id') === (string) $package->id)>{{ $package->name }} · Rs. {{ number_format((float) $package->base_price, 2) }} per adult</option>
                            @endforeach
                        </select>
                        @if ($packages->isEmpty())
                            <p class="mt-1 text-xs text-amber-700">No active packages are available. You can still create a fully custom package.</p>
                        @endif
                    </div>
                    <div>
                        <label for="departure_date" class="mb-1 block text-sm font-semibold text-slate-700">Departure date <span class="font-normal text-slate-400">(if known)</span></label>
                        <input id="departure_date" name="departure_date" type="date" list="package-departures" value="{{ old('departure_date') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <datalist id="package-departures"></datalist>
                        <p id="departure-date-hint" class="mt-1 text-xs text-slate-500">If the selected package has preset departure dates, choose one of those dates.</p>
                    </div>
                </div>
                <section id="custom-package-fields" hidden class="space-y-5 sm:col-span-2">
                    <div class="rounded-xl border border-violet-200 bg-violet-50/60 p-4 sm:p-5">
                        <h3 class="font-bold text-violet-950">Custom package details</h3>
                        <p class="mt-1 text-sm text-violet-800">This package is private to this booking and will not be published in the public package catalogue. You can add its day-by-day itinerary on the next screen.</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label for="custom_package_name" class="mb-1 block text-sm font-semibold text-slate-700">Package name <span class="text-rose-500">*</span></label>
                                <input id="custom_package_name" name="custom_package_name" type="text" value="{{ old('custom_package_name') }}" maxlength="120" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_departure_city" class="mb-1 block text-sm font-semibold text-slate-700">Departure city <span class="text-rose-500">*</span></label>
                                <input id="custom_departure_city" name="custom_departure_city" type="text" value="{{ old('custom_departure_city') }}" maxlength="100" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_category" class="mb-1 block text-sm font-semibold text-slate-700">Category / theme</label>
                                <input id="custom_category" name="custom_category" type="text" value="{{ old('custom_category') }}" maxlength="80" placeholder="Family, Adventure, Honeymoon..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_tour_type" class="mb-1 block text-sm font-semibold text-slate-700">Trip style <span class="text-rose-500">*</span></label>
                                <select id="custom_tour_type" name="custom_tour_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                    <option value="group" @selected(old('custom_tour_type', 'group') === 'group')>Group tour</option>
                                    <option value="private" @selected(old('custom_tour_type') === 'private')>Private tour</option>
                                </select>
                            </div>
                            <div>
                                <label for="custom_departure_date" class="mb-1 block text-sm font-semibold text-slate-700">Departure date <span class="text-rose-500">*</span></label>
                                <input id="custom_departure_date" name="custom_departure_date" type="date" value="{{ old('custom_departure_date') }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_days" class="mb-1 block text-sm font-semibold text-slate-700">Trip duration (days) <span class="text-rose-500">*</span></label>
                                <input id="custom_days" name="custom_days" type="number" value="{{ old('custom_days', 1) }}" min="1" max="99" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label for="custom_description" class="mb-1 block text-sm font-semibold text-slate-700">Package description</label>
                                <textarea id="custom_description" name="custom_description" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">{{ old('custom_description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4 sm:p-5">
                        <h3 class="font-bold text-slate-900">Custom package pricing</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="custom_base_price" class="mb-1 block text-sm font-semibold text-slate-700">Adult price per person (Rs.) <span class="text-rose-500">*</span></label>
                                <input id="custom_base_price" name="custom_base_price" type="number" value="{{ old('custom_base_price') }}" min="0" max="99999999.99" step="0.01" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_child_price" class="mb-1 block text-sm font-semibold text-slate-700">Child price (Rs.)</label>
                                <input id="custom_child_price" name="custom_child_price" type="number" value="{{ old('custom_child_price') }}" min="0" max="999999.99" step="0.01" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_infant_price" class="mb-1 block text-sm font-semibold text-slate-700">Infant price (Rs.)</label>
                                <input id="custom_infant_price" name="custom_infant_price" type="number" value="{{ old('custom_infant_price') }}" min="0" max="999999.99" step="0.01" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_discount_percentage" class="mb-1 block text-sm font-semibold text-slate-700">Discount (%) <span class="text-rose-500">*</span></label>
                                <input id="custom_discount_percentage" name="custom_discount_percentage" type="number" value="{{ old('custom_discount_percentage', 0) }}" min="0" max="100" step="0.01" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="custom_tax_percentage" class="mb-1 block text-sm font-semibold text-slate-700">Tax (%) <span class="text-rose-500">*</span></label>
                                <input id="custom_tax_percentage" name="custom_tax_percentage" type="number" value="{{ old('custom_tax_percentage', 0) }}" min="0" max="100" step="0.01" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-slate-500">The booking total is calculated from these rates and the traveler counts above. Hotel and activity costs are added when you build the itinerary next.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4 sm:p-5">
                        <h3 class="font-bold text-slate-900">Custom package destinations</h3>
                        <p class="mt-1 text-sm text-slate-500">Choose at least one country, state, and city. You can add destinations, hotels, and services to the itinerary on the next screen.</p>
                        @php
                            $selectedCountries = array_map('strval', old('custom_destinations.country', []));
                            $selectedStates = array_map('strval', old('custom_destinations.state', []));
                            $selectedCities = array_map('strval', old('custom_destinations.city', []));
                        @endphp
                        <div class="mt-4 space-y-5">
                            <fieldset>
                                <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Countries <span class="text-rose-500">*</span></legend>
                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($countries as $destinationCountry)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <input type="checkbox" name="custom_destinations[country][]" value="{{ $destinationCountry->id }}" @checked(in_array((string) $destinationCountry->id, $selectedCountries, true)) class="custom-destination-country rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>{{ $destinationCountry->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">States <span class="text-rose-500">*</span></legend>
                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($states as $state)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" data-custom-state-option data-country-id="{{ $state->country_id }}">
                                            <input type="checkbox" name="custom_destinations[state][]" value="{{ $state->id }}" @checked(in_array((string) $state->id, $selectedStates, true)) class="custom-destination-state rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>{{ $state->name }} — {{ $state->country?->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Cities <span class="text-rose-500">*</span></legend>
                                <div class="grid max-h-72 gap-2 overflow-y-auto sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($cities as $city)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" data-custom-city-option data-state-id="{{ $city->state_id }}">
                                            <input type="checkbox" name="custom_destinations[city][]" value="{{ $city->id }}" @checked(in_array((string) $city->id, $selectedCities, true)) class="custom-destination-city rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>{{ $city->name }} — {{ $city->state?->name }}, {{ $city->state?->country?->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </section>

                <div>
                    <label for="room_count" class="mb-1 block text-sm font-semibold text-slate-700">Rooms</label>
                    <input id="room_count" name="room_count" type="number" value="{{ old('room_count', 1) }}" min="1" max="20" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="adult_count" class="mb-1 block text-sm font-semibold text-slate-700">Adults <span class="text-rose-500">*</span></label>
                    <input id="adult_count" name="adult_count" type="number" value="{{ old('adult_count', 2) }}" min="1" max="99" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="child_count" class="mb-1 block text-sm font-semibold text-slate-700">Children <span class="text-rose-500">*</span></label>
                    <input id="child_count" name="child_count" type="number" value="{{ old('child_count', 0) }}" min="0" max="99" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="student_count" class="mb-1 block text-sm font-semibold text-slate-700">Students <span class="text-rose-500">*</span></label>
                    <input id="student_count" name="student_count" type="number" value="{{ old('student_count', 0) }}" min="0" max="99" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="extra_bed_count" class="mb-1 block text-sm font-semibold text-slate-700">Extra beds</label>
                    <input id="extra_bed_count" name="extra_bed_count" type="number" value="{{ old('extra_bed_count', 0) }}" min="0" max="20" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create booking and process</button>
        </div>
    </form>
</div>
<script>
    const packageSelect = document.getElementById('package_id');
    const departureDate = document.getElementById('departure_date');
    const departureSuggestions = document.getElementById('package-departures');
    const departureHint = document.getElementById('departure-date-hint');
    const packageTypeSelect = document.getElementById('package_type');
    const existingPackageFields = document.getElementById('existing-package-fields');
    const customPackageFields = document.getElementById('custom-package-fields');
    const countryInputs = Array.from(document.querySelectorAll('.custom-destination-country'));
    const stateOptions = Array.from(document.querySelectorAll('[data-custom-state-option]'));
    const cityOptions = Array.from(document.querySelectorAll('[data-custom-city-option]'));

    function setSectionEnabled(section, enabled) {
        section.querySelectorAll('input, select, textarea').forEach((input) => {
            input.disabled = !enabled;
        });
        section.hidden = !enabled;
        section.classList.toggle('hidden', !enabled);
    }

    function syncDestinations() {
        if (packageTypeSelect.value !== 'custom') {
            return;
        }

        const selectedCountryIds = new Set(countryInputs.filter((input) => input.checked).map((input) => input.value));
        stateOptions.forEach((option) => {
            const input = option.querySelector('.custom-destination-state');
            const available = selectedCountryIds.has(option.dataset.countryId);
            option.classList.toggle('hidden', !available);
            input.disabled = !available || packageTypeSelect.value !== 'custom';
            if (!available) {
                input.checked = false;
            }
        });
        const selectedStateIds = new Set(stateOptions
            .map((option) => option.querySelector('.custom-destination-state'))
            .filter((input) => input.checked && !input.disabled)
            .map((input) => input.value));
        cityOptions.forEach((option) => {
            const input = option.querySelector('.custom-destination-city');
            const available = selectedStateIds.has(option.dataset.stateId);
            option.classList.toggle('hidden', !available);
            input.disabled = !available || packageTypeSelect.value !== 'custom';
            if (!available) {
                input.checked = false;
            }
        });
    }

    function updateDepartureDates() {
        if (!packageSelect || !departureDate || !departureSuggestions || !departureHint) {
            return;
        }
        const selectedPackage = packageSelect.selectedOptions[0];
        const dates = selectedPackage?.dataset.departures?.split(',').filter(Boolean) ?? [];
        departureSuggestions.replaceChildren(...dates.map((date) => {
            const option = document.createElement('option');
            option.value = date;
            return option;
        }));
        departureDate.required = dates.length > 0;
        departureHint.textContent = dates.length > 0
            ? 'Choose one of this package’s preset departure dates.'
            : 'Enter a departure date if known. A date is required for packages that include a hotel.';
        if (dates.length > 0 && !dates.includes(departureDate.value)) {
            departureDate.value = '';
        }
    }

    function syncPackageType() {
        const useExisting = packageTypeSelect.value === 'existing';
        setSectionEnabled(existingPackageFields, useExisting);
        setSectionEnabled(customPackageFields, !useExisting);
        if (packageSelect && packageSelect.options.length <= 1) {
            packageSelect.disabled = true;
        }
        updateDepartureDates();
        syncDestinations();
    }

    packageSelect?.addEventListener('change', updateDepartureDates);
    packageTypeSelect.addEventListener('change', syncPackageType);
    countryInputs.forEach((input) => input.addEventListener('change', syncDestinations));
    stateOptions.forEach((option) => option.querySelector('.custom-destination-state').addEventListener('change', syncDestinations));
    updateDepartureDates();
    syncPackageType();
</script>
@endsection
