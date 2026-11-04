@extends('layouts.admin')
@section('title', 'Edit Cab')
@section('header', 'Edit Cab')
@section('subheader', 'Update cab details, associate, and pricing.')
@section('content')
@include('admin.cabs._form', ['cab' => $cab, 'action' => route('admin.cabs.update', $cab), 'method' => 'PUT'])
@endsection
