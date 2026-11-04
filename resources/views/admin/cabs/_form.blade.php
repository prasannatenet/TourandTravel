@php($errors = $errors ?? new \Illuminate\Support\MessageBag())
<form method="{{ $method ?? 'POST' }}" action="{{ $action ?? '' }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method ?? 'PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Please correct the following errors:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="cab_associate_id" class="mb-1 block text-xs font-semibold text-slate-500">Cab Associate</label>
            <select id="cab_associate_id" name="cab_associate_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">-- Select associate (optional) --</option>
                @foreach ($associates ?? [] as $associate)
                    <option value="{{ $associate->id }}" {{ old('cab_associate_id', $cab?->cab_associate_id) == $associate->id ? 'selected' : '' }}>{{ $associate->name }}</option>
                @endforeach
            </select>
            @error('cab_associate_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="name" class="mb-1 block text-xs font-semibold text-slate-500">Cab Name <span class="text-red-500">*</span></label>
            <input id="name" name="name" required value="{{ old('name', $cab?->name) }}" placeholder="e.g. Toyota Innova" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="registration_number" class="mb-1 block text-xs font-semibold text-slate-500">Registration Number <span class="text-red-500">*</span></label>
            <input id="registration_number" name="registration_number" required value="{{ old('registration_number', $cab?->registration_number) }}" placeholder="e.g. MH12AB3456" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('registration_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="type" class="mb-1 block text-xs font-semibold text-slate-500">Cab Type <span class="text-red-500">*</span></label>
            <input id="type" name="type" required value="{{ old('type', $cab?->type) }}" placeholder="e.g. SUV, Sedan, Hatchback" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="city_id" class="mb-1 block text-xs font-semibold text-slate-500">City <span class="text-red-500">*</span></label>
            <select id="city_id" name="city_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select city</option>
                @foreach ($cities ?? [] as $city)
                    <option value="{{ $city->id }}" {{ old('city_id', $cab?->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            @error('city_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="capacity" class="mb-1 block text-xs font-semibold text-slate-500">Capacity (seats) <span class="text-red-500">*</span></label>
            <input id="capacity" name="capacity" type="number" required value="{{ old('capacity', $cab?->capacity) }}" min="1" max="50" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('capacity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="rate_per_km" class="mb-1 block text-xs font-semibold text-slate-500">Rate per km (Rs.) <span class="text-red-500">*</span></label>
            <input id="rate_per_km" name="rate_per_km" type="number" required value="{{ old('rate_per_km', $cab?->rate_per_km) }}" min="0" step="0.01" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('rate_per_km') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="base_fare" class="mb-1 block text-xs font-semibold text-slate-500">Base Fare (Rs.)</label>
            <input id="base_fare" name="base_fare" type="number" value="{{ old('base_fare', $cab?->base_fare ?? 0) }}" min="0" step="0.01" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('base_fare') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="image" class="mb-1 block text-xs font-semibold text-slate-500">Cab Image <span class="text-red-500">*</span></label>
            <input id="image" name="image" type="file" accept=".png,.jpg,.jpeg,.webp,.avif" @if (!$cab) required @endif class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-semibold text-slate-500">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="active" {{ old('status', $cab?->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $cab?->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="maintenance" {{ old('status', $cab?->status ?? 'active') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
            @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Cab</button>
        <a href="{{ route('admin.cabs.index') }}" class="ml-2 rounded-lg bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Cancel</a>
    </div>
</form>
