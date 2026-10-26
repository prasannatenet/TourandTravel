@extends('layouts.admin')
@section('title', $city->name.' - Places')
@section('header', $city->name)
@section('subheader', $city->state->country->name.' / '.$city->state->name)
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex gap-2">
        <a href="{{ route('admin.places.index') }}" class="px-4 py-2 bg-white shadow-sm text-sm font-semibold rounded-lg border border-slate-200 hover:bg-slate-50">← Back to Places</a>
        <a href="{{ route('admin.cities.edit', $city) }}" class="px-4 py-2 bg-white shadow-sm text-sm font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 text-blue-600">Edit {{ $city->name }}</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <h1 class="text-xl font-bold text-slate-800">{{ $city->name }}</h1>
            <p class="text-sm text-slate-500">{{ $city->state->name }}, {{ $city->state->country->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.places.create') }}?city_id={{ $city->id }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">+ Add Place</a>
            <a href="#" class="px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-lg hover:bg-emerald-100">{{ $places->total() }} place{{ $places->total() !== 1 ? 's' : '' }} in this city</a>
        </div>
    </div>
</div>

@if ($places->total() === 0)
<div class="bg-white rounded-2xl shadow-sm p-12 text-center">
    <p class="text-slate-500 text-sm">No places added to <span class="font-semibold text-slate-800">{{ $city->name }}</span> yet.</p>
    <a href="{{ route('admin.places.create') }}?city_id={{ $city->id }}" class="text-sm font-semibold text-blue-600 hover underline">Add your first place</a>
</div>
@else
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="text-left px-4 py-3">Image</th>
                <th class="text-left px-4 py-3">Place</th>
                <th class="text-left px-4 py-3">Duration</th>
                <th class="text-left px-4 py-3">Best Time</th>
                <th class="text-left px-4 py-3">Price / Person</th>
                <th class="text-left px-4 py-3">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($places as $p)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2"><img src="{{ $p->primaryImageUrl() }}" class="h-12 w-16 rounded-lg object-cover"></td>
                <td class="px-4 py-2 font-semibold">{{ $p->name }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $p->visit_duration_hours }} hrs</td>
                <td class="px-4 py-2 capitalize"><span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-1 rounded-full">{{ $p->best_time_to_visit }}</span></td>
                <td class="px-4 py-2 whitespace-nowrap">Rs.{{ number_format($p->price_per_person) }}</td>
                <td class="px-4 py-2">
                    <div class="flex gap-1.5">
                        <a href="{{ route('admin.places.show', $p) }}" class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700">View</a>
                        <a href="{{ route('admin.places.edit', $p) }}" class="px-3 py-1 bg-slate-900 text-white text-xs rounded-lg">Edit</a>
                        <form method="POST" action="{{ route('admin.places.destroy', $p) }}" onsubmit="return confirm('Delete place?')">@csrf @method('DELETE')<button class="px-3 py-1 bg-red-50 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-100">Delete</button></form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No places yet. <a class="text-blue-600 font-semibold" href="{{ route('admin.places.create') }}?city_id={{ $city->id }}">Add one</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $places->links() }}</div>
@endif
@endsection
