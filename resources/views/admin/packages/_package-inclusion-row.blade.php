@php
    $selectedInclusion = $inclusions->firstWhere('id', $packageInclusionRow['package_inclusion_id'] ?? null);
    $customized = (bool) ($packageInclusionRow['id'] ?? false) || old('inclusions.'.$inclusionKey.'.customized', false);
    $displayName = $packageInclusionRow['custom_name'] ?? $selectedInclusion?->name ?? '';
    $displayPlaceFrom = $packageInclusionRow['custom_place_from'] ?? $selectedInclusion?->place_from ?? '';
    $displayPlaceTo = $packageInclusionRow['custom_place_to'] ?? $selectedInclusion?->place_to ?? '';
    $displayDescription = $packageInclusionRow['custom_description'] ?? $selectedInclusion?->description ?? '';
    $displayDuration = $packageInclusionRow['custom_duration_hours'] ?? '';
    $displayScheduledTime = $packageInclusionRow['custom_scheduled_time'] ?? '';
    $customImageOne = $packageInclusionRow['custom_image_one'] ?? null;
    $customImageTwo = $packageInclusionRow['custom_image_two'] ?? null;
    $imageOne = $customImageOne ?: $selectedInclusion?->image_one;
    $imageTwo = $customImageTwo ?: $selectedInclusion?->image_two;
@endphp

<div class="mt-4 border-t border-slate-100 pt-4" data-package-inclusion-row data-inclusion-key="{{ $inclusionKey }}">
    @if(!empty($packageInclusionRow['id']))
        <input type="hidden" name="inclusions[{{ $inclusionKey }}][id]" value="{{ $packageInclusionRow['id'] }}">
    @endif
    <div class="mb-3 flex items-center justify-between gap-3">
        <h5 class="text-sm font-semibold text-slate-800">Service details</h5>
        <p class="text-[11px] text-slate-500">These details apply only to this itinerary entry.</p>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <input type="hidden" name="inclusions[{{ $inclusionKey }}][package_inclusion_id]" value="{{ $packageInclusionRow['package_inclusion_id'] ?? '' }}" data-inclusion-catalog-id>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Package display name <span class="text-red-500">*</span></label>
            <input type="text" name="inclusions[{{ $inclusionKey }}][custom_name]" value="{{ $displayName }}" required maxlength="120" data-inclusion-field="custom_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Ride / activity duration (hours)</label>
            <input type="number" name="inclusions[{{ $inclusionKey }}][custom_duration_hours]" value="{{ $displayDuration }}" min="0.25" max="72" step="0.25" data-inclusion-field="custom_duration_hours" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
            <p class="mt-1 text-[11px] text-slate-400">Example: start at 05:00 + 6 hours ends at 11:00 and adds 6 hours to its scheduled day.</p>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Suggested start time <span class="font-normal text-slate-400">(optional)</span></label>
            <input type="time" name="inclusions[{{ $inclusionKey }}][custom_scheduled_time]" value="{{ $displayScheduledTime ? substr((string) $displayScheduledTime, 0, 5) : '' }}" data-inclusion-field="custom_scheduled_time" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
            <p class="mt-1 text-[11px] text-slate-400">With a duration set, the entry is scheduled automatically. Its start time is used; otherwise it follows the previous item's estimated finish.</p>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Pickup place</label>
            <input type="text" name="inclusions[{{ $inclusionKey }}][custom_place_from]" value="{{ $displayPlaceFrom }}" maxlength="150" data-inclusion-field="custom_place_from" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Drop-off place</label>
            <input type="text" name="inclusions[{{ $inclusionKey }}][custom_place_to]" value="{{ $displayPlaceTo }}" maxlength="150" data-inclusion-field="custom_place_to" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-xs font-medium text-slate-600">Description for this package entry</label>
            <textarea name="inclusions[{{ $inclusionKey }}][custom_description]" rows="2" data-inclusion-field="custom_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">{{ $displayDescription }}</textarea>
            <p class="mt-1 text-[11px] text-slate-400">Each entry has its own description. Add the same service again for another day to give it different details.</p>
        </div>
        @foreach(['custom_image_one' => ['Image 1', $imageOne], 'custom_image_two' => ['Image 2', $imageTwo]] as $field => [$label, $image])
            <div class="rounded-lg border border-slate-200 p-3">
                <label class="mb-2 block text-xs font-medium text-slate-600">{{ $label }}</label>
                @if($image)
                    <div class="mb-2 flex items-center gap-2">
                        <img src="{{ asset('storage/'.$image) }}" alt="" class="h-10 w-14 rounded object-cover">
                        @if($customized)
                            <label class="flex items-center gap-1.5 text-xs text-slate-500">
                                <input type="checkbox" name="inclusions[{{ $inclusionKey }}][remove_{{ $field }}]" value="1" class="rounded border-slate-300 text-red-600">
                                Remove custom image
                            </label>
                        @endif
                    </div>
                @endif
                <input type="file" name="inclusions[{{ $inclusionKey }}][{{ $field }}]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-inclusion-image-input class="block w-full text-xs text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:font-medium file:text-slate-700">
                <p class="mt-1 text-[11px] text-slate-500" data-inclusion-image-name>No new image selected.</p>
                <p id="inclusion-image-help-{{ $inclusionKey }}-{{ $field }}" class="mt-1 text-[11px] text-slate-400">Optional package-specific override. JPG, PNG, or WebP up to 4 MB; leave blank to keep the current image or use the catalog photo.</p>
                @error('inclusions.'.$inclusionKey.'.'.$field)
                    <p class="mt-1 text-xs font-medium text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>
</div>
