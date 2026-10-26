@extends('layouts.admin')
@section('title', 'New Inclusion')
@section('header', 'New Package Inclusion')
@section('subheader', 'Add a reusable meal, vehicle, or service for packages.')

@section('content')
<div class="overflow-hidden bg-white shadow rounded-2xl">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div><h2 class="text-lg font-bold text-slate-800">Create Inclusion</h2><p class="text-xs text-slate-500 mt-0.5">Package-specific combinations are selected when editing each package.</p></div>
        <a href="{{ route('admin.inclusions.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
    <form method="POST" action="{{ route('admin.inclusions.store') }}" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-5">
        @csrf
        @include('admin.inclusions._form')
        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
            <a href="{{ route('admin.inclusions.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Create Inclusion</button>
        </div>
    </form>
</div>
@endsection
