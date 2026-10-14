@extends('layouts.admin')
@section('title', 'Edit City')
@section('header', 'Edit: ' . $city->name)
@section('content')
<div class="max-w-3xl bg-white rounded-2xl shadow-sm p-6">
<form method="POST" action="{{ route('admin.cities.update', $city) }}" enctype="multipart/form-data" class="space-y-4">
@csrf @method('PUT')
@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3"><ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div><label class="block text-sm font-semibold mb-1">State *</label>
<select name="state_id" required class="w-full border rounded-lg px-3 py-2 text-sm">@foreach ($states as $s)<option value="{{ $s->id }}" {{ old('state_id', $city->state_id) == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->country->name }})</option>@endforeach</select></div>
<div><label class="block text-sm font-semibold mb-1">City Name *</label><input name="name" value="{{ old('name', $city->name) }}" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
</div>
<div><label class="block text-sm font-semibold mb-1">Short Description *</label><textarea name="short_description" required maxlength="500" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('short_description', $city->short_description) }}</textarea></div>
<div><label class="block text-sm font-semibold mb-1">Primary Image (blank = keep)</label><div class="flex items-center gap-3"><img src="{{ $city->primaryImageUrl() }}" class="h-20 w-28 rounded-lg object-cover"><input type="file" name="primary_image" accept="image/*" class="text-sm"></div></div>
<div><label class="block text-sm font-semibold mb-1">Gallery (tick remove / add more)</label>
<div class="flex gap-2 flex-wrap mb-2">@foreach (($city->secondary_images ?? []) as $img)<label class="relative block"><img src="{{ asset('storage/'.$img) }}" class="h-16 w-24 rounded-lg object-cover"><span class="absolute top-1 left-1 bg-white rounded px-1 text-xs"><input type="checkbox" name="remove_secondary[]" value="{{ $img }}"> remove</span></label>@endforeach</div>
<input type="file" name="secondary_images[]" accept="image/*" multiple class="w-full text-sm"></div>
<div class="flex gap-2"><button class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg">Update</button><a href="{{ route('admin.cities.index') }}" class="px-5 py-2 bg-slate-100 text-sm rounded-lg">Cancel</a></div>
</form>
</div>
@endsection
