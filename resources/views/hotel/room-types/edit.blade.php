@extends('layouts.hotel')
@section('title', 'Edit Room Type')
@section('content')
<div class="mb-6">
    <a href="{{ route('hotel.room-types.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-arrow-left mr-2"></i>Back to Room Types</a>
    <h1 class="mt-3 text-3xl font-bold text-slate-900">Edit Room Type</h1>
    <p class="mt-2 text-slate-500">Update {{ $roomType->name }}.</p>
</div>
<form method="POST" action="{{ route('hotel.room-types.update', $roomType) }}" class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @method('PUT')
    @include('hotel.room-types._form', ['submitLabel' => 'Save Changes'])
</form>
@endsection
