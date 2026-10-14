@extends('layouts.admin')
@section('title', 'Cities')
@section('header', 'Destination - Cities (Step 3)')
@section('subheader', 'Select a state from dropdown, then create city with images.')
@section('content')
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
@endif
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
<p class="text-sm text-slate-500">{{ $cities->total() }} cities</p>
<a href="{{ route('admin.cities.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">+ Add City</a>
</div>
<form method="GET" action="{{ route('admin.cities.index') }}" id="cityFilterForm" class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex flex-wrap items-end gap-3">
<div class="flex-1 min-w-[180px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">Search city</label>
<input type="text" name="search" value="{{ $search }}" placeholder="Search by city name..." class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
</div>
<div class="min-w-[180px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">Country</label>
<select name="country_id" id="filterCountry" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
<option value="">All countries</option>
@foreach ($countries as $c)<option value="{{ $c->id }}" {{ (string) $countryId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
</select>
</div>
<div class="min-w-[180px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">State</label>
<select name="state_id" id="filterState" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
<option value="">All states</option>
@foreach ($states as $s)<option value="{{ $s->id }}" {{ (string) $stateId === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
</select>
</div>
<div class="flex gap-2">
<button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
<a href="{{ route('admin.cities.index') }}" class="px-4 py-2 bg-slate-100 text-sm rounded-lg">Reset</a>
</div>
</form>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
@forelse ($cities as $city)
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
<img src="{{ $city->primaryImageUrl() }}" class="w-full h-44 object-cover">
<div class="p-4">
<p class="text-xs text-slate-400 uppercase">{{ $city->state->country->name }} / {{ $city->state->name }}</p>
<h3 class="font-bold text-lg">{{ $city->name }}</h3>
<p class="text-sm text-slate-500 mt-1">{{ $city->short_description }}</p>
<div class="flex gap-2 mt-3">
<a href="{{ route('admin.cities.edit', $city) }}" class="flex-1 text-center px-3 py-1.5 bg-slate-900 text-white text-sm rounded-lg">Edit</a>
<form method="POST" action="{{ route('admin.cities.destroy', $city) }}" class="flex-1" onsubmit="return confirm('Delete city?')">@csrf @method('DELETE')<button class="w-full px-3 py-1.5 bg-red-50 text-red-600 text-sm font-semibold rounded-lg">Delete</button></form>
</div>
</div>
</div>
@empty
<div class="col-span-full bg-white rounded-2xl p-10 text-center text-slate-500">No cities yet. Create country and state first.</div>
@endforelse
</div>
<div class="mt-5">{{ $cities->links() }}</div>
@endsection
@push('scripts')
<script>
(function () {
    var countrySel = document.getElementById('filterCountry');
    var stateSel = document.getElementById('filterState');
    if (!countrySel || !stateSel) {
        return;
    }
    var base = "{{ route('admin.places.states-by-country', ['country' => '__COUNTRY__']) }}";
    var keepState = stateSel.value;
    countrySel.addEventListener('change', function () {
        if (!countrySel.value) {
            return;
        }
        stateSel.disabled = true;
        fetch(base.replace('__COUNTRY__', countrySel.value))
            .then(function (r) { return r.json(); })
            .then(function (items) {
                stateSel.innerHTML = '<option value="">All states</option>';
                items.forEach(function (item) {
                    var o = document.createElement('option');
                    o.value = item.id;
                    o.textContent = item.name;
                    if (String(item.id) === String(keepState)) {
                        o.selected = true;
                    }
                    stateSel.appendChild(o);
                });
                stateSel.disabled = false;
            });
    });
})();
</script>
@endpush
