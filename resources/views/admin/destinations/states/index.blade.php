@extends('layouts.admin')
@section('title', 'States')
@section('header', 'Destination - States (Step 2)')
@section('subheader', 'Select a created country from dropdown, then create state in it.')
@section('content')
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
@endif
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
<p class="text-sm text-slate-500">{{ $states->total() }} states</p>
<a href="{{ route('admin.states.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">+ Add State</a>
</div>
<form method="GET" action="{{ route('admin.states.index') }}" class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex flex-wrap items-end gap-3">
<div class="flex-1 min-w-[180px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">Search state</label>
<input type="text" name="search" value="{{ $search }}" placeholder="Search by state name..." class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
</div>
<div class="min-w-[180px]">
<label class="block text-xs font-semibold text-slate-500 mb-1">Country</label>
<select name="country_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
<option value="">All countries</option>
@foreach ($countries as $c)<option value="{{ $c->id }}" {{ (string) $countryId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
</select>
</div>
<div class="flex gap-2">
<button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg">Filter</button>
<a href="{{ route('admin.states.index') }}" class="px-4 py-2 bg-slate-100 text-sm rounded-lg">Reset</a>
</div>
</form>
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
<table class="w-full text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="text-left px-6 py-3">State</th><th class="text-left px-6 py-3">Country</th><th class="text-left px-6 py-3">Cities</th><th class="text-left px-6 py-3">Actions</th></tr></thead>
<tbody class="divide-y">
@forelse ($states as $s)
<tr class="hover:bg-slate-50"><td class="px-6 py-3 font-semibold">{{ $s->name }}</td><td class="px-6 py-3">{{ $s->country->name }}</td><td class="px-6 py-3">{{ $s->cities_count }}</td>
<td class="px-6 py-3 flex gap-2"><a href="{{ route('admin.states.edit', $s) }}" class="px-3 py-1 bg-slate-900 text-white text-xs rounded-lg">Edit</a>
<form method="POST" action="{{ route('admin.states.destroy', $s) }}" onsubmit="return confirm('Delete state + cities?')">@csrf @method('DELETE')<button class="px-3 py-1 bg-red-50 text-red-600 text-xs font-semibold rounded-lg">Delete</button></form></td></tr>
@empty
<tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">No states yet. Create a country first.</td></tr>
@endforelse
</tbody></table>
</div>
<div class="mt-4">{{ $states->links() }}</div>
@endsection
