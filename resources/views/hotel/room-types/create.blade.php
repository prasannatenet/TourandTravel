@extends('layouts.hotel')
@section('title', 'Add Room Type')
@section('content')
<div class="mb-6">
    <a href="{{ route('hotel.room-types.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-arrow-left mr-2"></i>Back to Room Types</a>
    <h1 class="mt-3 text-3xl font-bold text-slate-900">Add Room Type</h1>
    <p class="mt-2 text-slate-500">Create a category for rooms at your hotel.</p>
</div>
<form method="POST" action="{{ route('hotel.room-types.store') }}" class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @include('hotel.room-types._form', ['roomType' => null, 'submitLabel' => 'Create Room Type'])
</form>
@endsection
