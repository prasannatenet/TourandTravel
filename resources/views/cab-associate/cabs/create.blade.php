@extends('layouts.cab-associate')
@section('title', 'Add Cab')
@section('content')
<div class="mb-6"><p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Fleet management</p><h1 class="mt-2 text-3xl font-bold text-slate-900">Add Cab</h1></div>
@include('cab-associate.cabs._form', ['action' => route('cab-associate.cabs.store'), 'method' => 'POST', 'submitLabel' => 'Save Cab'])
@endsection
