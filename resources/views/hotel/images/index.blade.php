@extends('layouts.hotel')
@section('title', 'Hotel Photos')
@section('content')
<div class="mb-6">
    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Hotel profile</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Hotel Photos</h1>
    <p class="mt-2 text-slate-500">Manage one primary hotel photo and up to 10 photos total for {{ $hotel->name }}.</p>
</div>
<form method="POST" action="{{ route('hotel.images.update') }}" enctype="multipart/form-data" class="grid gap-6">
    @csrf
    @method('PUT')
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    <section class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-5 flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid fa-image"></i></span>
            <div>
                <h2 class="font-bold text-slate-900">Primary Hotel Image</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">This is the main image representing your hotel. Upload one JPG, PNG, or WebP image up to 4 MB.</p>
            </div>
        </div>
        @if ($hotel->primary_image)
            <div class="mb-4 max-w-xl overflow-hidden rounded-xl border border-slate-200">
                <img src="{{ asset('storage/'.$hotel->primary_image) }}" alt="{{ $hotel->name }} primary hotel image" class="h-64 w-full object-cover">
                <label class="flex items-center gap-2 px-4 py-3 text-sm text-slate-600">
                    <input type="checkbox" name="remove_primary_image" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                    Remove primary image
                </label>
            </div>
        @endif
        <label for="primary_image" class="mb-2 block text-sm font-semibold text-slate-700">{{ $hotel->primary_image ? 'Replace primary image' : 'Upload primary image' }}</label>
        <input id="primary_image" name="primary_image" type="file" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700">
        @error('primary_image')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('remove_primary_image')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-5 flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fa-solid fa-images"></i></span>
            <div>
                <h2 class="font-bold text-slate-900">Secondary Hotel Images</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Upload additional hotel photos. Choose JPG, PNG, or WebP images up to 4 MB each. The primary image counts toward the 10-photo total.</p>
            </div>
        </div>
        <label for="secondary_images" class="mb-2 block text-sm font-semibold text-slate-700">Upload secondary images</label>
        <input id="secondary_images" name="secondary_images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700">
        @error('secondary_images')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('secondary_images.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @if (! empty($hotel->secondary_images))
            <div class="mt-6 border-t border-slate-100 pt-5">
                <h3 class="font-semibold text-slate-900">Current Secondary Images</h3>
                <p class="mt-1 text-sm text-slate-500">Select any secondary images you want to remove.</p>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($hotel->secondary_images as $image)
                        <label class="cursor-pointer overflow-hidden rounded-xl border border-slate-200">
                            <img src="{{ asset('storage/'.$image) }}" alt="{{ $hotel->name }} secondary hotel image" class="h-36 w-full object-cover">
                            <span class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600"><input type="checkbox" name="remove_secondary_images[]" value="{{ $image }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500"> Remove image</span>
                        </label>
                    @endforeach
                </div>
                @error('remove_secondary_images.*')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endif
    </section>

    <div class="flex justify-end">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-floppy-disk mr-2"></i>Save Hotel Images</button>
    </div>
</form>
@endsection
