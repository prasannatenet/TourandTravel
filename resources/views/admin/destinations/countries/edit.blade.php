@extends('layouts.admin')
@section('title', 'Edit Country')
@section('header', 'Edit Country')
@section('content')
<div class="max-w-xl bg-white rounded-2xl shadow-sm p-6">
<form method="POST" action="{{ route('admin.countries.update', $country) }}" class="space-y-4">
@csrf @method('PUT')
<div><label class="block text-sm font-semibold mb-1">Country Name *</label><input name="name" value="{{ old('name', $country->name) }}" required maxlength="100" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div class="flex gap-2"><button class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg">Update</button><a href="{{ route('admin.countries.index') }}" class="px-5 py-2 bg-slate-100 text-sm rounded-lg">Cancel</a></div>
</form>
</div>
@endsection
