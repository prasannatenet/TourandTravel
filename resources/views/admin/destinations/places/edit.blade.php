@extends('layouts.admin')
@section('title', 'Edit Place')
@section('header', 'Edit: ' . $place->name)
@section('content')
<div class="max-w-3xl bg-white rounded-2xl shadow-sm p-6">
<form method="POST" action="{{ route('admin.places.update', $place) }}" enctype="multipart/form-data" class="space-y-4">
@csrf @method('PUT')
@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3"><ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div>
<label class="block text-sm font-semibold mb-1">Country *</label>
<select id="countrySelect" name="country_id" data-states-url="{{ route('admin.places.states-by-country', ['country' => '__COUNTRY__']) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
@foreach ($countries as $c)<option value="{{ $c->id }}" {{ old('country_id', $place->city->state->country_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
</select>
</div>
<div>
<label class="block text-sm font-semibold mb-1">State *</label>
<select id="stateSelect" name="state_id" data-cities-url="{{ route('admin.places.cities-by-state', ['state' => '__STATE__']) }}" data-selected="{{ old('state_id', $place->city->state_id) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
@foreach ($states as $s)<option value="{{ $s->id }}" {{ old('state_id', $place->city->state_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
</select>
</div>
<div>
<label class="block text-sm font-semibold mb-1">City *</label>
<select id="citySelect" name="city_id" data-selected="{{ old('city_id', $place->city_id) }}" required class="w-full border rounded-lg px-3 py-2 text-sm">
@foreach ($cities as $c)<option value="{{ $c->id }}" {{ old('city_id', $place->city_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
</select>
</div>
</div>
<div><label class="block text-sm font-semibold mb-1">Place Name *</label><input name="name" value="{{ old('name', $place->name) }}" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Place Description *</label><textarea name="place_description" required maxlength="500" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('place_description', $place->place_description) }}</textarea></div>
<div><label class="block text-sm font-semibold mb-1">Description *</label><textarea name="description" required rows="4" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('description', $place->description) }}</textarea></div>
<div><label class="block text-sm font-semibold mb-1">Primary Img (blank = keep)</label><div class="flex items-center gap-3"><img src="{{ $place->primaryImageUrl() }}" class="h-20 w-28 rounded-lg object-cover"><input type="file" name="primary_image" accept="image/*" class="text-sm"></div></div>
<div><label class="block text-sm font-semibold mb-1">Gallery (tick remove / add more)</label>
<div class="flex gap-2 flex-wrap mb-2">@foreach (($place->secondary_images ?? []) as $img)<label class="relative block"><img src="{{ asset('storage/'.$img) }}" class="h-16 w-24 rounded-lg object-cover"><span class="absolute top-1 left-1 bg-white rounded px-1 text-xs"><input type="checkbox" name="remove_secondary[]" value="{{ $img }}"> remove</span></label>@endforeach</div>
<input type="file" name="secondary_images[]" accept="image/*" multiple class="w-full text-sm"></div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div><label class="block text-sm font-semibold mb-1">Visit Duration (hours) *</label><input type="number" name="visit_duration_hours" value="{{ old('visit_duration_hours', $place->visit_duration_hours) }}" required min="0.5" max="72" step="0.5" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Best Time *</label>
<select name="best_time_to_visit" required class="w-full border rounded-lg px-3 py-2 text-sm">@foreach (['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening', 'night' => 'Night'] as $v => $l)<option value="{{ $v }}" {{ old('best_time_to_visit', $place->best_time_to_visit) == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
<div><label class="block text-sm font-semibold mb-1">Price per Person (Rs.) *</label><input type="number" name="price_per_person" value="{{ old('price_per_person', $place->price_per_person) }}" required min="0" max="999999.99" step="1.00" placeholder="0.00" onfocus="this.select()" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Price per Person (Indians) (Rs.)</label><input type="number" name="price_indian" value="{{ old('price_indian', $place->price_indian) }}" min="0" max="999999.99" step="1.00" placeholder="0.00" onfocus="this.select()" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Price per Person (Foreigners) (Rs.)</label><input type="number" name="price_foreigner" value="{{ old('price_foreigner', $place->price_foreigner) }}" min="0" max="999999.99" step="1.00" placeholder="0.00" onfocus="this.select()" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Price per Person (Student) (Rs.)</label><input type="number" name="price_student" value="{{ old('price_student', $place->price_student) }}" min="0" max="999999.99" step="1.00" placeholder="0.00" onfocus="this.select()" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
</div>
<div><label class="block text-sm font-semibold mb-1">Note</label><textarea name="note" maxlength="1000" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('note', $place->note) }}</textarea></div>
<div class="flex gap-2"><button class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg">Update</button><a href="{{ route('admin.places.index') }}" class="px-5 py-2 bg-slate-100 text-sm rounded-lg">Cancel</a></div>
</form>
</div>
@endsection
@push('scripts')
@include('admin.destinations.places._cascade-script')
@endpush

