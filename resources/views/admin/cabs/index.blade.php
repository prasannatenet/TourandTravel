@extends('layouts.admin')
@section('title', 'Cabs')
@section('header', 'Cab Management')
@section('subheader', 'Manage cabs, registration numbers, types, and pricing by associate.')
@section('content')
@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500">{{ $cabs->total() }} cabs</p>
    <a href="{{ route('admin.cabs.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Add Cab</a>
</div>
<form method="GET" action="{{ route('admin.cabs.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm">
    <div class="min-w-[220px] flex-1">
        <label for="cab-search" class="mb-1 block text-xs font-semibold text-slate-500">Search cabs</label>
        <input id="cab-search" type="search" name="search" value="{{ $validated['search'] ?? '' }}" placeholder="Cab name, registration, or type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="min-w-[160px] flex-shrink-0">
        <label for="cab-status" class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
        <select id="cab-status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="active" {{ ($validated['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ ($validated['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="maintenance" {{ ($validated['status'] ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        </select>
    </div>
    <div class="min-w-[200px] flex-shrink-0">
        <label for="cab-associate" class="mb-1 block text-xs font-semibold text-slate-500">Associate</label>
        <select id="cab-associate" name="associate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All associates</option>
            @foreach ($associates as $associate)
                <option value="{{ $associate->id }}" {{ ($validated['associate'] ?? '') == $associate->id ? 'selected' : '' }}>{{ $associate->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Search</button>
    @if ($validated['search'] ?? null)
        <a href="{{ route('admin.cabs.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm">Clear</a>
    @endif
</form>
<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Cab</th>
                    <th class="px-5 py-3">Registration</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Capacity</th>
                    <th class="px-5 py-3">Associate</th>
                    <th class="px-5 py-3">Rate / km</th>
                    <th class="px-5 py-3">Availability</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($cabs as $cab)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-900">{{ $cab->name }}</p>
                            <p class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $cab->image ?: 'No image' }}</p>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $cab->registration_number }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ ucfirst($cab->type) }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ $cab->capacity }} seats</td>
                        <td class="px-5 py-4 text-slate-700">{{ $cab->associate?->name ?? 'Admin created' }}</td>
                        <td class="px-5 py-4 text-slate-600">Rs.{{ number_format($cab->rate_per_km) }}</td>
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.cabs.availability', $cab) }}" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">View schedule</a>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-semibold {{ $cab->status === 'active' ? 'bg-green-100 text-green-700' : ($cab->status === 'inactive' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">
                                {{ ucfirst($cab->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.cabs.edit', $cab) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Edit</a>
                                <form method="POST" action="{{ route('admin.cabs.destroy', $cab) }}" onsubmit="return confirm('Delete this cab?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-500">No cabs found. Add a cab to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-5">{{ $cabs->links() }}</div>
@endsection
