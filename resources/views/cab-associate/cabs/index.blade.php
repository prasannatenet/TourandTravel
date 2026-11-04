@extends('layouts.cab-associate')
@section('title', 'My Cabs')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Fleet management</p><h1 class="mt-2 text-3xl font-bold text-slate-900">My Cabs</h1><p class="mt-2 text-slate-500">Manage your cab details and availability calendar.</p></div>
    <a href="{{ route('cab-associate.cabs.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-plus mr-2"></i>Add Cab</a>
</div>
<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    @if ($cabs->isEmpty())
        <div class="px-6 py-14 text-center">
            <i class="fa-solid fa-car text-3xl text-slate-300"></i>
            <h2 class="mt-4 font-bold text-slate-900">No cabs yet</h2>
            <p class="mt-1 text-sm text-slate-500">Add your first cab to manage its details and availability.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Cab</th><th class="px-5 py-3">Registration</th><th class="px-5 py-3">City</th><th class="px-5 py-3">Capacity</th><th class="px-5 py-3">Rate / km</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($cabs as $cab)
                        <tr>
                            <td class="px-5 py-4"><div class="flex items-center gap-3">@if ($cab->image)<img src="{{ asset('storage/'.$cab->image) }}" alt="{{ $cab->name }}" class="h-12 w-16 rounded-lg object-cover">@else<span class="flex h-12 w-16 items-center justify-center rounded-lg bg-slate-100 text-slate-400"><i class="fa-solid fa-image"></i></span>@endif<div><p class="font-semibold text-slate-900">{{ $cab->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $cab->type }}</p></div></div></td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $cab->registration_number }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $cab->city?->name ?? 'Not set' }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $cab->capacity }} seats</td>
                            <td class="px-5 py-4 text-slate-700">₹{{ number_format((float) $cab->rate_per_km, 2) }}</td>
                            <td class="px-5 py-4"><span class="rounded px-2.5 py-1 text-xs font-semibold {{ $cab->status === 'active' ? 'bg-green-100 text-green-700' : ($cab->status === 'inactive' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($cab->status) }}</span></td>
                            <td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('cab-associate.cabs.availability', $cab) }}" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700">Schedule</a><a href="{{ route('cab-associate.cabs.edit', $cab) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Edit</a><form method="POST" action="{{ route('cab-associate.cabs.destroy', $cab) }}" onsubmit="return confirm('Delete this cab and its availability schedule?')">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600">Delete</button></form></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $cabs->links() }}</div>
    @endif
</div>
@endsection
