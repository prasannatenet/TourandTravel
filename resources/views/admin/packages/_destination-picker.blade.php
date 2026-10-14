@php
    $exists = [
        'country' => [],
        'state' => [],
        'city' => [],
        'place' => [],
    ];
    if (isset($package)) {
        foreach ($package->destinations as $destination) {
            $type = strtolower(class_basename($destination->destination_type));
            if (array_key_exists($type, $exists)) {
                $exists[$type][] = $destination->destination_id;
            }
        }
    }

    $savedDestinations = [
        'country' => old('destinations.country', $exists['country'] ?? []),
        'state' => old('destinations.state', $exists['state'] ?? []),
        'city' => old('destinations.city', $exists['city'] ?? []),
        'place' => old('destinations.place', $exists['place'] ?? []),
    ];
@endphp

<div class="border border-slate-200 rounded-xl p-4 sm:p-5 bg-white" id="package-destination-picker">
    <h4 class="text-sm font-semibold text-slate-700 mb-1">Destinations</h4>
    <p class="text-xs text-slate-500 mb-4">Select one or more countries, then their states, then cities, then places. Each level only shows items belonging to the level above.</p>

    <div class="border-t border-slate-100 pt-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Countries (multi-select)</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach($countries as $country)
                <label class="flex items-center gap-2.5 text-sm text-slate-600 cursor-pointer hover:bg-slate-50 rounded-lg px-2 py-1.5">
                    <input type="checkbox" name="destinations[country][]" value="{{ $country->id }}" {{ in_array($country->id, $savedDestinations['country']) ? 'checked' : '' }} class="package-country rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="truncate">{{ $country->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="border-t border-slate-100 pt-4 mt-4">
        <label for="package-states" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">States (from selected countries, multi-select)</label>
        <select id="package-states" name="destinations[state][]" multiple size="6" data-destination-level="state" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" disabled>
            @foreach($states as $state)
                <option value="{{ $state->id }}" data-country-id="{{ $state->country_id }}" {{ in_array($state->id, $savedDestinations['state']) ? 'selected' : '' }}>{{ $state->name }} ({{ $state->country->name ?? '' }})</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-400 mt-1">Tick a country above first — only its states appear here.</p>
    </div>

    <div class="border-t border-slate-100 pt-4 mt-4">
        <label for="package-cities" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Cities (from selected states, multi-select)</label>
        <select id="package-cities" name="destinations[city][]" multiple size="6" data-destination-level="city" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" disabled>
            @foreach($cities as $city)
                <option value="{{ $city->id }}" data-state-id="{{ $city->state_id }}" {{ in_array($city->id, $savedDestinations['city']) ? 'selected' : '' }}>{{ $city->name }} ({{ $city->state->name ?? '' }})</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-400 mt-1">Select one or more states above — only their cities appear here.</p>
    </div>

    <div class="border-t border-slate-100 pt-4 mt-4">
        <label for="package-places" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Places (from selected cities, multi-select)</label>
        <select id="package-places" name="destinations[place][]" multiple size="6" data-destination-level="place" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" disabled>
            @foreach($places as $place)
                <option value="{{ $place->id }}" data-city-id="{{ $place->city_id }}" {{ in_array($place->id, $savedDestinations['place']) ? 'selected' : '' }}>{{ $place->name }} ({{ $place->city->name ?? '' }})</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-400 mt-1">Select one or more cities above — only their places appear here.</p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var picker = document.getElementById('package-destination-picker');
    if (!picker) {
        return;
    }

    var countryBoxes = Array.from(picker.querySelectorAll('.package-country'));
    var stateSelect = picker.querySelector('[data-destination-level="state"]');
    var citySelect = picker.querySelector('[data-destination-level="city"]');
    var placeSelect = picker.querySelector('[data-destination-level="place"]');

    function selectedValues(select) {
        return Array.from(select.selectedOptions).map(function (option) {
            return option.value;
        });
    }

    function filterOptions(select, allowedValues, datasetKey) {
        Array.from(select.options).forEach(function (option) {
            var isAllowed = allowedValues.has(option.dataset[datasetKey] || '');
            option.hidden = !isAllowed;
            option.disabled = !isAllowed;
            if (!isAllowed) {
                option.selected = false;
            }
        });
        select.disabled = allowedValues.size === 0;
    }

    function refreshPlaces() {
        var selectedCities = new Set(selectedValues(citySelect));
        filterOptions(placeSelect, selectedCities, 'cityId');
    }

    function refreshCities() {
        var selectedStates = new Set(selectedValues(stateSelect));
        filterOptions(citySelect, selectedStates, 'stateId');
        refreshPlaces();
    }

    function refreshStates() {
        var selectedCountries = new Set(countryBoxes.filter(function (box) {
            return box.checked;
        }).map(function (box) {
            return box.value;
        }));
        filterOptions(stateSelect, selectedCountries, 'countryId');
        refreshCities();
    }

    countryBoxes.forEach(function (box) {
        box.addEventListener('change', refreshStates);
    });
    stateSelect.addEventListener('change', refreshCities);
    citySelect.addEventListener('change', refreshPlaces);

    refreshStates();
})();
</script>
@endpush
