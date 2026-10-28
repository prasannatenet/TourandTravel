@extends('layouts.admin')
@section('title', 'Create Package')
@section('header', 'Create Tour Package')
@section('subheader', 'Add package details, choose what is included, and map the destinations.')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-900/10">
        <div class="flex flex-col gap-6 px-5 py-6 sm:px-8 sm:py-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-50">
                    <i class="fa-solid fa-suitcase-rolling"></i> Package setup
                </span>
                <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Build a trip</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-indigo-100">Start with the essentials, choose inclusions and destinations, then lay out each day of the trip.</p>
            </div>
            <a href="{{ route('admin.packages.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                <i class="fa-solid fa-arrow-left text-xs"></i> Back to packages
            </a>
        </div>
        <nav aria-label="Package form sections" class="flex flex-wrap gap-2 border-t border-white/15 bg-indigo-950/10 px-5 py-3 sm:px-8">
            <a href="#package-details" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-indigo-200">01</span> Details</a>
            <a href="#package-commercial" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-indigo-200">02</span> Category & pricing</a>
            <a href="#package-destinations" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-indigo-200">03</span> Destinations</a>
            <a href="#package-plan" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-indigo-200">04</span> Day-by-day plan</a>
            <a href="#package-images" class="rounded-lg px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/15"><span class="mr-1.5 text-indigo-200">05</span> Images</a>
        </nav>
    </section>

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                <div>
                    <p class="font-semibold">Please review the highlighted fields.</p>
                    <p class="mt-0.5 text-red-700">There {{ $errors->count() === 1 ? 'is' : 'are' }} {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('issue', $errors->count()) }} to fix before saving.</p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.packages.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <section id="package-details" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-700">01</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Package details</h3>
                    <p class="mt-1 text-sm text-slate-500">Give the package a clear name, price, and length.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-x-5 gap-y-4 px-5 py-5 sm:grid-cols-2 sm:px-7 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-2">
                    <label for="package-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Package name <span class="text-red-500">*</span></label>
                    <input id="package-name" type="text" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="off" placeholder="For example: Kerala Backwaters Escape" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                    @error('name') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="departure-city" class="mb-1.5 block text-sm font-semibold text-slate-700">Departure city <span class="text-red-500">*</span></label>
                    <input id="departure-city" type="text" name="departure_city" value="{{ old('departure_city') }}" required maxlength="100" placeholder="For example: Delhi" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                    @error('departure_city') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="package-days" class="mb-1.5 block text-sm font-semibold text-slate-700">Duration <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <div class="relative min-w-0 flex-1">
                            <input id="package-days" type="number" name="days" value="{{ old('days', 1) }}" required min="1" max="99" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-12 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">days</span>
                        </div>
                        <div class="relative min-w-0 flex-1">
                            <input id="package-nights" type="number" name="nights" value="{{ old('nights', 1) }}" required min="1" max="99" aria-label="Number of nights" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-14 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">nights</span>
                        </div>
                    </div>
                    @error('days') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    @error('nights') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="package-status" class="mb-1.5 block text-sm font-semibold text-slate-700">Visibility</label>
                    @php($selectedStatus = (string) old('is_active', '1'))
                    <select id="package-status" name="is_active" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                        <option value="1" @selected($selectedStatus === '1')>Active — visible to customers</option>
                        <option value="0" @selected($selectedStatus === '0')>Draft — hidden from customers</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="package-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Short description <span class="font-normal text-slate-400">(optional)</span></label>
                    <textarea id="package-description" name="description" rows="3" maxlength="2000" placeholder="What makes this trip special? Add a short overview for customers." class="w-full resize-y rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">{{ old('description') }}</textarea>
                    <div class="mt-1.5 flex items-center justify-between gap-3">
                        <p class="text-xs text-slate-400">Keep it concise. You can add the day-by-day details later.</p>
                        @error('description') <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <div id="package-commercial" class="scroll-mt-28 space-y-5">
            @include('admin.packages._commercial-settings')
        </div>

        <section id="package-destinations" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sm font-bold text-sky-700">03</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Destinations</h3>
                    <p class="mt-1 text-sm text-slate-500">Choose the places guests will visit. Each level filters the next.</p>
                </div>
            </div>
            <div class="px-5 py-5 sm:px-7">
                @include('admin.packages._destination-picker', [
                    'countries' => $countries,
                    'states' => $states,
                    'cities' => $cities,
                    'places' => $places,
                'showPlaces' => false,
                ])
            </div>
        </section>

        @include('admin.packages._plan-manager', [
            'places' => $places,
            'inclusions' => $inclusions,
            'inclusionItems' => $inclusionItems,
            'planItems' => $planItems,
            'showInclusionsSection' => false,
        ])

        <div class="hidden" aria-hidden="true">
            @include('admin.packages._inclusion-picker', [
                'inclusions' => $inclusions,
                'packageInclusionRows' => old('inclusions', []),
            ])
        </div>

        <section id="package-images" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-sm font-bold text-amber-700">05</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Package images <span class="font-normal text-slate-400">(optional)</span></h3>
                    <p class="mt-1 text-sm text-slate-500">Choose a cover photo and up to 8 gallery photos. JPG, PNG, or WebP up to 4 MB each.</p>
                </div>
            </div>
            <div class="px-5 py-5 sm:px-7">
                <label for="package-image-file" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50/70 px-5 py-8 text-center transition hover:border-indigo-400 hover:bg-indigo-50/40">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-indigo-600 shadow-sm"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <span class="mt-3 text-sm font-semibold text-slate-700">Choose a cover image</span>
                    <span class="mt-1 text-xs text-slate-400">Select an image from your device</span>
                    <input id="package-image-file" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-4 block w-full max-w-xs text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700">
                </label>
                @error('image') <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                <div class="mt-5">
                    <label for="package-gallery-files" class="mb-1.5 block text-sm font-semibold text-slate-700">Gallery images</label>
                    <input id="package-gallery-files" type="file" name="gallery_images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700">
                    <p class="mt-1 text-xs text-slate-400">Select up to 8 additional photos.</p>
                    @error('gallery_images') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @foreach($errors->getMessages() as $field => $messages)
                        @if(str_starts_with($field, 'gallery_images.'))<p class="mt-1 text-xs text-red-600">{{ $messages[0] }}</p>@endif
                    @endforeach
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="text-xs text-slate-500"><span class="text-red-500">*</span> Required fields</p>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20">
                    <i class="fa-solid fa-check text-xs"></i> Create package
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
