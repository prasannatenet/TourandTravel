@extends('layouts.admin')
@section('title', 'Add Hotel')
@section('header', 'Add Hotel')
@section('subheader', 'Choose a country, state, and city, then enter hotel details and pricing.')
@section('content')
@include('admin.hotels._form')
@endsection
