@extends('layouts.admin')
@section('title', 'Add Cab')
@section('header', 'Add Cab')
@section('subheader', 'Assign a cab to an associate and enter its registration, type, and pricing.')
@section('content')
@include('admin.cabs._form', ['cab' => null, 'action' => route('admin.cabs.store'), 'method' => 'POST'])
@endsection
