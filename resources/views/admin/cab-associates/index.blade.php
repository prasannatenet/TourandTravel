@extends('layouts.admin')
@section('title', 'Cab Associates')
@section('header', 'Cab Associate Management')
@section('subheader', 'Manage cab associates who own and operate cabs.')
@section('content')
@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500">{{ $associates->total() }} associates</p>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('cab-associate.login') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Associate Portal</a>
        <a href="{{ route('admin.cab-associates.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Add Associate</a>
    </div>
</div>
<form method="GET" action="{{ route('admin.cab-associates.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm">
    <div class="min-w-[220px] flex-1">
        <label for="associate-search" class="mb-1 block text-xs font-semibold text-slate-500">Search associates</label>
        <input id="associate-search" type="search" name="search" value="{{ $validated['search'] ?? '' }}" placeholder="Name, username, or phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="min-w-[160px] flex-shrink-0">
        <label for="associate-status" class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
        <select id="associate-status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="active" {{ ($validated['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ ($validated['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Search</button>
    @if ($validated['search'] ?? null)
        <a href="{{ route('admin.cab-associates.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm">Clear</a>
    @endif
</form>
<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Associate</th>
                    <th class="px-5 py-3">Username</th>
                    <th class="px-5 py-3">Phone</th>
                    <th class="px-5 py-3">City</th>
                    <th class="px-5 py-3">Commission</th>
                    <th class="px-5 py-3">Cabs</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($associates as $associate)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-900">{{ $associate->name }}</p>
                            <p class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $associate->email ?: 'No email' }}</p>
                        </td>
                        <td class="px-5 py-4 text-slate-700">{{ $associate->username }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ $associate->phone }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ $associate->city->name ?? 'Not set' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ number_format($associate->commission_rate) }}%</td>
                        <td class="px-5 py-4 text-slate-600">{{ $associate->cabs->count() }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-semibold {{ $associate->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($associate->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.cab-associates.edit', $associate) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Edit</a>
                                <form method="POST" action="{{ route('admin.cab-associates.destroy', $associate) }}" onsubmit="return confirm('Delete this associate? Their cabs will also be removed.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">No cab associates found. Add an associate to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-5">{{ $associates->links() }}</div>
@endsection
