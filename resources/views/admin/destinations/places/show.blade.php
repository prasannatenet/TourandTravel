@extends('layouts.admin')
@section('title', $place->name)
@section('header', $place->name)
@section('subheader', $place->city->state->country->name . ' / ' . $place->city->state->name . ' / ' . $place->city->name)
@section('content')
<div class="flex gap-2 mb-4">
<a href="{{ route('admin.places.index') }}" class="px-4 py-2 bg-white shadow-sm text-sm font-semibold rounded-lg">← Back to Places</a>
<a href="{{ route('admin.places.edit', $place) }}" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg">Edit</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
<div class="lg:col-span-2 space-y-5">
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
<img src="{{ $place->primaryImageUrl() }}" class="w-full h-72 object-cover">
<div class="p-6">
<div class="flex flex-wrap gap-2 text-xs mb-3">
<span class="bg-slate-100 px-2.5 py-1 rounded-full">{{ $place->city->state->country->name }}</span>
<span class="bg-slate-100 px-2.5 py-1 rounded-full">{{ $place->city->state->name }}</span>
<span class="bg-slate-100 px-2.5 py-1 rounded-full">{{ $place->city->name }}</span>
<span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">{{ $place->visit_duration_hours }} hrs visit</span>
<span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full">Rs.{{ number_format($place->price_per_person) }} / person</span>
<span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full capitalize">Best: {{ $place->best_time_to_visit }}</span>
</div>
<h2 class="text-sm font-semibold text-slate-500 uppercase">Place Description (short)</h2>
<p class="mt-1">{{ $place->place_description }}</p>
<h2 class="text-sm font-semibold text-slate-500 uppercase mt-5">Description (full)</h2>
<p class="mt-1 whitespace-pre-line">{{ $place->description }}</p>
@if ($place->note)
<h2 class="text-sm font-semibold text-slate-500 uppercase mt-5">Note</h2>
<div class="mt-1 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg px-4 py-3">{{ $place->note }}</div>
@endif
</div>
</div>
@if (($place->secondary_images ?? []) !== [])
<div class="bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold mb-3">Gallery ({{ count($place->secondary_images) }})</h2>
<div class="grid grid-cols-2 gap-3">
@foreach ($place->secondary_images as $img)
<img src="{{ asset('storage/' . $img) }}" class="w-full h-32 rounded-xl object-cover">
@endforeach
</div>
</div>
@endif
</div>
<div class="lg:col-span-1 bg-white rounded-2xl shadow-sm p-6 h-fit">
<h2 class="font-bold mb-3">Complete Information</h2>
<dl class="text-sm space-y-2.5">
<div class="flex justify-between gap-2"><dt class="text-slate-500">Country</dt><dd class="font-semibold text-right">{{ $place->city->state->country->name }}</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">State</dt><dd class="font-semibold text-right">{{ $place->city->state->name }}</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">City</dt><dd class="font-semibold text-right">{{ $place->city->name }}</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">Place</dt><dd class="font-semibold text-right">{{ $place->name }}</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">Slug</dt><dd class="font-mono text-xs text-right">{{ $place->slug }}</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">Duration</dt><dd class="font-semibold text-right">{{ $place->visit_duration_hours }} hours</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">Best Time</dt><dd class="font-semibold capitalize text-right">{{ $place->best_time_to_visit }}</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">Price / Person</dt><dd class="font-semibold text-right">Rs.{{ number_format($place->price_per_person) }}</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">Gallery</dt><dd class="font-semibold text-right">{{ count($place->secondary_images ?? []) }} imgs</dd></div>
<div class="flex justify-between gap-2"><dt class="text-slate-500">Created</dt><dd class="text-right">{{ $place->created_at->format('d M Y') }}</dd></div>
</dl>
</div>
</div>
@endsection
