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
    <p class="text-xs text-slate-500 mb-4">{{ ($showPlaces ?? true) ? 'Choose countries, states, cities, and places. Select multiple checkboxes at each level; each level shows options from your previous selection.' : 'Choose countries, states, and cities. Select multiple checkboxes at each level; each level shows options from your previous selection.' }}</p>

    <div class="border-t border-slate-100 pt-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Countries <span class="font-normal normal-case tracking-normal">(select one or more)</span></p>
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
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">States <span class="font-normal normal-case tracking-normal">(select one or more)</span></p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2" data-destination-level="state">
            @foreach($states as $state)
                <label class="flex items-center gap-2.5 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700 cursor-pointer transition hover:border-indigo-300 hover:bg-indigo-50/50" data-country-id="{{ $state->country_id }}">
                    <input type="checkbox" name="destinations[state][]" value="{{ $state->id }}" {{ in_array($state->id, $savedDestinations['state']) ? 'checked' : '' }} class="package-state rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="min-w-0 truncate">{{ $state->name }} <span class="text-xs text-slate-400">{{ $state->country->name ?? '' }}</span></span>
                </label>
            @endforeach
        </div>
        <p class="text-xs text-slate-400 mt-2">Select a country above to show its states.</p>
    </div>

    <div class="border-t border-slate-100 pt-4 mt-4">
        <p class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Cities <span class="font-normal normal-case tracking-normal">(select one or more)</span></p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2" data-destination-level="city">
            @foreach($cities as $city)
                <label class="flex items-center gap-2.5 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700 cursor-pointer transition hover:border-indigo-300 hover:bg-indigo-50/50" data-state-id="{{ $city->state_id }}">
                    <input type="checkbox" name="destinations[city][]" value="{{ $city->id }}" {{ in_array($city->id, $savedDestinations['city']) ? 'checked' : '' }} class="package-city rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="min-w-0 truncate">{{ $city->name }} <span class="text-xs text-slate-400">{{ $city->state->name ?? '' }}</span></span>
                </label>
            @endforeach
        </div>
        <p class="text-xs text-slate-400 mt-2">Select states above to show their cities.</p>
    </div>

    @if($showPlaces ?? true)
        <div class="border-t border-slate-100 pt-4 mt-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Places <span class="font-normal normal-case tracking-normal">(select one or more)</span></p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2" data-destination-level="place">
                @foreach($places as $place)
                    <label class="flex items-center gap-2.5 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700 cursor-pointer transition hover:border-indigo-300 hover:bg-indigo-50/50" data-city-id="{{ $place->city_id }}">
                        <input type="checkbox" name="destinations[place][]" value="{{ $place->id }}" {{ in_array($place->id, $savedDestinations['place']) ? 'checked' : '' }} class="package-place rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="min-w-0 truncate">{{ $place->name }} <span class="text-xs text-slate-400">{{ $place->city->name ?? '' }}</span></span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-slate-400 mt-2">Select cities above to show their places.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function () {
    var picker = document.getElementById('package-destination-picker');
    if (!picker) {
        return;
    }

    var countryBoxes = Array.from(picker.querySelectorAll('.package-country'));
    var stateBoxes = Array.from(picker.querySelectorAll('.package-state'));
    var cityBoxes = Array.from(picker.querySelectorAll('.package-city'));
    var placeBoxes = Array.from(picker.querySelectorAll('.package-place'));

    function selectedCheckboxValues(checkboxes) {
        return checkboxes.filter(function (checkbox) {
            return checkbox.checked;
        }).map(function (checkbox) {
            return checkbox.value;
        });
    }

    function filterCheckboxes(checkboxes, allowedValues, datasetKey) {
        checkboxes.forEach(function (checkbox) {
            var label = checkbox.closest('label');
            var isAllowed = allowedValues.has(label.dataset[datasetKey] || '');
            label.hidden = !isAllowed;
            checkbox.disabled = !isAllowed;
            if (!isAllowed) {
                checkbox.checked = false;
            }
        });
    }

    function refreshPlaces() {
        var selectedCities = new Set(selectedCheckboxValues(cityBoxes));
        filterCheckboxes(placeBoxes, selectedCities, 'cityId');
    }

    function refreshCities() {
        var selectedStates = new Set(selectedCheckboxValues(stateBoxes));
        filterCheckboxes(cityBoxes, selectedStates, 'stateId');
        refreshPlaces();
    }

    function refreshStates() {
        var selectedCountries = new Set(selectedCheckboxValues(countryBoxes));
        filterCheckboxes(stateBoxes, selectedCountries, 'countryId');
        refreshCities();
    }

    countryBoxes.forEach(function (box) {
        box.addEventListener('change', refreshStates);
    });
    stateBoxes.forEach(function (box) {
        box.addEventListener('change', refreshCities);
    });
    cityBoxes.forEach(function (box) {
        box.addEventListener('change', refreshPlaces);
    });

    refreshStates();
})();
</script>
@endpush
