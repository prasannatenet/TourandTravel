@extends('layouts.cab-associate')
@section('title', 'Edit Cab')
@section('content')
<div class="mb-6"><p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Fleet management</p><h1 class="mt-2 text-3xl font-bold text-slate-900">Edit {{ $cab->name }}</h1></div>
@include('cab-associate.cabs._form', ['action' => route('cab-associate.cabs.update', $cab), 'method' => 'PUT', 'submitLabel' => 'Save Changes'])
@endsection
