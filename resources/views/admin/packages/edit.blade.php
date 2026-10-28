@extends('layouts.admin')
@section('title', 'Edit Package')
@section('header', 'Edit Tour Package')
@section('subheader', 'Update package details, inclusions, destinations, and itinerary.')

@section('content')
<div class="mx-auto max-w-[1500px] pb-6">
    @if($errors->any())
        <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                <div>
                    <p class="font-semibold">Please review the highlighted fields.</p>
                    <p class="mt-0.5 text-red-700">There {{ $errors->count() === 1 ? 'is' : 'are' }} {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('issue', $errors->count()) }} to fix before saving.</p>
                </div>
            </div>
        </div>
    @endif

    @php
        $packageInclusionRows = old('inclusions');
        if ($packageInclusionRows === null) {
            $packageInclusionRows = collect($inclusionItems)->mapWithKeys(function ($item): array {
                $inclusion = $item->inclusion;
                $isCustomized = $item->customized;

                return ['item_'.$item->id => [
                    'id' => $item->id,
                    'package_inclusion_id' => $inclusion->id,
                    'custom_name' => $isCustomized ? $item->custom_name : $inclusion->name,
                    'custom_place_from' => $isCustomized ? $item->custom_place_from : $inclusion->place_from,
                    'custom_place_to' => $isCustomized ? $item->custom_place_to : $inclusion->place_to,
                    'custom_description' => $isCustomized ? $item->custom_description : $inclusion->description,
                    'custom_scheduled_time' => $isCustomized ? $item->custom_scheduled_time : $inclusion->scheduled_time,
                    'custom_duration_hours' => $isCustomized ? $item->custom_duration_hours : '',
                    'custom_image_one' => $item->custom_image_one,
                    'custom_image_two' => $item->custom_image_two,
                    'customized' => $isCustomized,
                ]];
            })->all();
        }
    @endphp

    <form method="POST" action="{{ route('admin.packages.update', $package) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)_300px]">
            <aside class="space-y-6">
                <div class="overflow-hidden rounded-[26px] p-5 text-white shadow-xl shadow-indigo-900/15" style="background: linear-gradient(135deg, #4338ca 0%, #4f46e5 52%, #7c3aed 100%); color: #ffffff;">
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-indigo-100" style="color: #e0e7ff;">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        PACKAGE UPDATE
                    </div>

                    <h2 class="text-3xl font-bold leading-tight tracking-tight text-white" style="color: #ffffff;">{{ $package->name }}</h2>
                    <p class="mt-3 text-sm leading-6 text-indigo-100" style="color: #e0e7ff;">Update the trip details, pricing, destinations, and daily itinerary without losing the package setup you already created.</p>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/15" style="color: #ffffff;">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to packages
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-2 text-[10px] font-semibold text-indigo-100" style="color: #e0e7ff;">
                        <a href="#package-details" class="rounded-full bg-white/10 px-2.5 py-1.5 transition hover:bg-white/20">01 Details</a>
                        <a href="#package-commercial" class="rounded-full bg-white/10 px-2.5 py-1.5 transition hover:bg-white/20">02 Pricing</a>
                        <a href="#package-destinations" class="rounded-full bg-white/10 px-2.5 py-1.5 transition hover:bg-white/20">03 Destinations</a>
                        <a href="#package-inclusions" class="rounded-full bg-white/10 px-2.5 py-1.5 transition hover:bg-white/20">04 Inclusions</a>
                        <a href="#package-plan" class="rounded-full bg-white/10 px-2.5 py-1.5 transition hover:bg-white/20">05 Itinerary</a>
                        <a href="#package-images" class="rounded-full bg-white/10 px-2.5 py-1.5 transition hover:bg-white/20">06 Images</a>
                    </div>
                </div>

                <div id="package-details" class="scroll-mt-28 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-xs font-bold text-slate-700">01</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Package details</h3>
                            <p class="text-xs text-slate-500">Update the package information and trip duration.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="package-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Package name <span class="text-red-500">*</span></label>
                            <input id="package-name" type="text" name="name" value="{{ old('name', $package->name) }}" required maxlength="100" autocomplete="off" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                            @error('name') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="departure-city" class="mb-1.5 block text-sm font-semibold text-slate-700">Departure city <span class="text-red-500">*</span></label>
                            <input id="departure-city" type="text" name="departure_city" value="{{ old('departure_city', $package->departure_city) }}" required maxlength="100" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                            @error('departure_city') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="package-days" class="mb-1.5 block text-sm font-semibold text-slate-700">Duration</label>
                                <div class="relative">
                                    <input id="package-days" type="number" name="days" value="{{ old('days', $package->days) }}" required min="1" max="99" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[10px] text-slate-400">days</span>
                                </div>
                                @error('days') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="package-nights" class="mb-1.5 block text-sm font-semibold text-slate-700">Nights</label>
                                <div class="relative">
                                    <input id="package-nights" type="number" name="nights" value="{{ old('nights', $package->nights) }}" required min="1" max="99" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-11 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[10px] text-slate-400">nights</span>
                                </div>
                                @error('nights') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="package-status" class="mb-1.5 block text-sm font-semibold text-slate-700">Visibility</label>
                            @php($selectedStatus = (string) old('is_active', $package->is_active ? '1' : '0'))
                            <select id="package-status" name="is_active" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="1" @selected($selectedStatus === '1')>Active</option>
                                <option value="0" @selected($selectedStatus === '0')>Draft</option>
                            </select>
                        </div>

                        <div>
                            <label for="package-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Short description</label>
                            <textarea id="package-description" name="description" rows="4" maxlength="2000" placeholder="What makes this trip special?" class="w-full resize-y rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">{{ old('description', $package->description) }}</textarea>
                            @error('description') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </aside>

            <main class="space-y-6">
                <div id="package-commercial" class="scroll-mt-28 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-xs font-bold text-indigo-700">02</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Commercial settings</h3>
                            <p class="text-xs text-slate-500">Pricing, taxes, and package setup.</p>
                        </div>
                    </div>
                    @include('admin.packages._commercial-settings', ['package' => $package])
                </div>

                <div id="package-destinations" class="scroll-mt-28 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-xs font-bold text-sky-700">03</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Destinations</h3>
                            <p class="text-xs text-slate-500">Choose the destination scope for this package.</p>
                        </div>
                    </div>
                    @include('admin.packages._destination-picker', [
                        'countries' => $countries,
                        'states' => $states,
                        'cities' => $cities,
                        'places' => $places,
                        'package' => $package,
                        'showPlaces' => false,
                    ])
                </div>

                <section id="package-inclusions" class="scroll-mt-28 overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-start gap-3.5 border-b border-slate-100 bg-slate-50/50 px-5 py-5 sm:px-7">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-sm">04</span>
                        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Package Inclusions &amp; Amenities</h3>
                                <p class="mt-1 text-sm text-slate-500">Create services with descriptions and up to two photos, then schedule them on a day.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.inclusions.index') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-list-check"></i> Manage services
                                </a>
                                <a href="{{ route('admin.inclusions.create') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                    <i class="fa-solid fa-plus"></i> New service
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 sm:p-7">
                        @if($inclusions->isNotEmpty())
                            <div>
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Available services</p>
                                <div class="flex flex-wrap gap-2.5">
                                    @foreach($inclusions as $inclusion)
                                        <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">
                                            <i class="fa-solid fa-check text-xs text-emerald-600"></i>{{ $inclusion->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-slate-500">No catalog services are available yet. Create services such as Pickup Car, Lunch, or Dinner in Package Inclusions first.</p>
                        @endif
                    </div>
                </section>

                <div class="hidden" aria-hidden="true">
                    @include('admin.packages._inclusion-picker', [
                        'inclusions' => $inclusions,
                        'packageInclusionRows' => $packageInclusionRows,
                    ])
                </div>

                @include('admin.packages._plan-manager', [
                    'places' => $places,
                    'inclusions' => $inclusions,
                    'inclusionItems' => $inclusionItems,
                    'planItems' => $planItems,
                    'showInclusionsSection' => false,
                ])
            </main>

            <aside class="space-y-6">
                <div id="package-images" class="scroll-mt-28 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-xs font-bold text-amber-700">06</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Package images</h3>
                            <p class="text-xs text-slate-500">Replace the cover image or add more gallery photos.</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        @if($package->image)
                            <div class="rounded-[18px] border border-slate-200 bg-slate-50 p-3">
                                <p class="mb-2 text-sm font-semibold text-slate-700">Current cover image</p>
                                <img src="{{ asset('storage/'.$package->image) }}" alt="{{ $package->name }} cover" class="h-28 w-full rounded-xl object-cover shadow-sm">
                            </div>
                        @endif

                        <div class="rounded-[18px] border border-dashed border-slate-300 bg-slate-50/70 p-4 text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm text-indigo-600">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-slate-700">Replace cover image</p>
                            <p class="mt-1 text-xs text-slate-400">Select a JPG, PNG, or WebP image from your device</p>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-4 block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700">
                            @error('image') <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-slate-700">Gallery photos</p>
                            @if($package->galleryImages->isNotEmpty())
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($package->galleryImages as $galleryImage)
                                        <label class="rounded-xl border border-slate-200 bg-slate-50 p-2">
                                            <img src="{{ asset('storage/'.$galleryImage->image) }}" alt="Package gallery photo" class="h-20 w-full rounded-lg object-cover">
                                            <span class="mt-2 flex items-center gap-2 text-[11px] text-slate-600"><input type="checkbox" name="remove_gallery_images[]" value="{{ $galleryImage->id }}" class="rounded border-slate-300 text-red-600"> Remove photo</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-500">No gallery photos added yet.</p>
                            @endif

                            <div class="rounded-[18px] border border-dashed border-slate-300 bg-slate-50/70 p-4">
                                <label for="package-gallery-files" class="mb-1.5 block text-sm font-semibold text-slate-700">Add gallery photos</label>
                                <input id="package-gallery-files" type="file" name="gallery_images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700">
                                <p class="mt-1 text-[11px] text-slate-400">Add up to 8 additional photos. Each file must be 4 MB or less.</p>
                                @error('gallery_images') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div id="package-images" class="scroll-mt-28 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-500"><span class="text-red-500">*</span> Required fields</p>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20">
                            <i class="fa-solid fa-check text-xs"></i> Update package
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>
@endsection
