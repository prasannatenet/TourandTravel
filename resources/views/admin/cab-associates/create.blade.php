@extends('layouts.admin')
@section('title', 'Add Cab Associate')
@section('header', 'Add Cab Associate')
@section('subheader', 'Register a cab associate who can manage their own cabs.')
@section('content')
@include('admin.cab-associates._form', ['associate' => null, 'action' => route('admin.cab-associates.store'), 'method' => 'POST'])
@endsection
