@extends('layouts.admin')
@section('title', 'Hotels')
@section('header', 'Hotel Management')
@section('subheader', 'Manage hotels, star ratings, and addresses by city.')
@section('content')
@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500">{{ $hotels->total() }} hotels</p>
    <a href="{{ route('admin.hotels.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Add Hotel</a>
</div>
<form method="GET" action="{{ route('admin.hotels.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm">
    <div class="min-w-[220px] flex-1">
        <label for="hotel-search" class="mb-1 block text-xs font-semibold text-slate-500">Search hotels</label>
        <input id="hotel-search" type="search" name="search" value="{{ $search }}" placeholder="Hotel, city, or address" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Search</button>
    @if ($search)
        <a href="{{ route('admin.hotels.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm">Clear</a>
    @endif
</form>
<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Hotel</th>
                    <th class="px-5 py-3">Hotel code</th>
                    <th class="px-5 py-3">Login username</th>
                    <th class="px-5 py-3">Location</th>
                    <th class="px-5 py-3">Stars</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($hotels as $hotel)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-900">{{ $hotel->name }}</p>
                            <p class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $hotel->address }}</p>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $hotel->hotel_code }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ $hotel->username ?: 'Not set' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $hotel->city->name }}, {{ $hotel->city->state->name }}, {{ $hotel->city->state->country->name }}</td>
                        <td class="px-5 py-4">{{ $hotel->star_rating }} star</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.hotels.show', $hotel) }}" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">View</a>
                                <a href="{{ route('admin.hotels.edit', $hotel) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Edit</a>
                                <form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" onsubmit="return confirm('Delete this hotel?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">No hotels found. Add a hotel to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-5">{{ $hotels->links() }}</div>
@endsection
