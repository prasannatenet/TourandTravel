@extends('layouts.admin')
@section('title', 'Create Package')
@section('header', 'New Tour Package')
@section('subheader', 'Add a package with multi-destination selection.')

@section('content')
<div class="overflow-hidden bg-white shadow rounded-2xl">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Create Package</h2>
            <p class="text-xs text-slate-500 mt-0.5">Add a new tour package with multi-destination selection.</p>
        </div>
        <a href="{{ route('admin.packages.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form method="POST" action="{{ route('admin.packages.store') }}" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Package Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Departure City *</label>
                <input type="text" name="departure_city" value="{{ old('departure_city') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('departure_city') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Base Price (USD) *</label>
                <input type="number" name="base_price" value="{{ old('base_price') }}" step="1.00" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('base_price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Days *</label>
                <input type="number" name="days" value="{{ old('days') ?? 1 }}" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('days') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nights *</label>
                <input type="number" name="nights" value="{{ old('nights') ?? 1 }}" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('nights') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1" {{ old('is_active', '1') ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') !== '1' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
            <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Short description of the package...">{{ old('description') }}</textarea>
            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="border-t border-slate-200 pt-4">
            <h4 class="text-sm font-semibold text-slate-700 mb-3">Package Image (optional)</h4>
            <input type="file" name="image" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
        </div>

        <div class="border-t border-slate-200 pt-4">
            @include('admin.packages._destination-picker', [
                'countries' => $countries,
                'states' => $states,
                'cities' => $cities,
                'places' => $places,
            ])
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
            <a href="{{ route('admin.packages.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Create Package</button>
        </div>
    </form>
</div>
@endsection
