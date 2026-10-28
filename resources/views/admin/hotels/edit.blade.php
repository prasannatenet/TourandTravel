@extends('layouts.admin')
@section('title', 'Edit Hotel')
@section('header', 'Edit Hotel')
@section('subheader', 'Update the hotel location, details, and pricing.')
@section('content')
@include('admin.hotels._form')
@endsection
