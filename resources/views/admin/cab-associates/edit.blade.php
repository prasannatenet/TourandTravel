@extends('layouts.admin')
@section('title', 'Edit Cab Associate')
@section('header', 'Edit Cab Associate')
@section('subheader', 'Update associate details, city, commission, and status.')
@section('content')
@include('admin.cab-associates._form', ['associate' => $associate, 'action' => route('admin.cab-associates.update', $associate), 'method' => 'PUT'])
@endsection
