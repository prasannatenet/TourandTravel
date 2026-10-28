@extends('layouts.hotel')
@section('title', 'Edit Room')
@section('content')
<div class="mb-6">
    <a href="{{ route('hotel.rooms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-arrow-left mr-2"></i>Back to Rooms</a>
    <h1 class="mt-3 text-3xl font-bold text-slate-900">Edit Room</h1>
    <p class="mt-2 text-slate-500">Update {{ $room->name }}.</p>
</div>
<form method="POST" action="{{ route('hotel.rooms.update', $room) }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @method('PUT')
    @include('hotel.rooms._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
