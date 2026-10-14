@extends('layouts.admin')
@section('title', 'Add State')
@section('header', 'Add State')
@section('subheader', 'Pick a country from dropdown created in Step 1.')
@section('content')
<div class="max-w-xl bg-white rounded-2xl shadow-sm p-6">
<form method="POST" action="{{ route('admin.states.store') }}" class="space-y-4">
@csrf
@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3"><ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if ($countries->isEmpty())
<div class="bg-amber-50 border border-amber-200 text-amber-700 text-sm rounded-lg px-4 py-3">No countries yet. <a class="font-semibold underline" href="{{ route('admin.countries.create') }}">Create a country first</a>.</div>
@endif
<div><label class="block text-sm font-semibold mb-1">Country * (dropdown of created countries)</label>
<select name="country_id" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">-- Select Country --</option>@foreach ($countries as $c)<option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
<div><label class="block text-sm font-semibold mb-1">State Name *</label><input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Goa" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div class="flex gap-2"><button class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg">Save State</button><a href="{{ route('admin.states.index') }}" class="px-5 py-2 bg-slate-100 text-sm rounded-lg">Cancel</a></div>
</form>
</div>
@endsection
