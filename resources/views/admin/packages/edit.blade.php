@extends('layouts.admin')
@section('title', 'Edit Package')
@section('header', 'Edit Tour Package')
@section('subheader', 'Edit package and multi-destination selection.')

@section('content')
<div class="overflow-hidden bg-white shadow rounded-2xl">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">{{ $package->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Edit package and multi-destination selection.</p>
        </div>
        <a href="{{ route('admin.packages.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form method="POST" action="{{ route('admin.packages.update', $package) }}" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Package Name *</label>
                <input type="text" name="name" value="{{ old('name', $package->name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Departure City *</label>
                <input type="text" name="departure_city" value="{{ old('departure_city', $package->departure_city) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('departure_city') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Days *</label>
                <input id="package-days" type="number" name="days" value="{{ old('days', $package->days) }}" min="1" max="99" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('days') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nights *</label>
                <input type="number" name="nights" value="{{ old('nights', $package->nights) }}" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('nights') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1" {{ old('is_active', $package->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $package->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
        </div>

        <div class="space-y-5">
            @include('admin.packages._commercial-settings', ['package' => $package])
        </div>

        <div class="border-t border-slate-200 pt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
            <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Short description of the package...">{{ old('description', $package->description) }}</textarea>
            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="border-t border-slate-200 pt-4">
            <h4 class="text-sm font-semibold text-slate-700 mb-3">Package images (optional)</h4>
            @if($package->image)
            <div class="mb-3 text-sm text-slate-500">
                <p class="mb-2 font-medium text-slate-700">Current cover</p>
                <img src="{{ asset('storage/'.$package->image) }}" alt="{{ $package->name }} cover" class="mb-2 h-28 w-48 rounded-lg object-cover">
            </div>
            @endif
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Replace cover photo</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
            @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

            <div class="mt-5">
                <p class="mb-2 text-sm font-semibold text-slate-700">Gallery</p>
                @if($package->galleryImages->isNotEmpty())
                    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach($package->galleryImages as $galleryImage)
                            <label class="rounded-lg border border-slate-200 p-2">
                                <img src="{{ asset('storage/'.$galleryImage->image) }}" alt="Package gallery photo" class="h-24 w-full rounded object-cover">
                                <span class="mt-2 flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="remove_gallery_images[]" value="{{ $galleryImage->id }}" class="rounded border-slate-300 text-red-600"> Remove photo</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="mb-3 text-xs text-slate-500">No gallery photos added.</p>
                @endif
                <label for="package-gallery-files" class="mb-1.5 block text-sm font-medium text-slate-700">Add gallery photos (up to 8 total, 4 MB each)</label>
                <input id="package-gallery-files" type="file" name="gallery_images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700">
                @error('gallery_images')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @foreach($errors->getMessages() as $field => $messages)
                    @if(str_starts_with($field, 'gallery_images.'))<p class="mt-1 text-xs text-red-600">{{ $messages[0] }}</p>@endif
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-200 pt-4">
            @include('admin.packages._destination-picker', [
                'countries' => $countries,
                'states' => $states,
                'cities' => $cities,
                'places' => $places,
                'package' => $package,
                'showPlaces' => false,
            ])
        </div>

        @include('admin.packages._plan-manager', [
            'places' => $places,
            'inclusions' => $inclusions,
            'inclusionItems' => $inclusionItems,
            'planItems' => $planItems,
        ])

        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
            <a href="{{ route('admin.packages.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Update Package</button>
        </div>
    </form>
</div>
@endsection
