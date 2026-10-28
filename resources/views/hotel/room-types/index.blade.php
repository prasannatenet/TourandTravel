@extends('layouts.hotel')
@section('title', 'Room Types')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Room setup</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Room Types</h1>
        <p class="mt-2 text-slate-500">Manage categories such as Standard, Deluxe, and Luxury.</p>
    </div>
    <a href="{{ route('hotel.room-types.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-plus mr-2"></i>Add Room Type</a>
</div>
@if ($errors->has('room_type'))
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('room_type') }}</div>
@endif
<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    @if ($roomTypes->isEmpty())
        <div class="px-6 py-14 text-center">
            <i class="fa-solid fa-layer-group text-3xl text-slate-300"></i>
            <h2 class="mt-4 font-bold text-slate-900">No room types yet</h2>
            <p class="mt-1 text-sm text-slate-500">Add a room type before creating rooms.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-6 py-4">Type</th><th class="px-6 py-4">Description</th><th class="px-6 py-4">Rooms</th><th class="px-6 py-4 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($roomTypes as $roomType)
                        <tr>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $roomType->name }}</td>
                            <td class="max-w-md px-6 py-4 text-slate-600">{{ $roomType->description ?: '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $roomType->rooms_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('hotel.room-types.edit', $roomType) }}" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                    @if ($roomType->rooms_count === 0)
                                        <form method="POST" action="{{ route('hotel.room-types.destroy', $roomType) }}" onsubmit="return confirm('Delete this room type?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $roomTypes->links() }}</div>
    @endif
</div>
@endsection
