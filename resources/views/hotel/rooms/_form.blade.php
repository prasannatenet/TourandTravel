@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">Please correct the following:</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Room name <span class="text-red-600">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $room?->name) }}" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="room_type_id" class="mb-2 block text-sm font-semibold text-slate-700">Room type <span class="text-red-600">*</span></label>
        <select id="room_type_id" name="room_type_id" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            <option value="">Select a room type</option>
            @foreach ($roomTypes as $roomType)
                <option value="{{ $roomType->id }}" @selected((string) old('room_type_id', $room?->room_type_id) === (string) $roomType->id)>{{ $roomType->name }}</option>
            @endforeach
        </select>
        @error('room_type_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="adult_cost" class="mb-2 block text-sm font-semibold text-slate-700">Price per adult (₹) <span class="text-red-600">*</span></label>
        <input id="adult_cost" name="adult_cost" type="number" value="{{ old('adult_cost', $room?->adult_cost) }}" required min="0" max="99999999.99" step="0.01" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        @error('adult_cost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="child_cost" class="mb-2 block text-sm font-semibold text-slate-700">Price per child (0–12 years, ₹) <span class="text-red-600">*</span></label>
        <input id="child_cost" name="child_cost" type="number" value="{{ old('child_cost', $room?->child_cost) }}" required min="0" max="99999999.99" step="0.01" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        @error('child_cost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="extra_bed_cost" class="mb-2 block text-sm font-semibold text-slate-700">Extra bed price (₹) <span class="text-red-600">*</span></label>
        <input id="extra_bed_cost" name="extra_bed_cost" type="number" value="{{ old('extra_bed_cost', $room?->extra_bed_cost) }}" required min="0" max="99999999.99" step="0.01" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        @error('extra_bed_cost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="lg:col-span-2">
        <label for="images" class="mb-2 block text-sm font-semibold text-slate-700">Room images {{ $room ? '' : '*' }}</label>
        <input id="images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple {{ $room ? '' : 'required' }} class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700">
        <p class="mt-1 text-xs text-slate-500">JPG, PNG, or WebP; up to 4 MB per image and 8 images per room.</p>
        @error('images')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('images.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    @if ($room && ! empty($room->images))
        <div class="lg:col-span-2">
            <p class="mb-3 text-sm font-semibold text-slate-700">Current images <span class="font-normal text-slate-500">(check any to remove)</span></p>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($room->images as $imagePath)
                    <label class="relative cursor-pointer overflow-hidden rounded-xl border border-slate-200">
                        <img src="{{ asset('storage/'.$imagePath) }}" alt="{{ $room->name }}" class="h-32 w-full object-cover">
                        <span class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600"><input type="checkbox" name="remove_images[]" value="{{ $imagePath }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500"> Remove image</span>
                    </label>
                @endforeach
            </div>
            @error('remove_images.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif
    <div class="lg:col-span-2">
        <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Room description <span class="text-red-600">*</span></label>
        <textarea id="description" name="description" rows="5" required maxlength="5000" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('description', $room?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="highlights" class="mb-2 block text-sm font-semibold text-slate-700">Highlight points</label>
        <textarea id="highlights" name="highlights" rows="5" maxlength="5000" placeholder="Enter one point per line" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('highlights', $room?->highlights) }}</textarea>
        @error('highlights')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="key_points" class="mb-2 block text-sm font-semibold text-slate-700">Key points</label>
        <textarea id="key_points" name="key_points" rows="5" maxlength="5000" placeholder="Enter one point per line" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('key_points', $room?->key_points) }}</textarea>
        @error('key_points')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="lg:col-span-2">
        <label for="amenities" class="mb-2 block text-sm font-semibold text-slate-700">Things included / in-room amenities</label>
        <textarea id="amenities" name="amenities" rows="4" maxlength="5000" placeholder="For example: Balcony&#10;Air conditioning&#10;Tea and coffee maker" class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('amenities', $room?->amenities) }}</textarea>
        @error('amenities')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5 lg:col-span-2">
        <a href="{{ route('hotel.rooms.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</div>
