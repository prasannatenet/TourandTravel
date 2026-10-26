<div>
    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Inclusion Name *</label>
    <input id="name" type="text" name="name" value="{{ old('name', $inclusion->name ?? '') }}" required maxlength="120" placeholder="Breakfast, Sedan transfer, Guided tour..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
</div>

<div class="border-t border-slate-200 pt-4">
    <h3 class="text-sm font-semibold text-slate-700">Inclusion Images <span class="font-normal text-slate-400">(up to 2 images, optional)</span></h3>
    <p class="text-xs text-slate-500 mt-1">Add photos for this meal, vehicle, or service. JPG, PNG, and WebP up to 4 MB each.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
        @foreach(['image_one' => 'Image 1', 'image_two' => 'Image 2'] as $imageField => $imageLabel)
            <div class="rounded-xl border border-slate-200 p-3">
                <label for="{{ $imageField }}" class="block text-xs font-semibold text-slate-600 mb-2">{{ $imageLabel }}</label>
                @if(isset($inclusion) && $inclusion->{$imageField})
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ asset('storage/'.$inclusion->{$imageField}) }}" alt="{{ $inclusion->name }} {{ strtolower($imageLabel) }}" class="h-16 w-20 rounded-lg object-cover">
                        <label class="flex items-center gap-2 text-xs text-slate-500">
                            <input type="checkbox" name="remove_{{ $imageField }}" value="1" @checked(old('remove_'.$imageField)) class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            Remove current image
                        </label>
                    </div>
                @endif
                <input id="{{ $imageField }}" type="file" name="{{ $imageField }}" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-500 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                @if($errors->has($imageField)) <p class="text-xs text-red-600 mt-1">{{ $errors->first($imageField) }}</p> @endif
            </div>
        @endforeach
    </div>
</div>

<div>
    <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description <span class="font-normal text-slate-400">(up to 200 words)</span></label>
    <textarea id="description" name="description" rows="5" placeholder="Describe what this meal, vehicle, or service includes..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $inclusion->description ?? '') }}</textarea>
    @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
</div>
