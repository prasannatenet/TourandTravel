<div class="grid gap-6">
    <div>
        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Room type name <span class="text-red-600">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $roomType?->name) }}" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" maxlength="2000" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('description', $roomType?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
        <a href="{{ route('hotel.room-types.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</div>
