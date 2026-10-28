@extends('layouts.hotel')
@section('title', 'Rooms')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Room setup</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Rooms</h1>
        <p class="mt-2 text-slate-500">Manage room details, photos, highlights, key points, and amenities.</p>
    </div>
    <a href="{{ route('hotel.rooms.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-plus mr-2"></i>Add Room</a>
</div>
<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    @if ($rooms->isEmpty())
        <div class="px-6 py-14 text-center">
            <i class="fa-solid fa-bed text-3xl text-slate-300"></i>
            <h2 class="mt-4 font-bold text-slate-900">No rooms yet</h2>
            <p class="mt-1 text-sm text-slate-500">Create a room type, then add your first room.</p>
            <a href="{{ route('hotel.room-types.index') }}" class="mt-4 inline-flex font-semibold text-indigo-600 hover:text-indigo-800">Manage Room Types<i class="fa-solid fa-arrow-right ml-2 mt-1 text-xs"></i></a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-6 py-4">Room</th><th class="px-6 py-4">Room Type</th><th class="px-6 py-4">Adult</th><th class="px-6 py-4">Child (0–12)</th><th class="px-6 py-4">Extra Bed</th><th class="px-6 py-4">Description</th><th class="px-6 py-4 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rooms as $room)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if (! empty($room->images))
                                        <img src="{{ asset('storage/'.$room->images[0]) }}" alt="{{ $room->name }}" class="h-14 w-20 rounded-lg object-cover">
                                    @else
                                        <span class="flex h-14 w-20 items-center justify-center rounded-lg bg-slate-100 text-slate-400"><i class="fa-solid fa-image"></i></span>
                                    @endif
                                    <div><p class="font-semibold text-slate-900">{{ $room->name }}</p><p class="mt-1 text-xs text-slate-500">{{ count($room->images ?? []) }} image(s)</p></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $room->roomType->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $room->adult_cost !== null ? '₹'.number_format((float) $room->adult_cost, 2) : 'Not set' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $room->child_cost !== null ? '₹'.number_format((float) $room->child_cost, 2) : 'Not set' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $room->extra_bed_cost !== null ? '₹'.number_format((float) $room->extra_bed_cost, 2) : 'Not set' }}</td>
                            <td class="max-w-sm px-6 py-4 text-slate-600">{{ \Illuminate\Support\Str::limit($room->description, 110) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('hotel.rooms.edit', $room) }}" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('hotel.rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room and its uploaded photos?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $rooms->links() }}</div>
    @endif
</div>
@endsection
