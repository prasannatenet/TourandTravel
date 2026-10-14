@extends('layouts.admin')
@section('title', 'Add Place')
@section('header', 'Add City Place')
@section('subheader', 'Pick country, then state, then city.')
@section('content')
<div class="max-w-3xl bg-white rounded-2xl shadow-sm p-6">
<form method="POST" action="{{ route('admin.places.store') }}" enctype="multipart/form-data" class="space-y-4">
@csrf
@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3"><ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if ($countries->isEmpty())
<div class="bg-amber-50 border border-amber-200 text-amber-700 text-sm rounded-lg px-4 py-3">No countries yet. <a class="font-semibold underline" href="{{ route('admin.countries.create') }}">Create a country first</a>.</div>
@endif
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div>
<label class="block text-sm font-semibold mb-1">Country *</label>
<select id="countrySelect" data-states-url="{{ route('admin.places.states-by-country', ['country' => '__COUNTRY__']) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
<option value="">-- Select Country --</option>
@foreach ($countries as $c)<option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
</select>
</div>
<div>
<label class="block text-sm font-semibold mb-1">State * (states of selected country)</label>
<select id="stateSelect" data-cities-url="{{ route('admin.places.cities-by-state', ['state' => '__STATE__']) }}" data-selected="{{ old('state_id') }}" class="w-full border rounded-lg px-3 py-2 text-sm" disabled>
<option value="">-- Select State --</option>
</select>
</div>
<div>
<label class="block text-sm font-semibold mb-1">City * (cities of selected state)</label>
<select id="citySelect" name="city_id" data-selected="{{ old('city_id') }}" required class="w-full border rounded-lg px-3 py-2 text-sm" disabled>
<option value="">-- Select City --</option>
</select>
</div>
</div>
<input type="hidden" name="country_id" id="countryHidden" value="{{ old('country_id') }}">
<input type="hidden" name="state_id" id="stateHidden" value="{{ old('state_id') }}">
<div><label class="block text-sm font-semibold mb-1">Place Name *</label><input name="name" value="{{ old('name') }}" required maxlength="150" placeholder="Baga Beach" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Place Description * (short, max 500)</label><textarea name="place_description" required maxlength="500" rows="2" placeholder="Famous beach for nightlife..." class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('place_description') }}</textarea></div>
<div><label class="block text-sm font-semibold mb-1">Description * (full details)</label><textarea name="description" required rows="4" placeholder="History, activities, entry fee..." class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('description') }}</textarea></div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div><label class="block text-sm font-semibold mb-1">Primary Img * (single)</label><input type="file" name="primary_image" accept="image/*" required class="w-full text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Secondary Imgs (multi, max 8)</label><input type="file" name="secondary_images[]" accept="image/*" multiple class="w-full text-sm"></div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div><label class="block text-sm font-semibold mb-1">Visit Duration (hours) *</label><input type="number" name="visit_duration_hours" value="{{ old('visit_duration_hours', 2) }}" required min="0.5" max="72" step="0.5" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Best Time to Visit *</label>
<select name="best_time_to_visit" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">-- Select --</option>@foreach (['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening', 'night' => 'Night'] as $v => $l)<option value="{{ $v }}" {{ old('best_time_to_visit') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
</div>
<div><label class="block text-sm font-semibold mb-1">Note for the Place (optional)</label><textarea name="note" maxlength="1000" rows="2" placeholder="Carry sunscreen, avoid monsoon..." class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('note') }}</textarea></div>
<div class="flex gap-2"><button class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg">Save Place</button><a href="{{ route('admin.places.index') }}" class="px-5 py-2 bg-slate-100 text-sm rounded-lg">Cancel</a></div>
</form>
</div>
@endsection
@push('scripts')
@include('admin.destinations.places._cascade-script')
<script>
(function () {
    var countrySel = document.getElementById('countrySelect');
    var stateSel = document.getElementById('stateSelect');
    var citySel = document.getElementById('citySelect');
    var countryHidden = document.getElementById('countryHidden');
    var stateHidden = document.getElementById('stateHidden');
    function sync() {
        if (countryHidden) {
            countryHidden.value = countrySel ? countrySel.value : '';
        }
        if (stateHidden) {
            stateHidden.value = stateSel ? stateSel.value : '';
        }
    }
    if (countrySel) {
        countrySel.addEventListener('change', sync);
    }
    if (stateSel) {
        stateSel.addEventListener('change', sync);
    }
    if (citySel) {
        citySel.addEventListener('change', sync);
    }
    sync();
})();
</script>
@endpush

