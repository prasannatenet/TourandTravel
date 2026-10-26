@php
    $package = $package ?? null;
    $departureRows = old('availabilities');
    if ($departureRows === null) {
        $departureRows = $package?->availabilities->map(fn ($departure): array => [
            'id' => $departure->id,
            'departure_date' => $departure->departure_date->format('Y-m-d'),
            'seat_limit' => $departure->seat_limit,
            'adult_price_override' => $departure->adult_price_override,
        ])->all() ?? [];
    }
@endphp

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
        <h3 class="text-base font-bold text-slate-900">Package category and style</h3>
        <p class="mt-1 text-sm text-slate-500">Label the trip for easy browsing and identify whether it is a group departure or a private trip.</p>
    </div>
    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-7">
        <div>
            <label for="package-category" class="mb-1.5 block text-sm font-semibold text-slate-700">Category / theme</label>
            <input id="package-category" name="category" list="package-category-options" value="{{ old('category', $package?->category) }}" maxlength="80" placeholder="For example: Family, Honeymoon, Adventure" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
            <datalist id="package-category-options"><option value="Family"><option value="Honeymoon"><option value="Adventure"><option value="Beach"><option value="Nature"><option value="Culture"><option value="Pilgrimage"><option value="Wildlife"><option value="Luxury"><option value="Weekend getaway"></datalist>
            @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="package-tour-type" class="mb-1.5 block text-sm font-semibold text-slate-700">Trip style <span class="text-red-500">*</span></label>
            <select id="package-tour-type" name="tour_type" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                <option value="group" @selected(old('tour_type', $package?->tour_type ?? 'group') === 'group')>Group tour</option>
                <option value="private" @selected(old('tour_type', $package?->tour_type ?? 'group') === 'private')>Private tour</option>
            </select>
            @error('tour_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
        <h3 class="text-base font-bold text-slate-900">Pricing model</h3>
        <p class="mt-1 text-sm text-slate-500">Set traveler prices and optional discount and tax rates. Departure dates can override the adult price.</p>
    </div>
    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-4 sm:px-7">
        <div>
            <label for="base-price" class="mb-1.5 block text-sm font-semibold text-slate-700">Adult price (per person) <span class="text-red-500">*</span></label>
            <div class="relative"><span class="absolute inset-y-0 left-3 flex items-center text-slate-400">₹</span><input id="base-price" type="number" name="base_price" value="{{ old('base_price', $package?->base_price) }}" required min="0" max="99999999.99" step="0.01" class="w-full rounded-xl border border-slate-300 py-2.5 pl-8 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"></div>
            @error('base_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="child-price" class="mb-1.5 block text-sm font-semibold text-slate-700">Child price (optional)</label>
            <div class="relative"><span class="absolute inset-y-0 left-3 flex items-center text-slate-400">₹</span><input id="child-price" type="number" name="child_price" value="{{ old('child_price', $package?->child_price) }}" min="0" max="99999999.99" step="0.01" placeholder="Optional child rate" class="w-full rounded-xl border border-slate-300 py-2.5 pl-8 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"></div>
            @error('child_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="infant-price" class="mb-1.5 block text-sm font-semibold text-slate-700">Infant price (optional)</label>
            <div class="relative"><span class="absolute inset-y-0 left-3 flex items-center text-slate-400">₹</span><input id="infant-price" type="number" name="infant_price" value="{{ old('infant_price', $package?->infant_price) }}" min="0" max="99999999.99" step="0.01" placeholder="Optional infant rate" class="w-full rounded-xl border border-slate-300 py-2.5 pl-8 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"></div>
            @error('infant_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="discount-percentage" class="mb-1.5 block text-sm font-semibold text-slate-700">Discount (%)</label>
            <div class="relative"><input id="discount-percentage" type="number" name="discount_percentage" value="{{ old('discount_percentage', $package?->discount_percentage ?? 0) }}" required min="0" max="100" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 pr-9 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"><span class="absolute inset-y-0 right-3 flex items-center text-slate-400">%</span></div>
            @error('discount_percentage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="tax-percentage" class="mb-1.5 block text-sm font-semibold text-slate-700">Tax (%)</label>
            <div class="relative"><input id="tax-percentage" type="number" name="tax_percentage" value="{{ old('tax_percentage', $package?->tax_percentage ?? 0) }}" required min="0" max="100" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 pr-9 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"><span class="absolute inset-y-0 right-3 flex items-center text-slate-400">%</span></div>
            @error('tax_percentage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-departure-manager>
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
        <div><h3 class="text-base font-bold text-slate-900">Availability and seat limits</h3><p class="mt-1 text-sm text-slate-500">Add bookable departure dates, seat capacity, and an optional date-specific adult price.</p></div>
        <button type="button" data-add-departure class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-plus mr-1"></i> Add departure</button>
    </div>
    <div class="space-y-3 p-5 sm:p-7" data-departure-list>
        @foreach($departureRows as $index => $departure)
            @include('admin.packages._departure-row', ['index' => $index, 'departure' => $departure])
        @endforeach
        <p data-empty-departures class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500 {{ count($departureRows) ? 'hidden' : '' }}">No departure dates yet. Add a date to set its seat capacity.</p>
    </div>
    <template data-departure-template>@include('admin.packages._departure-row', ['index' => '__INDEX__', 'departure' => []])</template>
</section>

@push('scripts')
<script>
(function () {
    var manager = document.querySelector('[data-departure-manager]');
    if (!manager) { return; }
    var list = manager.querySelector('[data-departure-list]');
    var template = manager.querySelector('[data-departure-template]');
    var nextIndex = list.querySelectorAll('[data-departure-row]').length;
    manager.querySelector('[data-add-departure]').addEventListener('click', function () {
        var container = document.createElement('div');
        container.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)).trim();
        list.appendChild(container.firstElementChild);
        updateEmptyState();
    });
    list.addEventListener('click', function (event) {
        var removeButton = event.target.closest('[data-remove-departure]');
        if (removeButton) { removeButton.closest('[data-departure-row]').remove(); updateEmptyState(); }
    });
    function updateEmptyState() {
        list.querySelector('[data-empty-departures]').classList.toggle('hidden', list.querySelectorAll('[data-departure-row]').length > 0);
    }
})();
</script>
@endpush
