@php($errors = $errors ?? new \Illuminate\Support\MessageBag())
<form method="{{ $method ?? 'POST' }}" action="{{ $action ?? '' }}" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
    @csrf
    @if ($method ?? '' !== 'POST')
        @method($method ?? 'PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="name" class="mb-1 block text-xs font-semibold text-slate-500">Associate Name <span class="text-red-500">*</span></label>
            <input id="name" name="name" required value="{{ old('name', $associate?->name) }}" placeholder="e.g. Rajesh Travels" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="username" class="mb-1 block text-xs font-semibold text-slate-500">Username <span class="text-red-500">*</span></label>
            <input id="username" name="username" required value="{{ old('username', $associate?->username) }}" placeholder="e.g. rajesh_travels" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        @if ($method ?? '' === 'POST')
        <div>
            <label for="password" class="mb-1 block text-xs font-semibold text-slate-500">Password <span class="text-red-500">*</span></label>
            <input id="password" name="password" required type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-slate-500">Confirm Password <span class="text-red-500">*</span></label>
            <input id="password_confirmation" name="password_confirmation" required type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('password_confirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        @endif
        <div>
            <label for="phone" class="mb-1 block text-xs font-semibold text-slate-500">Phone <span class="text-red-500">*</span></label>
            <input id="phone" name="phone" required value="{{ old('phone', $associate?->phone) }}" placeholder="e.g. 9876543210" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="mb-1 block text-xs font-semibold text-slate-500">Email</label>
            <input id="email" name="email" value="{{ old('email', $associate?->email) }}" placeholder="e.g. rajesh@example.com" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="address" class="mb-1 block text-xs font-semibold text-slate-500">Address</label>
            <textarea id="address" name="address" rows="2" placeholder="e.g. 123 Main Street" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('address', $associate?->address) }}</textarea>
            @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="city_id" class="mb-1 block text-xs font-semibold text-slate-500">City</label>
            <select id="city_id" name="city_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select city</option>
                @foreach ($cities ?? [] as $city)
                    <option value="{{ $city->id }}" {{ old('city_id', $associate?->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            @error('city_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="commission_rate" class="mb-1 block text-xs font-semibold text-slate-500">Commission Rate (%)</label>
            <input id="commission_rate" name="commission_rate" type="number" value="{{ old('commission_rate', $associate?->commission_rate ?? 0) }}" min="0" max="100" step="0.01" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('commission_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-semibold text-slate-500">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="active" {{ old('status', $associate?->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $associate?->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Associate</button>
        <a href="{{ route('admin.cab-associates.index') }}" class="ml-2 rounded-lg bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Cancel</a>
    </div>
</form>
