@extends('layouts.hotel')
@section('title', 'Add Room')
@section('content')
<div class="mb-6">
    <a href="{{ route('hotel.rooms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-arrow-left mr-2"></i>Back to Rooms</a>
    <h1 class="mt-3 text-3xl font-bold text-slate-900">Add Room</h1>
    <p class="mt-2 text-slate-500">Add the room details guests should know about.</p>
</div>
@if ($roomTypes->isEmpty())
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
        <h2 class="font-bold">Create a room type first</h2>
        <p class="mt-1 text-sm">Rooms must be assigned to one of your hotel’s room types.</p>
        <a href="{{ route('hotel.room-types.create') }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Add Room Type</a>
    </div>
@else
    <form method="POST" action="{{ route('hotel.rooms.store') }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
        @csrf
        @include('hotel.rooms._form', ['room' => null, 'submitLabel' => 'Create Room'])
    </form>
@endif
@endsection
