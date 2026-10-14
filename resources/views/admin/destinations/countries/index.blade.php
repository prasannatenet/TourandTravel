@extends('layouts.admin')
@section('title', 'Countries')
@section('header', 'Destination - Countries (Step 1)')
@section('subheader', 'Create country first, then add states under it.')
@section('content')
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
@endif
<div class="flex items-center justify-between mb-4">
<p class="text-sm text-slate-500">{{ $countries->total() }} countries</p>
<a href="{{ route('admin.countries.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">+ Add Country</a>
</div>
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
<table class="w-full text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="text-left px-6 py-3">Country</th><th class="text-left px-6 py-3">States</th><th class="text-left px-6 py-3">Actions</th></tr></thead>
<tbody class="divide-y">
@forelse ($countries as $c)
<tr class="hover:bg-slate-50"><td class="px-6 py-3 font-semibold">{{ $c->name }}</td><td class="px-6 py-3">{{ $c->states_count }}</td>
<td class="px-6 py-3 flex gap-2"><a href="{{ route('admin.countries.edit', $c) }}" class="px-3 py-1 bg-slate-900 text-white text-xs rounded-lg">Edit</a>
<form method="POST" action="{{ route('admin.countries.destroy', $c) }}" onsubmit="return confirm('Delete country + all states/cities?')">@csrf @method('DELETE')<button class="px-3 py-1 bg-red-50 text-red-600 text-xs font-semibold rounded-lg">Delete</button></form></td></tr>
@empty
<tr><td colspan="3" class="px-6 py-8 text-center text-slate-500">No countries yet.</td></tr>
@endforelse
</tbody></table>
</div>
<div class="mt-4">{{ $countries->links() }}</div>
@endsection
