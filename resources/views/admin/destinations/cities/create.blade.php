@extends('layouts.admin')
@section('title', 'Add City')
@section('header', 'Add City')
@section('subheader', 'City slug auto-generates and stays hidden.')
@section('content')
<div class="max-w-3xl bg-white rounded-2xl shadow-sm p-6">
<form method="POST" action="{{ route('admin.cities.store') }}" enctype="multipart/form-data" class="space-y-4">
@csrf
@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3"><ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if ($states->isEmpty())
<div class="bg-amber-50 border border-amber-200 text-amber-700 text-sm rounded-lg px-4 py-3">No states yet. <a class="font-semibold underline" href="{{ route('admin.states.create') }}">Create a state first</a>.</div>
@endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div><label class="block text-sm font-semibold mb-1">State * (dropdown of created states)</label>
<select name="state_id" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">-- Select State --</option>@foreach ($states as $s)<option value="{{ $s->id }}" {{ old('state_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->country->name }})</option>@endforeach</select></div>
<div><label class="block text-sm font-semibold mb-1">City Name *</label><input name="name" id="city" value="{{ old('name') }}" required maxlength="100" placeholder="Panaji" class="w-full border rounded-lg px-3 py-2 text-sm"><p class="text-xs text-slate-400 mt-1">Slug preview: <span id="slugPrev" class="font-mono">-</span> (auto hidden)</p></div>
</div>
<div><label class="block text-sm font-semibold mb-1">Short Description * (max 500)</label><textarea name="short_description" required maxlength="500" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('short_description') }}</textarea></div>
<div><label class="block text-sm font-semibold mb-1">Primary Image *</label><input type="file" name="primary_image" accept="image/*" required class="w-full text-sm"></div>
<div><label class="block text-sm font-semibold mb-1">Secondary Images (multi, max 8)</label><input type="file" name="secondary_images[]" accept="image/*" multiple class="w-full text-sm"></div>
<div class="flex gap-2"><button class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg">Save City</button><a href="{{ route('admin.cities.index') }}" class="px-5 py-2 bg-slate-100 text-sm rounded-lg">Cancel</a></div>
</form>
</div>
@endsection
@push('scripts')
<script>
const c = document.getElementById('city'); const sp = document.getElementById('slugPrev');
function sl(s){ return s.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,''); }
if (c) { c.addEventListener('input', () => sp.textContent = sl(c.value) || '-'); }
</script>
@endpush
