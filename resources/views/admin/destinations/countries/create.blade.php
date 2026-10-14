@extends('layouts.admin')
@section('title', 'Add Country')
@section('header', 'Add Country')
@section('subheader', 'Step 1 of Destination setup.')
@section('content')
<div class="max-w-xl bg-white rounded-2xl shadow-sm p-6">
<form method="POST" action="{{ route('admin.countries.store') }}" class="space-y-4">
@csrf
@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3"><ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<div><label class="block text-sm font-semibold mb-1">Country Name *</label><input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="India" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
<div class="flex gap-2"><button class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg">Save Country</button><a href="{{ route('admin.countries.index') }}" class="px-5 py-2 bg-slate-100 text-sm rounded-lg">Cancel</a></div>
</form>
</div>
@endsection
