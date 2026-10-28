<div class="max-w-4xl rounded-2xl bg-white p-6 shadow-sm">
    <form method="POST" action="{{ $hotel ? route('admin.hotels.update', $hotel) : route('admin.hotels.store') }}" class="space-y-5">
        @csrf
        @if ($hotel)
            @method('PUT')
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($countries->isEmpty())
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                No countries are available. <a class="font-semibold underline" href="{{ route('admin.countries.create') }}">Create a country first</a>.
            </div>
        @endif
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="hotel-country" class="mb-1 block text-sm font-semibold">Country *</label>
                <select id="hotel-country" name="country_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected((string) old('country_id', $countryId) === (string) $country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('country_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="hotel-state" class="mb-1 block text-sm font-semibold">State *</label>
                <select id="hotel-state" name="state_id" required @disabled($states->isEmpty()) class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100">
                    <option value="">Select state</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}" @selected((string) old('state_id', $stateId) === (string) $state->id)>{{ $state->name }}</option>
                    @endforeach
                </select>
                @error('state_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="hotel-city" class="mb-1 block text-sm font-semibold">City *</label>
                <select id="hotel-city" name="city_id" required @disabled($cities->isEmpty()) class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100">
                    <option value="">Select city</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" @selected((string) old('city_id', $cityId) === (string) $city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
                @error('city_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="hotel-name" class="mb-1 block text-sm font-semibold">Hotel name *</label>
                <input id="hotel-name" name="name" value="{{ old('name', $hotel?->name) }}" required maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="hotel-stars" class="mb-1 block text-sm font-semibold">Hotel stars *</label>
                <select id="hotel-stars" name="star_rating" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select star rating</option>
                    @for ($rating = 1; $rating <= 5; $rating++)
                        <option value="{{ $rating }}" @selected((string) old('star_rating', $hotel?->star_rating) === (string) $rating)>{{ $rating }} star{{ $rating === 1 ? '' : 's' }}</option>
                    @endfor
                </select>
                @error('star_rating')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <p class="mb-2 text-sm font-semibold">Hotel cost *</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach (['adult_cost' => 'Adult cost', 'child_cost' => 'Child cost', 'extra_bed_cost' => 'Extra bed cost'] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-1 block text-xs text-slate-500">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="number" min="0" max="99999999.99" step="0.01" value="{{ old($field, $hotel?->{$field}) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            <label for="hotel-address" class="mb-1 block text-sm font-semibold">Full hotel address *</label>
            <textarea id="hotel-address" name="address" rows="4" maxlength="2000" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('address', $hotel?->address) }}</textarea>
            @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <section class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div>
                <h2 class="font-semibold text-slate-900">Hotel account credentials</h2>
                <p class="mt-1 text-sm text-slate-500">The hotel code is generated automatically. Passwords are securely hashed and cannot be viewed later.</p>
            </div>
            @if ($hotel)
                <p class="text-sm text-slate-700"><span class="font-semibold">Hotel code:</span> {{ $hotel->hotel_code }}</p>
            @endif
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="hotel-username" class="mb-1 block text-sm font-semibold">Login username *</label>
                    <input id="hotel-username" name="username" value="{{ old('username', $hotel?->username) }}" required minlength="3" maxlength="50" pattern="[a-z0-9._-]+" autocomplete="username" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Use lowercase letters, numbers, dots, underscores, or hyphens.</p>
                    @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="hotel-password" class="mb-1 block text-sm font-semibold">{{ $hotel?->password ? 'New password (optional)' : 'Password *' }}</label>
                    <input id="hotel-password" name="password" type="password" @required(! $hotel?->password) minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500">{{ $hotel?->password ? 'Leave blank to keep the current password.' : 'Use at least 12 characters.' }}</p>
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="hotel-password-confirmation" class="mb-1 block text-sm font-semibold">Confirm password {{ $hotel?->password ? '(only when changing password)' : '*' }}</label>
                    <input id="hotel-password-confirmation" name="password_confirmation" type="password" @required(! $hotel?->password) autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('password_confirmation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $hotel ? 'Save Hotel' : 'Create Hotel' }}</button>
            <a href="{{ route('admin.hotels.index') }}" class="rounded-lg bg-slate-100 px-5 py-2 text-sm">Cancel</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
(function () {
    var countrySelect = document.getElementById('hotel-country');
    var stateSelect = document.getElementById('hotel-state');
    var citySelect = document.getElementById('hotel-city');
    if (!countrySelect || !stateSelect || !citySelect) {
        return;
    }

    var statesUrl = @json(route('admin.places.states-by-country', ['country' => '__COUNTRY__']));
    var citiesUrl = @json(route('admin.places.cities-by-state', ['state' => '__STATE__']));

    function resetSelect(select, label) {
        select.replaceChildren(new Option(label, ''));
        select.disabled = true;
    }

    function populateSelect(select, items, label) {
        select.replaceChildren(new Option(label, ''));
        items.forEach(function (item) {
            select.add(new Option(item.name, item.id));
        });
        select.disabled = items.length === 0;
    }

    function loadOptions(select, url, label) {
        select.disabled = true;
        fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Unable to load location options.');
                }
                return response.json();
            })
            .then(function (items) {
                populateSelect(select, items, label);
            })
            .catch(function () {
                select.replaceChildren(new Option('Unable to load options. Please try again.', ''));
                select.disabled = false;
            });
    }

    countrySelect.addEventListener('change', function () {
        resetSelect(stateSelect, 'Select state');
        resetSelect(citySelect, 'Select city');
        if (countrySelect.value) {
            loadOptions(stateSelect, statesUrl.replace('__COUNTRY__', encodeURIComponent(countrySelect.value)), 'Select state');
        }
    });

    stateSelect.addEventListener('change', function () {
        resetSelect(citySelect, 'Select city');
        if (stateSelect.value) {
            loadOptions(citySelect, citiesUrl.replace('__STATE__', encodeURIComponent(stateSelect.value)), 'Select city');
        }
    });
})();
</script>
@endpush
