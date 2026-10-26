@extends('layouts.admin')
@section('title', 'City Places')
@section('header', 'Destination - Places (Step 4)')
@section('subheader', 'All places with country, state and city.')
@section('content')
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
@endif
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
<p class="text-sm text-slate-500">{{ $cityPlaceCountsTotal }} city{{ $cityPlaceCountsTotal != 1 ? 's' : '' }} in destination</p>
</div>
<form method="GET" action="{{ route('admin.places.index') }}" id="placeFilterForm" class="bg-white rounded-2xl shadow-sm p-4 mb-4">
<div class="flex flex-wrap items-end gap-3">
<div class="flex-1 min-w-[180px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">Search place</label>
<input type="text" name="search" value="{{ $search }}" placeholder="Search by place name..." class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
</div>
<div class="min-w-[160px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">Country</label>
<select name="country_id" id="filterCountry" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
<option value="">All countries</option>
@foreach ($countries as $c)<option value="{{ $c->id }}" {{ (string) $countryId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
</select>
</div>
<div class="min-w-[160px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">State</label>
<select name="state_id" id="filterState" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
<option value="">All states</option>
@foreach ($states as $s)<option value="{{ $s->id }}" {{ (string) $stateId === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
</select>
</div>
<div class="min-w-[160px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">City</label>
<select name="city_id" id="filterCity" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
<option value="">All cities</option>
@foreach ($cities as $c)<option value="{{ $c->id }}" {{ (string) $cityId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
</select>
</div>
<div class="min-w-[150px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">Best Time</label>
<select name="best_time_to_visit" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
<option value="">All times</option>
@foreach (['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening', 'night' => 'Night'] as $v => $l)<option value="{{ $v }}" {{ $bestTime === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach
</select>
</div>
<div class="flex gap-2">
<button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
<a href="{{ route('admin.places.index') }}" class="px-4 py-2 bg-slate-100 text-sm rounded-lg">Reset</a>
</div>
</div>
@if ($hasFilters)
<p class="text-xs text-slate-500 mt-3">Showing filtered results ({{ $places->total() }} found). <a href="{{ route('admin.places.index') }}" class="text-blue-600 font-semibold">Clear all filters</a></p>
@endif
</form>
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-sm min-w-[1000px]">
<thead class="bg-slate-50 text-xs uppercase text-slate-500">
<tr>
<th class="text-left px-4 py-3">City</th>
<th class="text-left px-4 py-3">State</th>
<th class="text-left px-4 py-3">Country</th>
<th class="text-left px-4 py-3">Places in City</th>
<th class="text-left px-4 py-3">Actions</th>
</tr>
</thead>
<tbody class="divide-y">
@forelse ($groupedByCity as $cityId => $data)
<tr class="hover:bg-slate-50">
    <td class="px-4 py-2">{{ $data['city']->name }}</td>
    <td class="px-4 py-2">{{ $data['state']->name }}</td>
    <td class="px-4 py-2">{{ $data['country']->name }}</td>
    <td class="px-4 py-2 whitespace-nowrap"><span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full font-semibold">{{ $data['count'] }} place{{ $data['count'] != 1 ? 's' : '' }}</span></td>
    <td class="px-4 py-2">
        <div class="flex gap-1.5">
            <a href="{{ route('admin.places.city.index', $data['city']) }}" class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700">View All</a>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No places yet. <a class="text-blue-600 font-semibold" href="{{ route('admin.places.create') }}">Add one</a>.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
<div class="mt-4">{{ $places->links() }}</div>
@endsection
@push('scripts')
<script>
(function () {
    var countrySel = document.getElementById('filterCountry');
    var stateSel = document.getElementById('filterState');
    var citySel = document.getElementById('filterCity');
    if (!countrySel || !stateSel || !citySel) {
        return;
    }
    var statesBase = "{{ route('admin.places.states-by-country', ['country' => '__COUNTRY__']) }}";
    var citiesBase = "{{ route('admin.places.cities-by-state', ['state' => '__STATE__']) }}";

    function fillOptions(select, items, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        items.forEach(function (item) {
            var o = document.createElement('option');
            o.value = item.id;
            o.textContent = item.name;
            select.appendChild(o);
        });
    }

    countrySel.addEventListener('change', function () {
        stateSel.value = '';
        citySel.value = '';
        if (!countrySel.value) {
            return;
        }
        stateSel.disabled = true;
        fetch(statesBase.replace('__COUNTRY__', countrySel.value))
            .then(function (r) { return r.json(); })
            .then(function (items) {
                fillOptions(stateSel, items, 'All states');
                fillOptions(citySel, [], 'All cities');
                stateSel.disabled = false;
            });
    });

    stateSel.addEventListener('change', function () {
        citySel.value = '';
        if (!stateSel.value) {
            return;
        }
        citySel.disabled = true;
        fetch(citiesBase.replace('__STATE__', stateSel.value))
            .then(function (r) { return r.json(); })
            .then(function (items) {
                fillOptions(citySel, items, 'All cities');
                citySel.disabled = false;
            });
    });
})();
</script>
@endpush

