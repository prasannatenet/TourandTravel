@extends('layouts.app')

@section('title', 'Build Your Own Package')

@section('content')
<div class="custom-package-page-container space-y-5">
    <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#10223b] via-[#183d68] to-[#315fa8] text-white shadow-lg shadow-slate-900/10" style="background: linear-gradient(45deg, var(--accent-orange), var(--accent-yellow), var(--accent-orange), var(--accent-yellow))">
        <div class="flex flex-col gap-6 px-5 py-6 sm:px-8 sm:py-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-blue-50"><i class="fa-solid fa-suitcase-rolling"></i> Package setup</span>
                <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Build your own trip</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-blue-100">Set up the trip details, choose destinations for each day, and add the inclusions you want us to consider. Your request stays private to this browser session.</p>
            </div>
            <a href="{{ route('packages.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"><i class="fa-solid fa-arrow-left text-xs"></i> Back to packages</a>
        </div>
        <nav aria-label="Package form sections" class="flex flex-wrap gap-2 border-t border-white/15 bg-[#10223b]/20 px-5 py-3 sm:px-8">
            <a href="#custom-package-details" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-orange-200">01</span> Details</a>
            <a href="#custom-package-travelers" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-orange-200">02</span> Travelers & price</a>
            <a href="#custom-package-plan" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-orange-200">03</span> Day-by-day plan</a>
            <a href="#custom-package-destinations" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-orange-200">04</span> Destinations</a>
            <a href="#custom-package-inclusions" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-orange-200">05</span> Services</a>
        </nav>
    </section>

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            <p class="font-bold">Please review the following:</p>
            <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="custom-package-form" method="POST" action="{{ route('custom-packages.store') }}" class="space-y-5">
        @csrf
        @if($places->isEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">There are no places available yet. Please check back after destinations have been added.</div>
        @endif
        <section id="custom-package-details" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">01</span>
                <div><h2 class="text-base font-bold text-slate-900">Package details</h2><p class="mt-1 text-sm text-slate-500">Give your trip a name, starting city, and duration.</p></div>
            </div>
            <div class="grid grid-cols-1 gap-x-5 gap-y-4 px-5 py-5 sm:grid-cols-2 sm:px-7 lg:grid-cols-3">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Package name <span class="text-red-500">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="150" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" placeholder="My Kashmir holiday">
                </div>
                <div>
                    <label for="departure_city" class="mb-1.5 block text-sm font-semibold text-slate-700">Departure city <span class="text-red-500">*</span></label>
                    <input id="departure_city" name="departure_city" value="{{ old('departure_city') }}" required maxlength="100" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" placeholder="Srinagar">
                </div>
                <div>
                    <label for="days" class="mb-1.5 block text-sm font-semibold text-slate-700">Trip length <span class="text-red-500">*</span></label>
                    <div class="flex gap-2"><div class="relative min-w-0 flex-1"><input id="days" name="days" type="number" value="{{ old('days', max(1, count($oldItinerary))) }}" required min="1" max="30" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-12 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"><span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">days</span></div><div class="relative min-w-0 flex-1"><input id="nights" name="nights" type="number" value="{{ old('nights', max(0, count($oldItinerary) - 1)) }}" required min="0" max="29" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-14 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"><span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">nights</span></div></div>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="description" class="mb-1.5 block text-sm font-semibold text-slate-700">Short description <span class="font-normal text-slate-400">(optional)</span></label>
                    <textarea id="description" name="description" rows="3" maxlength="2000" class="w-full resize-y rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" placeholder="What would you like from this trip?">{{ old('description') }}</textarea>
                </div>
            </div>
        </section>

        <section id="custom-package-travelers" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sm font-bold text-sky-700">02</span>
                <div><h2 class="text-base font-bold text-slate-900">Travelers and estimated price</h2><p class="mt-1 text-sm text-slate-500">Set your group size to estimate place admission and state daily costs for this itinerary.</p></div>
            </div>
            <div class="grid gap-5 px-5 py-5 sm:grid-cols-2 sm:px-7">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label for="adult_count" class="mb-1.5 block text-sm font-semibold text-slate-700">Adults <span class="text-red-500">*</span></label><input id="adult_count" name="adult_count" type="number" value="{{ old('adult_count', 1) }}" required min="1" max="99" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"></div>
                    <div><label for="child_count" class="mb-1.5 block text-sm font-semibold text-slate-700">Children</label><input id="child_count" name="child_count" type="number" value="{{ old('child_count', 0) }}" required min="0" max="99" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"></div>
                </div>
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4" aria-live="polite">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Estimated places and state daily total</p>
                    <p id="custom-package-estimate" class="mt-1 text-2xl font-black text-blue-950">₹0</p>
                    <p id="custom-package-estimate-summary" class="mt-1 text-xs text-blue-800">1 adult · 0 children</p>
                    <div id="custom-package-state-price" class="mt-2 space-y-1 text-xs font-semibold text-blue-900">No states selected.</div>
                    <p class="mt-2 text-xs leading-5 text-slate-600">Estimate uses listed India adult and child place rates plus the per-person daily rate for each state visited each day. Hotels, travel, and custom inclusions are not included; the admin will confirm the final package price.</p>
                </div>
            </div>
        </section>

        <section id="custom-package-destinations" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-sm font-bold text-orange-700">03</span>
                <div><h2 class="text-base font-bold text-slate-900">Choose your destinations</h2><p class="mt-1 text-sm text-slate-500">Select multiple countries, states, and cities. Places in the itinerary will come from the cities you check.</p></div>
            </div>
            <div class="grid gap-5 px-5 py-5 sm:grid-cols-3 sm:px-7">
                <fieldset class="rounded-xl border border-slate-200 p-4">
                    <legend class="px-1 text-sm font-bold text-slate-800">Countries</legend>
                    <div id="country-options" class="mt-2 max-h-56 space-y-2 overflow-y-auto"></div>
                </fieldset>
                <fieldset class="rounded-xl border border-slate-200 p-4">
                    <legend class="px-1 text-sm font-bold text-slate-800">States</legend>
                    <div id="state-options" class="mt-2 max-h-56 space-y-2 overflow-y-auto"><p class="text-xs text-slate-500">Choose countries first.</p></div>
                </fieldset>
                <fieldset class="rounded-xl border border-slate-200 p-4">
                    <legend class="px-1 text-sm font-bold text-slate-800">Cities</legend>
                    <div id="city-options" class="mt-2 max-h-56 space-y-2 overflow-y-auto"><p class="text-xs text-slate-500">Choose states first.</p></div>
                </fieldset>
            </div>
        </section>

        <section id="custom-package-plan" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">03</span>
                    <div><h2 class="text-base font-bold text-slate-900">Day-by-day itinerary plan</h2><p class="mt-1 text-sm text-slate-500">Add places and service stops to each day, set start times, and see estimated finish times.</p></div>
                </div>
                <div class="flex items-center gap-2"><span id="itinerary-duration-badge" class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700"></span></div>
            </div>
            <div class="space-y-5 px-5 py-5 sm:px-7"><div id="day-hours-summary" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4"></div><div id="itinerary-day-tabs" class="flex gap-2 overflow-x-auto border-b border-slate-200" role="tablist" aria-label="Itinerary days"></div><div id="itinerary-days"></div></div>
        </section>

        <section id="custom-package-inclusions" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-start gap-4 px-5 py-5 sm:px-7">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm font-bold text-emerald-700">04</span>
                    <div><h2 class="text-base font-bold text-slate-900">Your package services</h2><p class="mt-1 text-sm text-slate-500">Create services here, then add each one directly to the day where it belongs.</p></div>
                </div>
                <div class="mr-5 flex flex-wrap items-center gap-2 sm:mr-7">
                    <button id="add-inclusion" type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50"><i class="fa-solid fa-plus text-[10px]"></i> Add service</button>
                    <button id="save-inclusions" type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700"><i class="fa-solid fa-check"></i> Add services to day</button>
                </div>
            </div>
            <div id="saved-inclusion-list" class="space-y-2 px-5 pt-4 sm:px-7"></div>
            <div id="inclusion-list" class="space-y-3 px-5 pb-3 sm:px-7"></div>
            <p id="inclusions-saved-message" class="hidden px-5 pb-5 text-sm font-semibold text-emerald-700 sm:px-7" role="status"></p>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('packages.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Back to packages</a>
            <button type="submit" @disabled($places->isEmpty()) class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-extrabold text-white shadow hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">Save private package <i class="fa-solid fa-arrow-right ml-1 text-xs"></i></button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .custom-package-page-container {
        width: 100%;
        max-width: 94rem;
        margin: auto;
        padding: 18px 16px 60px;
    }
</style>
@endpush

@push('scripts')
<script>
    const availablePlaces = {{ \Illuminate\Support\Js::from($availablePlaces) }};
    const destinationCountries = {{ \Illuminate\Support\Js::from($countries->map(fn ($country) => ['id' => $country->id, 'name' => $country->name, 'states' => $country->states->map(fn ($state) => ['id' => $state->id, 'name' => $state->name, 'daily_price' => (float) $state->price_per_day, 'cities' => $state->cities->map(fn ($city) => ['id' => $city->id, 'name' => $city->name])->values()])->values()])->values()) }};
    const selectedCountryIds = {{ \Illuminate\Support\Js::from(array_map('strval', old('country_ids', []))) }};
    const selectedStateIds = {{ \Illuminate\Support\Js::from(array_map('strval', old('state_ids', []))) }};
    const selectedCityIds = {{ \Illuminate\Support\Js::from(array_map('strval', old('city_ids', []))) }};
    let itineraryDays = {{ \Illuminate\Support\Js::from($oldItinerary) }};
    let packageInclusions = {{ \Illuminate\Support\Js::from($oldInclusions) }};
    let savedPackageInclusions = packageInclusions
        .filter((inclusion) => inclusion.name?.trim())
        .map((inclusion) => ({ ...inclusion, name: inclusion.name.trim() }));
    packageInclusions = [{ name: '', description: '', duration_hours: '' }];
    const itineraryContainer = document.getElementById('itinerary-days');
    const itineraryDayTabs = document.getElementById('itinerary-day-tabs');
    let activeItineraryDay = 0;
    const inclusionContainer = document.getElementById('inclusion-list');
    const savedInclusionContainer = document.getElementById('saved-inclusion-list');
    const savedInclusionFields = document.createElement('div');
    savedInclusionFields.hidden = true;
    inclusionContainer.before(savedInclusionFields);
    const daysInput = document.getElementById('days');
    const adultsInput = document.getElementById('adult_count');
    const childrenInput = document.getElementById('child_count');
    const estimateOutput = document.getElementById('custom-package-estimate');
    const estimateSummary = document.getElementById('custom-package-estimate-summary');
    const countryOptions = document.getElementById('country-options');
    const stateOptions = document.getElementById('state-options');
    const cityOptions = document.getElementById('city-options');
    const statePriceOutput = document.getElementById('custom-package-state-price');
    const inclusionsSavedMessage = document.getElementById('inclusions-saved-message');
    const itineraryHasErrors = {{ \Illuminate\Support\Js::from($errors->has('itinerary') || $errors->has('inclusions')) }};
    if (itineraryHasErrors) {
        activeItineraryDay = Math.max(0, Number(itineraryDays.findIndex((day) => (day.inclusions ?? []).length > 0)));
    }

    function renderCheckboxOptions(container, items, name, selectedIds, labelForItem) {
        container.replaceChildren();
        if (!items.length) {
            const message = document.createElement('p');
            message.className = 'text-xs text-slate-500';
            message.textContent = 'Choose the level above first.';
            container.append(message);
            return;
        }

        items.forEach((item) => {
            const row = document.createElement('label');
            row.className = 'flex cursor-pointer items-start gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = name;
            checkbox.value = String(item.id);
            checkbox.checked = selectedIds.has(String(item.id));
            checkbox.className = 'mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500';
            checkbox.addEventListener('change', () => updateLocationChoices(name));
            const text = document.createElement('span');
            text.textContent = labelForItem(item);
            row.append(checkbox, text);
            container.append(row);
        });
    }

    function updateLocationChoices(changedName = null) {
        const priorStateIds = changedName === null ? selectedStateIds : Array.from(stateOptions.querySelectorAll('input:checked')).map((input) => input.value);
        const priorCityIds = changedName === null ? selectedCityIds : Array.from(cityOptions.querySelectorAll('input:checked')).map((input) => input.value);
        const countryIds = changedName === null ? selectedCountryIds : Array.from(countryOptions.querySelectorAll('input:checked')).map((input) => input.value);
        if (changedName !== 'country_ids[]') {
            renderCheckboxOptions(countryOptions, destinationCountries, 'country_ids[]', new Set(countryIds), (country) => country.name);
        }
        const stateChoices = destinationCountries.filter((country) => countryIds.includes(String(country.id))).flatMap((country) => country.states);
        const validStateIds = priorStateIds.filter((id) => stateChoices.some((state) => String(state.id) === String(id)));
        if (changedName !== 'state_ids[]') {
            renderCheckboxOptions(stateOptions, stateChoices, 'state_ids[]', new Set(validStateIds), (state) => `${state.name} · ₹${Number(state.daily_price).toLocaleString('en-IN')}/person/day`);
        }
        const stateIds = Array.from(stateOptions.querySelectorAll('input:checked')).map((input) => input.value);
        const selectedStates = stateChoices.filter((state) => stateIds.includes(String(state.id)));
        const cityChoices = selectedStates.flatMap((state) => state.cities.map((city) => ({ ...city, state_id: state.id })));
        const validCityIds = priorCityIds.filter((id) => cityChoices.some((city) => String(city.id) === String(id)));
        if (changedName !== 'city_ids[]') {
            renderCheckboxOptions(cityOptions, cityChoices, 'city_ids[]', new Set(validCityIds), (city) => city.name);
        }
        statePriceOutput.replaceChildren();
        if (selectedStates.length) {
            selectedStates.forEach((state) => {
                const line = document.createElement('p');
                line.textContent = `${state.name}: ₹${Number(state.daily_price).toLocaleString('en-IN')} per person/day`;
                statePriceOutput.append(line);
            });
            const totalLine = document.createElement('p');
            totalLine.className = 'pt-1 font-black';
            const dailyTotal = selectedStates.reduce((total, state) => total + Number(state.daily_price || 0), 0);
            totalLine.textContent = `Selected states daily total: ₹${dailyTotal.toLocaleString('en-IN')} per person`;
            statePriceOutput.append(totalLine);
        } else {
            statePriceOutput.textContent = 'No states selected.';
        }
        updateEstimatedPrice();
        const cityIds = new Set(validCityIds);
        itineraryDays = itineraryDays.map((day) => ({
            ...day,
            place_ids: (day.place_ids ?? []).filter((id) => availablePlaces.some((place) => String(place.id) === String(id) && cityIds.has(String(place.city_id)))),
        }));
        renderItinerary();
    }

    function makeLabel(text, htmlFor) {
        const label = document.createElement('label');
        label.className = 'mb-1 block text-xs font-bold text-slate-600';
        label.htmlFor = htmlFor;
        label.textContent = text;
        return label;
    }

    function syncItineraryInputs() {
        itineraryDays = Array.from(itineraryContainer.querySelectorAll('[data-itinerary-day]')).map((card, index) => ({
            day_number: index + 1,
            title: card.querySelector(`[name="itinerary[${index}][title]"]`)?.value ?? '',
            description: card.querySelector(`[name="itinerary[${index}][description]"]`)?.value ?? '',
            start_time: card.querySelector(`[name="itinerary[${index}][start_time]"]`)?.value ?? '',
            activities: Array.from(card.querySelectorAll('[data-itinerary-activity]')).map((activity) => ({
                type: activity.dataset.activityType,
                id: activity.dataset.activityId,
            })),
            place_ids: Array.from(card.querySelectorAll(`[name="itinerary[${index}][place_ids][]"]`)).map((input) => input.value),
            inclusions: Array.from(card.querySelectorAll(`[name="itinerary[${index}][inclusions][]"]`)).map((input) => input.value),
        }));
    }

    function renderItinerary() {
        const requestedDays = Math.max(1, Math.min(30, Number(daysInput.value) || 1));
        itineraryDays = itineraryDays.slice(0, requestedDays);
        while (itineraryDays.length < requestedDays) {
            itineraryDays.push({ title: '', description: '', place_ids: [], inclusions: [], activities: [] });
        }
        itineraryDays = itineraryDays.map((day) => ({
            ...day,
            place_ids: day.place_ids ?? [],
            inclusions: day.inclusions ?? [],
            activities: day.activities ?? [
                ...(day.place_ids ?? []).map((id) => ({ type: 'place', id: String(id) })),
                ...(day.inclusions ?? []).map((id) => ({ type: 'service', id: String(id) })),
            ],
        }));

        activeItineraryDay = Math.min(activeItineraryDay, itineraryDays.length - 1);
        itineraryDayTabs.replaceChildren();
        itineraryContainer.replaceChildren();
        itineraryDays.forEach((day, index) => {
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.id = `itinerary-day-tab-${index}`;
            tab.role = 'tab';
            tab.setAttribute('aria-controls', `itinerary-day-panel-${index}`);
            tab.setAttribute('aria-selected', String(index === activeItineraryDay));
            tab.tabIndex = index === activeItineraryDay ? 0 : -1;
            tab.className = `shrink-0 rounded-t-xl border-b-2 px-4 py-3 text-sm font-bold transition ${index === activeItineraryDay ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-800'}`;
            tab.textContent = `Day ${index + 1}`;
            tab.addEventListener('click', () => {
                syncItineraryInputs();
                activeItineraryDay = index;
                renderItinerary();
            });
            tab.addEventListener('keydown', (event) => {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }
                event.preventDefault();
                activeItineraryDay = (index + (event.key === 'ArrowRight' ? 1 : -1) + itineraryDays.length) % itineraryDays.length;
                renderItinerary();
                itineraryDayTabs.querySelector('[aria-selected="true"]')?.focus();
            });
            itineraryDayTabs.append(tab);

            const card = document.createElement('div');
            card.className = `rounded-2xl border border-slate-200 bg-slate-50/50 p-4 transition sm:p-5 ${index === activeItineraryDay ? '' : 'hidden'}`;
            card.dataset.itineraryDay = String(index);
            card.id = `itinerary-day-panel-${index}`;
            card.role = 'tabpanel';
            card.setAttribute('aria-labelledby', `itinerary-day-tab-${index}`);

            const dayHeader = document.createElement('div');
            dayHeader.className = 'mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3.5';
            const dayTitle = document.createElement('div');
            dayTitle.className = 'flex items-center gap-3';
            const dayBadge = document.createElement('span');
            dayBadge.className = 'flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-xs font-black text-white shadow-sm';
            dayBadge.textContent = `D${index + 1}`;
            const heading = document.createElement('div');
            const headingText = document.createElement('h3');
            headingText.className = 'text-sm font-black tracking-tight text-slate-900';
            headingText.textContent = `Day ${index + 1} Itinerary`;
            const headingHint = document.createElement('p');
            headingHint.className = 'mt-0.5 text-xs text-slate-500';
            headingHint.textContent = 'Add places and services in the order you plan to do them.';
            heading.append(headingText, headingHint);
            dayTitle.append(dayBadge, heading);
            dayHeader.append(dayTitle);
            card.append(dayHeader);

            const number = document.createElement('input');
            number.type = 'hidden';
            number.name = `itinerary[${index}][day_number]`;
            number.value = String(index + 1);
            card.append(number);

            const daySummary = document.createElement('p');
            daySummary.className = 'mb-3 text-xs font-semibold text-slate-500';
            daySummary.dataset.daySummary = String(index);
            card.append(daySummary);
            const dayCapacity = document.createElement('div');
            dayCapacity.className = 'mb-4 h-2 overflow-hidden rounded-full bg-slate-200';
            const dayCapacityBar = document.createElement('div');
            dayCapacityBar.className = 'h-full rounded-full bg-blue-500 transition-all';
            dayCapacityBar.dataset.dayCapacity = String(index);
            dayCapacity.append(dayCapacityBar);
            card.append(dayCapacity);

            const titleId = `itinerary-title-${index}`;
            const title = document.createElement('input');
            title.id = titleId;
            title.name = `itinerary[${index}][title]`;
            title.value = day.title ?? '';
            title.maxLength = 150;
            title.placeholder = 'Day heading (optional)';
            title.className = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10';
            const titleField = document.createElement('div');
            titleField.className = 'mb-3';
            titleField.append(makeLabel('Day heading (optional)', titleId), title);
            card.append(titleField);

            const descriptionId = `itinerary-description-${index}`;
            const description = document.createElement('textarea');
            description.id = descriptionId;
            description.name = `itinerary[${index}][description]`;
            description.value = day.description ?? '';
            description.maxLength = 1000;
            description.rows = 2;
            description.placeholder = 'Add notes for this day (optional)';
            description.className = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10';
            const descriptionField = document.createElement('div');
            descriptionField.className = 'mb-4';
            descriptionField.append(makeLabel('Day notes (optional)', descriptionId), description);
            card.append(descriptionField);

            const startTimeField = document.createElement('div');
            startTimeField.className = 'mb-4 max-w-xs';
            const startTimeId = `itinerary-start-time-${index}`;
            const startTime = document.createElement('input');
            startTime.type = 'time';
            startTime.id = startTimeId;
            startTime.name = `itinerary[${index}][start_time]`;
            startTime.value = day.start_time ?? '';
            startTime.className = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm';
            startTime.addEventListener('input', updateItineraryDurations);
            startTimeField.append(makeLabel('Day start time (optional)', startTimeId), startTime);
            card.append(startTimeField);

            const placesId = `itinerary-places-${index}`;
            const placesSelect = document.createElement('select');
            placesSelect.id = placesId;
            placesSelect.className = 'min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10';
            placesSelect.required = (day.place_ids ?? []).length === 0;
            const selectedCityIds = new Set(Array.from(cityOptions.querySelectorAll('input:checked')).map((input) => input.value));
            const cityPlaces = availablePlaces.filter((place) => selectedCityIds.has(String(place.city_id)));
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = selectedCityIds.size ? (cityPlaces.length ? 'Choose a place...' : 'No places listed in selected cities') : 'Choose destination cities first';
            placesSelect.append(placeholder);
            const selectedIds = (day.place_ids ?? []).map((id) => String(id));
            cityPlaces.forEach((place) => {
                const option = document.createElement('option');
                option.value = String(place.id);
                option.textContent = `${place.label} · ${place.duration_hours ? `${place.duration_hours} hrs` : 'duration unstated'}${place.best_time ? ` · ${place.best_time}` : ''}`;
                placesSelect.append(option);
            });
            const placePicker = document.createElement('div');
            placePicker.className = 'flex flex-wrap items-end gap-2';
            const pickerField = document.createElement('div');
            pickerField.className = 'min-w-56 flex-1';
            pickerField.append(makeLabel('Add a place', placesId), placesSelect);
            const addPlace = document.createElement('button');
            addPlace.type = 'button';
            addPlace.className = 'inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50';
            addPlace.innerHTML = '<i class="fa-solid fa-plus text-[10px]"></i> Add place';
            addPlace.disabled = cityPlaces.length === 0;
            addPlace.addEventListener('click', () => {
                if (!placesSelect.value || selectedIds.includes(placesSelect.value)) {
                    return;
                }
                syncItineraryInputs();
                itineraryDays[index].place_ids.push(placesSelect.value);
                itineraryDays[index].activities.push({ type: 'place', id: placesSelect.value });
                renderItinerary();
            });
            placePicker.append(pickerField, addPlace);
            card.append(placePicker);

            const activityList = document.createElement('div');
            activityList.className = 'mt-3 space-y-2';
            const activities = day.activities ?? [];
            if (activities.length === 0) {
                const emptyState = document.createElement('div');
                emptyState.className = 'rounded-xl border border-dashed border-slate-300 bg-white/70 px-4 py-5 text-center text-xs text-slate-500';
                emptyState.textContent = 'No activities scheduled for this day yet.';
                activityList.append(emptyState);
            }
            activities.forEach((activity, activityIndex) => {
                const isPlace = activity.type === 'place';
                const activityId = String(activity.id);
                const placeId = isPlace ? activityId : null;
                const inclusionIndex = isPlace ? null : Number(activityId);
                const place = isPlace ? availablePlaces.find((item) => String(item.id) === placeId) : null;
                const inclusion = isPlace ? null : savedPackageInclusions[inclusionIndex];
                if (!place && !inclusion) {
                    return;
                }
                const placeRow = document.createElement('div');
                placeRow.dataset.itineraryActivity = '';
                placeRow.dataset.activityType = activity.type;
                placeRow.dataset.activityId = activityId;
                placeRow.className = `flex items-center justify-between gap-3 rounded-xl border px-3.5 py-3 shadow-sm ${isPlace ? 'border-slate-200 bg-white' : 'border-emerald-100 bg-emerald-50/50'}`;
                const placeName = document.createElement('span');
                placeName.className = `text-sm font-semibold ${isPlace ? 'text-slate-800' : 'text-emerald-900'}`;
                placeName.textContent = `${activityIndex + 1}. ${isPlace ? place.label : inclusion.name} · ${((isPlace ? place.duration_hours : inclusion.duration_hours) ? `${isPlace ? place.duration_hours : inclusion.duration_hours} hrs` : 'duration unstated')}${isPlace ? '' : (inclusion.description ? ` · ${inclusion.description}` : '')}`;
                const actions = document.createElement('div');
                actions.className = 'flex shrink-0 items-center gap-1';
                const moveActivity = (offset) => {
                    const nextIndex = activityIndex + offset;
                    if (nextIndex < 0 || nextIndex >= itineraryDays[index].activities.length) {
                        return;
                    }
                    syncItineraryInputs();
                    const [movedActivity] = itineraryDays[index].activities.splice(activityIndex, 1);
                    itineraryDays[index].activities.splice(nextIndex, 0, movedActivity);
                    renderItinerary();
                };
                [['↑', -1, 'Move up'], ['↓', 1, 'Move down']].forEach(([symbol, offset, label]) => {
                    const moveButton = document.createElement('button');
                    moveButton.type = 'button';
                    moveButton.className = 'rounded-lg px-2 py-1 text-xs font-bold text-slate-500 hover:bg-slate-100';
                    moveButton.textContent = symbol;
                    moveButton.setAttribute('aria-label', `${label} ${isPlace ? 'place' : 'service'}`);
                    moveButton.disabled = activityIndex + Number(offset) < 0 || activityIndex + Number(offset) >= activities.length;
                    moveButton.addEventListener('click', () => moveActivity(Number(offset)));
                    actions.append(moveButton);
                });
                const removePlace = document.createElement('button');
                removePlace.type = 'button';
                removePlace.className = 'rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50';
                removePlace.textContent = 'Remove';
                removePlace.addEventListener('click', () => {
                    syncItineraryInputs();
                    itineraryDays[index].activities.splice(activityIndex, 1);
                    itineraryDays[index].place_ids = itineraryDays[index].place_ids.filter((id) => String(id) !== activityId);
                    itineraryDays[index].inclusions = itineraryDays[index].inclusions.filter((id) => String(id) !== activityId);
                    renderItinerary();
                });
                actions.append(removePlace);
                placeRow.append(placeName, actions);
                const hiddenActivity = document.createElement('input');
                hiddenActivity.type = 'hidden';
                hiddenActivity.name = `itinerary[${index}][activities][]`;
                hiddenActivity.value = `${activity.type}:${activityId}`;
                placeRow.append(hiddenActivity);
                if (isPlace) {
                    const hiddenPlaceId = document.createElement('input');
                    hiddenPlaceId.type = 'hidden';
                    hiddenPlaceId.name = `itinerary[${index}][place_ids][]`;
                    hiddenPlaceId.value = placeId;
                    placeRow.append(hiddenPlaceId);
                } else {
                    const hiddenInclusionId = document.createElement('input');
                    hiddenInclusionId.type = 'hidden';
                    hiddenInclusionId.name = `itinerary[${index}][inclusions][]`;
                    hiddenInclusionId.value = activityId;
                    placeRow.append(hiddenInclusionId);
                }
                activityList.append(placeRow);
            });
            card.append(activityList);

            const inclusionPicker = document.createElement('div');
            inclusionPicker.className = 'mt-5 border-t border-slate-200 pt-4';
            const inclusionControls = document.createElement('div');
            inclusionControls.className = 'flex flex-wrap items-end gap-2';
            const inclusionSelectId = `itinerary-inclusion-${index}`;
            const inclusionSelect = document.createElement('select');
            inclusionSelect.id = inclusionSelectId;
            inclusionSelect.className = 'min-w-56 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm';
            const scheduledInclusionIds = new Set((day.inclusions ?? []).map(String));
            const inclusionPlaceholder = document.createElement('option');
            inclusionPlaceholder.value = '';
            inclusionPlaceholder.textContent = savedPackageInclusions.length ? 'Choose a service stop...' : 'Add services below first';
            inclusionSelect.append(inclusionPlaceholder);
            savedPackageInclusions.forEach((inclusion, inclusionIndex) => {
                if (!inclusion.name?.trim() || scheduledInclusionIds.has(String(inclusionIndex))) {
                    return;
                }
                const option = document.createElement('option');
                option.value = String(inclusionIndex);
                option.textContent = `${inclusion.name} · ${inclusion.duration_hours ? `${inclusion.duration_hours} hrs` : 'duration unstated'}`;
                inclusionSelect.append(option);
            });
            const inclusionField = document.createElement('div');
            inclusionField.className = 'min-w-56 flex-1';
            inclusionField.append(makeLabel('Add a service stop', inclusionSelectId), inclusionSelect);
            const addInclusionStop = document.createElement('button');
            addInclusionStop.type = 'button';
            addInclusionStop.className = 'rounded-xl bg-emerald-600 px-3.5 py-2.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-50';
            addInclusionStop.textContent = 'Add service stop';
            addInclusionStop.disabled = false;
            addInclusionStop.addEventListener('click', () => {
                const inclusionIndex = inclusionSelect.value;
                if (inclusionIndex === '' || (itineraryDays[index].inclusions ?? []).map(String).includes(inclusionIndex)) {
                    return;
                }
                syncItineraryInputs();
                itineraryDays[index].inclusions.push(inclusionIndex);
                itineraryDays[index].activities.push({ type: 'service', id: inclusionIndex });
                renderItinerary();
            });
            inclusionControls.append(inclusionField, addInclusionStop);
            inclusionPicker.append(inclusionControls);
            card.append(inclusionPicker);
            itineraryContainer.append(card);
        });
        updateItineraryDurations();
        updateEstimatedPrice();
    }

    function updateItineraryDurations() {
        const days = Array.from(itineraryContainer.querySelectorAll('[data-itinerary-day]'));
        let totalMinutes = 0;
        const dayTotals = days.map((card, index) => {
            const activities = Array.from(card.querySelectorAll('[data-itinerary-activity]'));
            const minutes = activities.reduce((sum, activity) => {
                const duration = activity.dataset.activityType === 'place'
                    ? availablePlaces.find((place) => String(place.id) === activity.dataset.activityId)?.duration_hours
                    : savedPackageInclusions[Number(activity.dataset.activityId)]?.duration_hours;
                return sum + Math.round((Number(duration) || 0) * 60);
            }, 0);
            const placesCount = activities.filter((activity) => activity.dataset.activityType === 'place').length;
            totalMinutes += minutes;
            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;
            const durationText = minutes ? `${hours ? `${hours} hr${hours === 1 ? '' : 's'} ` : ''}${remainingMinutes ? `${remainingMinutes} min` : ''}`.trim() : 'Duration unstated';
            const startTime = card.querySelector(`[name="itinerary[${index}][start_time]"]`)?.value;
            let finishText = '';
            if (startTime && minutes) {
                const [startHour, startMinute] = startTime.split(':').map(Number);
                const finish = (startHour * 60) + startMinute + minutes;
                finishText = ` · approx. finish ${String(Math.floor(finish / 60) % 24).padStart(2, '0')}:${String(finish % 60).padStart(2, '0')}${finish >= 1440 ? ' next day' : ''}`;
            }
            const summary = card.querySelector('[data-day-summary]');
            if (summary) {
                summary.textContent = `${activities.length} activities · ${durationText} of 24 hrs${finishText}`;
            }
            const capacityBar = card.querySelector('[data-day-capacity]');
            if (capacityBar) {
                capacityBar.style.width = `${Math.min(100, minutes / 1440 * 100)}%`;
                capacityBar.className = `h-full rounded-full transition-all ${minutes >= 1440 ? 'bg-amber-500' : minutes >= 1260 ? 'bg-amber-400' : 'bg-blue-500'}`;
            }
            return { day: index + 1, minutes, activities: activities.length, places: placesCount };
        });
        const totalHours = Math.floor(totalMinutes / 60);
        const remainingMinutes = totalMinutes % 60;
        document.getElementById('itinerary-duration-badge').textContent = `${totalMinutes ? `${totalHours}h ${remainingMinutes}m` : '0h'} total visit time`;
        const summaryContainer = document.getElementById('day-hours-summary');
        summaryContainer.replaceChildren();
        dayTotals.forEach((day) => {
            const hours = Math.floor(day.minutes / 60);
            const minutes = day.minutes % 60;
            const summary = document.createElement('div');
            summary.className = `rounded-xl border px-3 py-2 text-xs ${day.minutes > 1440 ? 'border-rose-200 bg-rose-50 text-rose-900' : 'border-blue-100 bg-blue-50 text-blue-900'}`;
            summary.textContent = `Day ${day.day}: ${day.minutes ? `${hours}h ${minutes}m` : '0h'} / 24h · ${day.activities} activities`;
            summaryContainer.append(summary);
        });
    }

    function updateEstimatedPrice() {
        const adults = Math.max(1, Number(adultsInput.value) || 1);
        const children = Math.max(0, Number(childrenInput.value) || 0);
        let estimateTotal = 0;
        let selectedPlaceCount = 0;
        let stateDayCount = 0;
        const selectedStateIds = new Set(Array.from(stateOptions.querySelectorAll('input:checked')).map((input) => input.value));
        const selectedStateRates = destinationCountries
            .flatMap((country) => country.states)
            .filter((state) => selectedStateIds.has(String(state.id)));
        const selectedStateRateById = new Map(selectedStateRates.map((state) => [String(state.id), Number(state.daily_price) || 0]));
        Array.from(itineraryContainer.querySelectorAll('[data-itinerary-day]')).forEach((card) => {
            const dayPlaces = Array.from(card.querySelectorAll('input[name$="[place_ids][]"]'))
                .map((input) => availablePlaces.find((place) => String(place.id) === String(input.value)))
                .filter(Boolean);
            selectedPlaceCount += dayPlaces.length;
            estimateTotal += dayPlaces.reduce((total, place) => total + (Number(place.adult_price) * adults) + (Number(place.child_price) * children), 0);
            const statePrices = new Map(dayPlaces.map((place) => [String(place.state_id), selectedStateRateById.get(String(place.state_id)) ?? (Number(place.state_daily_price) || 0)]));
            selectedStateRateById.forEach((price, stateId) => statePrices.set(stateId, price));
            stateDayCount += statePrices.size;
            estimateTotal += Array.from(statePrices.values()).reduce((total, price) => total + price, 0) * (adults + children);
        });
        estimateOutput.textContent = `₹${estimateTotal.toLocaleString('en-IN', { maximumFractionDigits: 2 })}`;
        estimateSummary.textContent = `${adults} adult${adults === 1 ? '' : 's'} · ${children} ${children === 1 ? 'child' : 'children'} · ${selectedPlaceCount} place entr${selectedPlaceCount === 1 ? 'y' : 'ies'} · state daily charges for ${stateDayCount} state-day${stateDayCount === 1 ? '' : 's'}`;
    }

    function renderInclusions() {
        inclusionContainer.replaceChildren();
    savedInclusionContainer.replaceChildren();
        const uniqueSavedInclusions = new Map();
        savedPackageInclusions
            .filter((inclusion) => inclusion.name?.trim())
            .forEach((inclusion) => uniqueSavedInclusions.set(inclusion.name.trim().toLowerCase(), inclusion));
        savedPackageInclusions = Array.from(uniqueSavedInclusions.values());
        savedInclusionFields.replaceChildren();
        savedPackageInclusions.forEach((inclusion, index) => {
            ['name', 'description', 'duration_hours'].forEach((field) => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `inclusions[${index}][${field}]`;
                hiddenInput.value = inclusion[field] ?? '';
                savedInclusionFields.append(hiddenInput);
            });
        });
        const savedNames = new Set(savedPackageInclusions.map((inclusion) => inclusion.name.trim().toLowerCase()));
        let hasRenderedEmptyForm = false;
        savedPackageInclusions.forEach((inclusion) => {
            const inclusionName = inclusion.name.trim().toLowerCase();
            const savedRow = document.createElement('div');
            savedRow.className = 'flex flex-wrap items-center justify-between gap-2 rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-2.5';
            const savedName = document.createElement('span');
            savedName.className = 'text-sm font-semibold text-emerald-900';
            savedName.textContent = `${inclusion.name}${inclusion.duration_hours ? ` · ${inclusion.duration_hours} hrs` : ''}`;
            const savedActions = document.createElement('div');
            savedActions.className = 'flex items-center gap-3';
            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'text-xs font-semibold text-blue-700 hover:underline';
            editButton.textContent = 'Edit';
            editButton.addEventListener('click', () => {
                savedPackageInclusions = savedPackageInclusions.filter((saved) => saved.name.trim().toLowerCase() !== inclusionName);
                packageInclusions = packageInclusions.filter((item) => item.name?.trim().toLowerCase() !== inclusionName);
                packageInclusions.push({ ...inclusion });
                renderInclusions();
                renderItinerary();
            });
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'text-xs font-semibold text-rose-700 hover:underline';
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', () => {
                syncItineraryInputs();
                packageInclusions = packageInclusions.filter((item) => item.name?.trim().toLowerCase() !== inclusionName);
                savedPackageInclusions = savedPackageInclusions.filter((saved) => saved.name.trim().toLowerCase() !== inclusionName);
                itineraryDays = itineraryDays.map((day) => ({ ...day, inclusions: [] }));
                renderInclusions();
                renderItinerary();
            });
            savedActions.append(editButton, removeButton);
            savedRow.append(savedName, savedActions);
            savedInclusionContainer.append(savedRow);
        });
        packageInclusions.forEach((inclusion, index) => {
            if (inclusion.name?.trim() && savedNames.has(inclusion.name.trim().toLowerCase())) {
                return;
            }
            if (!inclusion.name?.trim()) {
                if (hasRenderedEmptyForm) {
                    return;
                }
                hasRenderedEmptyForm = true;
            }
            const row = document.createElement('div');
            row.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)_auto]';

            const nameField = document.createElement('div');
            const nameId = `inclusion-name-${index}`;
            const name = document.createElement('input');
            name.id = nameId;
            name.name = `inclusions[${savedPackageInclusions.length + index}][name]`;
            name.value = inclusion.name ?? '';
            name.maxLength = 120;
            name.placeholder = 'e.g. Airport pickup';
            name.className = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm';
            name.addEventListener('input', () => {
                packageInclusions[index].name = name.value;
                syncItineraryInputs();
                renderItinerary();
            });
            nameField.append(makeLabel('Inclusion name', nameId), name);
            row.append(nameField);

            const descriptionField = document.createElement('div');
            const descriptionId = `inclusion-description-${index}`;
            const description = document.createElement('textarea');
            description.id = descriptionId;
            description.name = `inclusions[${savedPackageInclusions.length + index}][description]`;
            description.value = inclusion.description ?? '';
            description.maxLength = 1000;
            description.rows = 2;
            description.placeholder = 'Details or preferences (optional)';
            description.className = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm';
            description.addEventListener('input', () => {
                packageInclusions[index].description = description.value;
                syncItineraryInputs();
                renderItinerary();
            });
            descriptionField.append(makeLabel('Description (optional)', descriptionId), description);
            row.append(descriptionField);

            const durationField = document.createElement('div');
            const durationId = `inclusion-duration-${index}`;
            const duration = document.createElement('input');
            duration.id = durationId;
            duration.type = 'number';
            duration.name = `inclusions[${savedPackageInclusions.length + index}][duration_hours]`;
            duration.value = inclusion.duration_hours ?? '';
            duration.min = '0.25';
            duration.max = '72';
            duration.step = '0.25';
            duration.placeholder = 'Hours';
            duration.className = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm';
            duration.addEventListener('input', () => {
                packageInclusions[index].duration_hours = duration.value;
                syncItineraryInputs();
                renderItinerary();
            });
            durationField.append(makeLabel('Duration (hours, optional)', durationId), duration);
            row.append(durationField);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'self-end rounded-lg px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => {
                syncItineraryInputs();
                packageInclusions.splice(index, 1);
                renderInclusions();
                renderItinerary();
            });
            row.append(remove);
            inclusionContainer.append(row);
        });
    }

    daysInput.addEventListener('input', () => {
        syncItineraryInputs();
        renderItinerary();
    });
    adultsInput.addEventListener('input', updateEstimatedPrice);
    childrenInput.addEventListener('input', updateEstimatedPrice);
    document.getElementById('add-inclusion').addEventListener('click', () => {
        const hasEmptyInclusion = packageInclusions.some((inclusion) => !inclusion.name?.trim());
        if (hasEmptyInclusion) {
            inclusionContainer.querySelector('input[name$="[name]"]')?.focus();
        } else if (packageInclusions.length < 20) {
            packageInclusions.push({ name: '', description: '', duration_hours: '' });
            renderInclusions();
        }
    });
    document.getElementById('save-inclusions').addEventListener('click', () => {
        const savedCount = savedPackageInclusions.length;
        const formInclusions = Array.from(inclusionContainer.querySelectorAll('[name^="inclusions["]')).reduce((inclusions, input) => {
            const match = input.name.match(/^inclusions\[(\d+)\]\[(name|description|duration_hours)\]$/);
            if (!match) {
                return inclusions;
            }

            const index = Number(match[1]) - savedCount;
            if (index < 0) {
                return inclusions;
            }
            inclusions[index] ??= { name: '', description: '', duration_hours: '' };
            inclusions[index][match[2]] = input.value;

            return inclusions;
        }, []).filter(Boolean);
        const completedInclusions = formInclusions.filter((inclusion) => inclusion.name?.trim());
        if (completedInclusions.length === 0) {
            inclusionsSavedMessage.textContent = 'Add a service name and duration before adding it to this day.';
            inclusionsSavedMessage.className = 'px-5 pb-4 text-sm font-semibold text-amber-700 sm:px-7';
            return;
        }

        syncItineraryInputs();
        const savedInclusionsByName = new Map(savedPackageInclusions.map((inclusion) => [inclusion.name.trim().toLowerCase(), inclusion]));
        completedInclusions
            .filter((inclusion) => inclusion.name?.trim())
            .forEach((inclusion) => savedInclusionsByName.set(inclusion.name.trim().toLowerCase(), { ...inclusion, name: inclusion.name.trim() }));
        savedPackageInclusions = Array.from(savedInclusionsByName.values());
        const newlySavedNames = completedInclusions.map((inclusion) => inclusion.name.trim());
        const activeDay = itineraryDays[activeItineraryDay];
        const activeDayInclusions = new Set((activeDay.inclusions ?? []).map(String));
        newlySavedNames.forEach((name) => {
            const inclusionIndex = savedPackageInclusions.findIndex((inclusion) => inclusion.name.trim().toLowerCase() === name.toLowerCase());
            if (inclusionIndex >= 0 && !activeDayInclusions.has(String(inclusionIndex))) {
                activeDay.inclusions.push(String(inclusionIndex));
                activeDay.activities.push({ type: 'service', id: String(inclusionIndex) });
            }
        });
        inclusionsSavedMessage.textContent = `${newlySavedNames.join(', ')} added to Day ${activeItineraryDay + 1}.`;
        inclusionsSavedMessage.className = 'px-5 pb-4 text-sm font-semibold text-emerald-700 sm:px-7';
        packageInclusions = [{ name: '', description: '', duration_hours: '' }];
        renderInclusions();
        renderItinerary();
    });
    updateLocationChoices();
    renderInclusions();
</script>
@endpush
