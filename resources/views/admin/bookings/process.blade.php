@extends('layouts.admin')

@php
    $customDestinationCountryIds = array_map('strval', old('custom_destinations.country', $customPackage['destinations']['country'] ?? []));
    $customDestinationStateIds = array_map('strval', old('custom_destinations.state', $customPackage['destinations']['state'] ?? []));
    $customDestinationCityIds = array_map('strval', old('custom_destinations.city', $customPackage['destinations']['city'] ?? []));
    $initialItems = old('itinerary');
    if (! is_array($initialItems)) {
        $initialItems = $itineraryItems->map(fn (array $item): array => [
            'kind' => $item['item_type'],
            'selection_id' => match ($item['item_type']) {
                'place' => $item['city_place_id'],
                'hotel' => $item['hotel_room_id'],
                default => $packageMode === 'custom'
                    ? ($item['package_inclusion_id'] ?? null)
                    : ($item['package_inclusion_item_id'] ?? null),
            },
            'day_number' => $item['day_number'],
            'start_time' => $item['start_time'],
            'nights' => $item['nights'] ?? 1,
            'additional_price' => $item['additional_price'] ?? 0,
        ])->all();
    }
    $initialItems = array_values(array_filter($initialItems, 'is_array'));

    $initialDays = old('days', $itineraryDays);
@endphp

@section('title', 'Process Booking')
@section('header', 'Process Booking #'.$booking->id)
@section('subheader', 'Keep the current package, switch to another package, or create a booking-only custom trip.')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 text-sm text-indigo-900">
        Changes on this page apply only to booking #{{ $booking->id }}. The booking will remain pending after saving.
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <dl class="grid gap-4 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-slate-500">Customer</dt>
                <dd class="font-semibold text-slate-900">{{ $booking->name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Package</dt>
                <dd class="font-semibold text-slate-900">{{ $packageMode === 'custom' ? ($customPackage['name'] ?? 'New custom package') : $selectedPackage->name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Current amount</dt>
                <dd class="font-semibold text-slate-900">Rs. {{ number_format((float) $booking->amount, 2) }}</dd>
            </div>
        </dl>
    </div>

    <form method="GET" action="{{ route('admin.bookings.process', $booking) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
            <div>
                <label for="package-mode" class="mb-1 block text-sm font-semibold text-slate-800">How should this booking change?</label>
                <select id="package-mode" name="package_mode" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="current" @selected($packageMode === 'current')>Make changes to this package</option>
                    <option value="existing" @selected($packageMode === 'existing')>Change to another available package</option>
                    <option value="custom" @selected($packageMode === 'custom')>Create a complete custom package</option>
                </select>
            </div>
            <div id="existing-package-selection" class="{{ $packageMode === 'existing' ? '' : 'hidden' }}">
                <label for="package-id" class="mb-1 block text-sm font-semibold text-slate-800">Available package</label>
                <select id="package-id" name="package_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    @foreach ($packages as $packageOption)
                        <option value="{{ $packageOption->id }}" @selected($selectedPackage->is($packageOption))>{{ $packageOption->name }} · Rs. {{ number_format((float) $packageOption->base_price, 2) }} per adult</option>
                    @endforeach
                </select>
            </div>
            <input id="package-id-fallback" type="hidden" name="package_id" value="{{ $booking->package_id }}">
            <button type="submit" class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Load Selection</button>
        </div>
        <p class="mt-3 text-xs leading-5 text-slate-500">Changes to this package are saved only on this booking. Choosing another package loads its saved plan. A custom package is built from scratch and is not added to the public package catalog.</p>
    </form>

    <form method="POST" action="{{ route('admin.bookings.process.update', $booking) }}" class="space-y-5" id="booking-itinerary-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">
        <input type="hidden" name="package_mode" id="booking-package-mode" value="{{ $packageMode }}">

        <section id="custom-package-fields" class="{{ $packageMode === 'custom' ? '' : 'hidden' }} space-y-5">
            <div class="rounded-2xl border border-violet-200 bg-violet-50/60 p-5 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-bold text-violet-950">Build a package for this customer</h2>
                    <p class="mt-1 text-sm text-violet-800">Complete the package details, pricing, destinations, and day plan. It will be stored only with this booking.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="custom-departure-date" class="mb-1 block text-sm font-semibold text-slate-800">Departure date <span class="text-rose-600">*</span></label>
                        <input id="custom-departure-date" name="custom_departure_date" type="date" value="{{ old('custom_departure_date', $customPackage['departure_date'] ?? $booking->check_in_date?->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2" @required($packageMode === 'custom')>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="custom-package-name" class="mb-1 block text-sm font-semibold text-slate-800">Package name <span class="text-rose-600">*</span></label>
                        <input id="custom-package-name" name="custom_package_name" type="text" maxlength="120" value="{{ old('custom_package_name', $customPackage['name'] ?? '') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2" @required($packageMode === 'custom')>
                    </div>
                    <div>
                        <label for="custom-departure-city" class="mb-1 block text-sm font-semibold text-slate-800">Departure city <span class="text-rose-600">*</span></label>
                        <input id="custom-departure-city" name="custom_departure_city" type="text" maxlength="100" value="{{ old('custom_departure_city', $customPackage['departure_city'] ?? '') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2" @required($packageMode === 'custom')>
                    </div>
                    <div>
                        <label for="custom-category" class="mb-1 block text-sm font-semibold text-slate-800">Category / theme</label>
                        <input id="custom-category" name="custom_category" type="text" maxlength="80" value="{{ old('custom_category', $customPackage['category'] ?? '') }}" placeholder="Family, Adventure, Honeymoon..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                    </div>
                    <div>
                        <label for="custom-tour-type" class="mb-1 block text-sm font-semibold text-slate-800">Trip style <span class="text-rose-600">*</span></label>
                        <select id="custom-tour-type" name="custom_tour_type" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2" @required($packageMode === 'custom')>
                            <option value="group" @selected(old('custom_tour_type', $customPackage['tour_type'] ?? 'group') === 'group')>Group tour</option>
                            <option value="private" @selected(old('custom_tour_type', $customPackage['tour_type'] ?? 'group') === 'private')>Private tour</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="custom-description" class="mb-1 block text-sm font-semibold text-slate-800">Package description</label>
                        <textarea id="custom-description" name="custom_description" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">{{ old('custom_description', $customPackage['description'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-bold text-slate-900">Pricing and duration</h2>
                <p class="mt-1 text-sm text-slate-500">Set the custom rates and package length for this booking.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['custom-base-price', 'custom_base_price', 'Adult price per person (Rs.)', $customPackage['base_price'] ?? '', true, '0', '99999999.99'],
                        ['custom-child-price', 'custom_child_price', 'Child price (Rs.)', $customPackage['child_price'] ?? '', false, '0', '999999.99'],
                        ['custom-infant-price', 'custom_infant_price', 'Infant price (Rs.)', $customPackage['infant_price'] ?? '', false, '0', '999999.99'],
                        ['custom-discount', 'custom_discount_percentage', 'Discount (%)', $customPackage['discount_percentage'] ?? 0, true, '0', '100'],
                        ['custom-tax', 'custom_tax_percentage', 'Tax (%)', $customPackage['tax_percentage'] ?? 0, true, '0', '100'],
                    ] as [$id, $name, $label, $value, $required, $minimum, $maximum])
                        <div>
                            <label for="{{ $id }}" class="mb-1 block text-sm font-semibold text-slate-800">{{ $label }} @if($required)<span class="text-rose-600">*</span>@endif</label>
                            <input id="{{ $id }}" name="{{ $name }}" type="number" min="{{ $minimum }}" max="{{ $maximum }}" step="0.01" value="{{ old($name, $value) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2" @required($packageMode === 'custom')>
                        </div>
                    @endforeach
                    <div class="flex items-end">
                        <p class="text-xs leading-5 text-slate-500">Custom child price is optional; if blank, the adult price is used.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-bold text-slate-900">Destinations</h2>
                <p class="mt-1 text-sm text-slate-500">Choose countries, then states and cities. Itinerary places and hotels must be in selected cities.</p>
                <div class="mt-4 space-y-5">
                    <fieldset>
                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Countries</legend>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($countries as $country)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" data-custom-country-option>
                                    <input type="checkbox" name="custom_destinations[country][]" value="{{ $country->id }}" @checked(in_array((string) $country->id, $customDestinationCountryIds, true)) class="custom-destination-country rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $country->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">States</legend>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($states as $state)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" data-custom-state-option data-country-id="{{ $state->country_id }}">
                                    <input type="checkbox" name="custom_destinations[state][]" value="{{ $state->id }}" @checked(in_array((string) $state->id, $customDestinationStateIds, true)) class="custom-destination-state rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $state->name }} <span class="text-xs text-slate-500">— {{ $state->country?->name }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Cities</legend>
                        <div class="grid max-h-72 gap-2 overflow-y-auto sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($cities as $city)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" data-custom-city-option data-state-id="{{ $city->state_id }}">
                            <input type="checkbox" name="custom_destinations[city][]" value="{{ $city->id }}" @checked(in_array((string) $city->id, $customDestinationCityIds, true)) class="custom-destination-city rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $city->name }} <span class="text-xs text-slate-500">— {{ $city->state?->name }}, {{ $city->state?->country?->name }}</span></span>
                        </label>
                    @endforeach
                        </div>
                    </fieldset>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/50 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Booking itinerary</h2>
                    <p class="mt-1 text-sm text-slate-500">Add, remove, and schedule places, hotels, and package services for this booking only.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="decrease-itinerary-days" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white font-bold text-slate-700 hover:bg-slate-50" aria-label="Remove a day">−</button>
                    <label for="itinerary-days" class="text-sm font-semibold text-slate-700">Trip days</label>
                    <input id="itinerary-days" name="days" type="number" min="1" max="99" required value="{{ $initialDays }}" class="w-20 rounded-lg border border-slate-300 px-3 py-2 text-center font-semibold text-slate-900">
                    <button type="button" id="increase-itinerary-days" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white font-bold text-slate-700 hover:bg-slate-50" aria-label="Add a day">+</button>
                </div>
            </div>

            <div class="space-y-5 px-5 py-5 sm:px-7">
                <div class="rounded-xl border border-sky-100 bg-sky-50/70 p-3.5 text-sm text-sky-900">
                    @if ($packageMode === 'custom')
                        Choose places and hotel rooms from the selected destination cities. Reducing trip days moves activities from removed days onto the new last day.
                    @else
                        Places and hotel rooms are limited to the selected package’s destination cities. Reducing trip days moves activities from removed days onto the new last day.
                    @endif
                </div>
                <div id="itinerary-days-container" class="space-y-5"></div>
                <p id="itinerary-empty-catalog" class="hidden rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">
                    There are no places, hotel rooms, or included services available in this package’s destinations yet.
                </p>
            </div>
        </section>

        <div class="flex flex-wrap justify-between gap-3">
            <a href="{{ route('admin.bookings.show', $booking) }}" class="rounded-lg bg-slate-100 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-200">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 font-semibold text-white hover:bg-indigo-700">{{ $packageMode === 'custom' ? 'Save Custom Package' : 'Save Booking Changes' }}</button>
        </div>
    </form>
</div>

<script>
    (() => {
        const places = @js($places->map(fn ($place) => [
            'id' => $place->id,
            'label' => $place->name.' — '.$place->city?->name,
        ])->values());
        const hotels = @js($hotelRooms->map(fn ($hotelRoom) => [
            'id' => $hotelRoom->id,
            'label' => ($hotelRoom->hotel?->name ?? 'Hotel').' — '.($hotelRoom->roomType?->name ?? $hotelRoom->name).' ('.($hotelRoom->hotel?->city?->name ?? '').') · Rs. '.number_format((float) $hotelRoom->customer_adult_price, 2).' per adult/night',
        ])->values());
        const services = @js($inclusionOptions);
        const initialItems = @js(array_values($initialItems));
        const dayInput = document.getElementById('itinerary-days');
        const daysContainer = document.getElementById('itinerary-days-container');
        const emptyCatalog = document.getElementById('itinerary-empty-catalog');
        const form = document.getElementById('booking-itinerary-form');
        const packageMode = @js($packageMode);
        const packageModeInput = document.getElementById('booking-package-mode');
        const packageChoiceMode = document.getElementById('package-mode');
        const existingPackageSelection = document.getElementById('existing-package-selection');
        const existingPackageId = document.getElementById('package-id');
        const fallbackPackageId = document.getElementById('package-id-fallback');
        const customPackageFields = document.getElementById('custom-package-fields');
        const customPackageName = document.getElementById('custom-package-name');
        const customBasePrice = document.getElementById('custom-base-price');
        const customCountryInputs = Array.from(document.querySelectorAll('.custom-destination-country'));
        const customStateOptions = Array.from(document.querySelectorAll('[data-custom-state-option]'));
        const customCityOptions = Array.from(document.querySelectorAll('[data-custom-city-option]'));
        const syncCustomDestinations = () => {
            const selectedCountryIds = new Set(customCountryInputs.filter((input) => input.checked).map((input) => input.value));
            customStateOptions.forEach((option) => {
                const input = option.querySelector('.custom-destination-state');
                const isAvailable = selectedCountryIds.has(option.dataset.countryId);
                option.classList.toggle('hidden', !isAvailable);
                input.disabled = !isAvailable;
                if (!isAvailable) {
                    input.checked = false;
                }
            });
            const selectedStateIds = new Set(customStateOptions
                .map((option) => option.querySelector('.custom-destination-state'))
                .filter((input) => input.checked && !input.disabled)
                .map((input) => input.value));
            customCityOptions.forEach((option) => {
                const input = option.querySelector('.custom-destination-city');
                const isAvailable = selectedStateIds.has(option.dataset.stateId);
                option.classList.toggle('hidden', !isAvailable);
                input.disabled = !isAvailable;
                if (!isAvailable) {
                    input.checked = false;
                }
            });
        };
        customCountryInputs.forEach((input) => input.addEventListener('change', syncCustomDestinations));
        customStateOptions.forEach((option) => {
            option.querySelector('.custom-destination-state').addEventListener('change', syncCustomDestinations);
        });
        syncCustomDestinations();
        const syncPackageChoice = () => {
            const isExistingPackage = packageChoiceMode.value === 'existing';
            existingPackageSelection.hidden = !isExistingPackage;
            existingPackageId.disabled = !isExistingPackage;
            fallbackPackageId.disabled = isExistingPackage;
        };
        packageChoiceMode.addEventListener('change', syncPackageChoice);
        syncPackageChoice();
        const optionsByKind = { place: places, hotel: hotels, inclusion: services };
        const dayCards = new Map();
        let currentDayCount = 0;

        const createElement = (tag, className, text = null) => {
            const element = document.createElement(tag);
            if (className) {
                element.className = className;
            }
            if (text !== null) {
                element.textContent = text;
            }
            return element;
        };

        const makeField = (labelText, control, className = '') => {
            const wrapper = createElement('label', `block text-sm font-semibold text-slate-700 ${className}`);
            wrapper.append(createElement('span', 'mb-1 block', labelText), control);
            return wrapper;
        };

        const createDayCard = (day) => {
            const card = createElement('section', 'rounded-2xl border border-slate-200 bg-slate-50/50 p-4 sm:p-5');
            card.dataset.dayCard = String(day);

            const header = createElement('div', 'mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3.5');
            const titleBlock = createElement('div', 'flex items-center gap-3');
            titleBlock.append(
                createElement('span', 'flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-xs font-black text-white', `D${day}`),
                createElement('div', 'text-sm font-black text-slate-900', `Day ${day} Itinerary`)
            );
            const actions = createElement('div', 'flex flex-wrap items-center gap-2');

            [
                ['place', 'Add Place', 'rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700'],
                ['hotel', 'Add Hotel', 'rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-50'],
                ['inclusion', 'Add Service', 'rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50'],
            ].forEach(([kind, label, classes]) => {
                const button = createElement('button', classes, label);
                button.type = 'button';
                button.disabled = optionsByKind[kind].length === 0;
                button.addEventListener('click', () => {
                    const defaultTime = kind === 'hotel' ? '15:00' : '09:00';
                    const startTime = kind === 'inclusion'
                        ? services.find((service) => String(service.id) === String(optionsByKind[kind][0].id))?.time || defaultTime
                        : defaultTime;
                    addItem(day, { kind, day_number: day, start_time: startTime, nights: 1 });
                });
                actions.append(button);
            });

            header.append(titleBlock, actions);
            const itemList = createElement('div', 'space-y-3');
            itemList.dataset.dayItems = String(day);
            const emptyMessage = createElement('p', 'rounded-xl border border-dashed border-slate-300 bg-white/70 px-4 py-6 text-center text-sm text-slate-500', `No activities scheduled for Day ${day}.`);
            card.append(header, itemList, emptyMessage);
            dayCards.set(day, { card, itemList, emptyMessage });
            daysContainer.append(card);
            refreshDay(day);
        };

        const selectionOptions = (kind, selectedId) => {
            const select = createElement('select', 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800');
            select.required = true;
            select.append(new Option('Choose an item...', ''));
            optionsByKind[kind].forEach((item) => {
                select.append(new Option(item.label, String(item.id)));
            });
            if (selectedId !== null && selectedId !== undefined) {
                select.value = String(selectedId);
            }
            return select;
        };

        const addItem = (day, item) => {
            const row = createElement('article', 'rounded-xl border border-slate-200 bg-white p-4');
            row.dataset.itineraryRow = '';
            row.dataset.kind = item.kind || 'place';

            const heading = createElement('div', 'mb-3 flex items-center justify-between gap-3');
            const rowTitle = createElement('p', 'text-sm font-bold text-slate-800');
            const removeButton = createElement('button', 'rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50', 'Remove');
            removeButton.type = 'button';
            removeButton.addEventListener('click', () => {
                row.remove();
                refreshDay(day);
                syncForm();
            });
            heading.append(rowTitle, removeButton);

            const grid = createElement('div', 'grid gap-3 sm:grid-cols-2 lg:grid-cols-4');
            const kindSelect = createElement('select', 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800');
            kindSelect.dataset.kindSelect = '';
            [['place', 'Place'], ['hotel', 'Hotel room'], ['inclusion', 'Package service']].forEach(([value, label]) => {
                kindSelect.append(new Option(label, value));
            });
            kindSelect.value = optionsByKind[item.kind] ? item.kind : 'place';
            const kindField = makeField('Activity type', kindSelect);

            const selectedId = item.selection_id;
            const selectField = makeField('Choose item', selectionOptions(kindSelect.value, selectedId), 'sm:col-span-2');
            selectField.querySelector('select').dataset.selectionSelect = '';

            const timeInput = createElement('input', 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800');
            timeInput.type = 'time';
            timeInput.required = true;
            timeInput.value = item.start_time || '09:00';
            const timeField = makeField('Start time', timeInput);

            const nightsInput = createElement('input', 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800');
            nightsInput.type = 'number';
            nightsInput.min = '1';
            nightsInput.max = '99';
            nightsInput.value = item.nights || 1;
            const nightsField = makeField('Hotel nights', nightsInput);

            const dayInputField = createElement('input');
            dayInputField.type = 'hidden';
            dayInputField.dataset.dayNumber = '';
            const additionalPriceInput = createElement('input');
            additionalPriceInput.type = 'hidden';
            additionalPriceInput.value = item.additional_price ?? 0;
            additionalPriceInput.dataset.additionalPrice = '';
            row.append(heading, grid, dayInputField, additionalPriceInput);
            grid.append(kindField, selectField, timeField, nightsField);

            const updateKind = (resetSelection) => {
                row.dataset.kind = kindSelect.value;
                const currentSelection = selectField.querySelector('select');
                const nextSelect = selectionOptions(kindSelect.value, resetSelection ? null : currentSelection.value);
                nextSelect.dataset.selectionSelect = '';
                currentSelection.replaceWith(nextSelect);
                nextSelect.addEventListener('change', syncForm);
                nightsField.hidden = kindSelect.value !== 'hotel';
                nightsInput.required = kindSelect.value === 'hotel';
                nightsInput.disabled = kindSelect.value !== 'hotel';
                rowTitle.textContent = kindSelect.options[kindSelect.selectedIndex].text;
                syncForm();
            };

            kindSelect.addEventListener('change', () => updateKind(true));
            timeInput.addEventListener('input', syncForm);
            nightsInput.addEventListener('input', syncForm);

            dayCards.get(day).itemList.append(row);
            updateKind(false);
            refreshDay(day);
            syncForm();
        };

        const refreshDay = (day) => {
            const card = dayCards.get(day);
            if (!card) {
                return;
            }
            card.emptyMessage.hidden = card.itemList.children.length > 0;
        };

        const syncForm = () => {
            let itemIndex = 0;
            dayCards.forEach(({ itemList }, day) => {
                Array.from(itemList.querySelectorAll('[data-itinerary-row]')).forEach((row) => {
                    row.dataset.day = String(day);
                    row.querySelector('[data-day-number]').name = `itinerary[${itemIndex}][day_number]`;
                    row.querySelector('[data-day-number]').value = String(day);
                    row.querySelector('[data-additional-price]').name = `itinerary[${itemIndex}][additional_price]`;
                    const kindSelect = row.querySelector('[data-kind-select]');
                    const kind = kindSelect.value;
                    kindSelect.name = `itinerary[${itemIndex}][kind]`;
                    const selection = row.querySelector('[data-selection-select]');
                    selection.name = `itinerary[${itemIndex}][selection_id]`;
                    const time = row.querySelector('input[type="time"]');
                    time.name = `itinerary[${itemIndex}][start_time]`;
                    const nights = row.querySelector('input[type="number"]');
                    nights.name = `itinerary[${itemIndex}][nights]`;
                    nights.disabled = kind !== 'hotel';
                    itemIndex++;
                });
                refreshDay(day);
            });
        };

        const setDayCount = (requestedCount) => {
            const nextCount = Math.max(1, Math.min(99, Number.parseInt(requestedCount, 10) || 1));
            if (nextCount < currentDayCount) {
                const target = dayCards.get(nextCount);
                for (let day = nextCount + 1; day <= currentDayCount; day++) {
                    const removedDay = dayCards.get(day);
                    Array.from(removedDay.itemList.querySelectorAll('[data-itinerary-row]')).forEach((row) => target.itemList.append(row));
                    removedDay.card.remove();
                    dayCards.delete(day);
                }
            } else {
                for (let day = currentDayCount + 1; day <= nextCount; day++) {
                    createDayCard(day);
                }
            }
            currentDayCount = nextCount;
            dayInput.value = String(nextCount);
            dayInput.max = '99';
            syncForm();
        };

        document.getElementById('decrease-itinerary-days').addEventListener('click', () => setDayCount(currentDayCount - 1));
        document.getElementById('increase-itinerary-days').addEventListener('click', () => setDayCount(currentDayCount + 1));
        dayInput.addEventListener('change', () => setDayCount(dayInput.value));
        packageModeInput.value = packageMode;
        customPackageName.required = packageMode === 'custom';
        customBasePrice.required = packageMode === 'custom';
        customPackageFields.hidden = packageMode !== 'custom';
        form.addEventListener('submit', syncForm);

        emptyCatalog.hidden = places.length + hotels.length + services.length > 0;
        const requestedDays = Number.parseInt(dayInput.value, 10) || 1;
        const initialDayCount = Math.max(requestedDays, ...initialItems.map((item) => Number.parseInt(item.day_number, 10) || 1));
        for (let day = 1; day <= Math.min(99, initialDayCount); day++) {
            createDayCard(day);
        }
        currentDayCount = Math.min(99, initialDayCount);
        initialItems.forEach((item) => {
            const day = Math.max(1, Math.min(currentDayCount, Number.parseInt(item.day_number, 10) || 1));
            addItem(day, item);
        });
        syncForm();
    })();
</script>
@endsection
