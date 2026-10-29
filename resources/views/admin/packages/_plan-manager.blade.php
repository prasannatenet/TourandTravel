@php
    $savedPlanItems = old('plan_items');
    $packageInclusionRows = old('inclusions');
    if ($packageInclusionRows === null) {
        $packageInclusionRows = collect($inclusionItems ?? [])->mapWithKeys(function ($item): array {
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

    $packageInclusionOptions = collect($packageInclusionRows)->mapWithKeys(function (array $item, string|int $key) use ($inclusions): array {
        $inclusion = $inclusions->firstWhere('id', $item['package_inclusion_id'] ?? null);

        return [(string) $key => [
            'name' => $item['custom_name'] ?? $inclusion?->name ?? '',
            'scheduled_time' => $item['custom_scheduled_time'] ?? $inclusion?->scheduled_time ?? '',
            'duration_hours' => $item['custom_duration_hours'] ?? '',
            'place_from' => $item['custom_place_from'] ?? $inclusion?->place_from ?? '',
            'place_to' => $item['custom_place_to'] ?? $inclusion?->place_to ?? '',
            'description' => $item['custom_description'] ?? $inclusion?->description ?? '',
            'image_one' => !empty($item['custom_image_one']) ? $item['custom_image_one'] : $inclusion?->image_one,
            'image_two' => !empty($item['custom_image_two']) ? $item['custom_image_two'] : $inclusion?->image_two,
            'custom_image_one' => $item['custom_image_one'] ?? '',
            'custom_image_two' => $item['custom_image_two'] ?? '',
        ]];
    })->all();

    if ($savedPlanItems === null && isset($package)) {
        $savedPlanItems = $package->planItems->map(fn ($item): array => [
            'kind' => $item->item_type === 'hotel' ? 'hotel' : ($item->package_inclusion_package_id ? 'inclusion' : 'place'),
            'day_number' => $item->day_number,
            'nights' => $item->nights,
            'start_time' => $item->start_time,
            'city_place_id' => $item->city_place_id,
            'hotel_room_id' => $item->hotel_room_id,
            'package_inclusion_item_key' => $item->package_inclusion_package_id ? 'item_'.$item->package_inclusion_package_id : '',
            'additional_price' => $item->additional_price,
            'show_charge_button' => (bool) $item->show_charge_button,
        ])->all();
    }

    $savedPlanItems ??= [];
    $maximumPlanDay = max(1, (int) old('days', $package->days ?? 1));
    $planItemsByDay = collect($savedPlanItems)->groupBy(fn ($item): int => (int) ($item['day_number'] ?? 1));
    $planSectionNumber = ($showInclusionsSection ?? true) || ! isset($package) ? '04' : '05';
    $nextPlanItemIndex = 0;
@endphp

<section id="package-plan" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-package-plan-manager>
    <!-- Section Header -->
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7 bg-slate-50/50">
        <div class="flex items-start gap-3.5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white shadow-sm">
                {{ $planSectionNumber }}
            </span>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-slate-900">Day-by-Day Itinerary Plan</h3>
                    <span id="plan-duration-badge" class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-700">
                        {{ $maximumPlanDay }} {{ \Illuminate\Support\Str::plural('Day', $maximumPlanDay) }} Trip
                    </span>
                </div>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                    Organize sightseeing places, timings, and stops organized cleanly by each day.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @unless(isset($package))
                <a href="{{ route('admin.inclusions.index') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="fa-solid fa-list-check"></i> Manage service catalog
                </a>
            @endunless
            <button type="button" id="quick-add-place" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i> Add Place Stop
            </button>
        </div>
    </div>

    <div class="space-y-5 px-5 py-5 sm:px-7">
        <!-- Destination Cities Status Alert -->
        <div id="destination-cities-alert" class="rounded-xl border border-sky-100 bg-sky-50/70 p-3.5 text-xs leading-relaxed text-sky-900 flex items-start gap-3">
            <i class="fa-solid fa-location-dot mt-0.5 text-sky-600 text-sm shrink-0"></i>
            <div class="flex-1">
                <span class="font-bold text-sky-950">Destination Scoping:</span>
                <span id="destination-cities-text">
                    Select destination cities in Step 03 above. Once selected, their attractions will automatically be available in each day's place list.
                </span>
            </div>
        </div>

        <!-- Validation Errors for Plan Items -->
        @foreach($errors->getMessages() as $field => $messages)
            @if(str_starts_with($field, 'plan_items.'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-700 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    <span>{{ $messages[0] }}</span>
                </div>
            @endif
        @endforeach

        <!-- Day Filter Tabs Bar -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1" id="day-nav-tabs">
                <button type="button" class="day-nav-tab active rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer bg-indigo-600 text-white shadow-sm" data-day-tab="all">
                    All Days <span class="ml-1 opacity-80" id="badge-all-count">({{ count($savedPlanItems) }})</span>
                </button>
                @foreach(range(1, $maximumPlanDay) as $d)
                    @php($dayCount = $planItemsByDay->get($d, collect())->count())
                    <button type="button" class="day-nav-tab rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer bg-slate-100 text-slate-600 hover:bg-slate-200" data-day-tab="{{ $d }}">
                        Day {{ $d }} <span class="ml-1 text-[11px] font-semibold text-slate-400" data-day-tab-badge="{{ $d }}">({{ $dayCount }})</span>
                    </button>
                @endforeach
            </div>

            <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
                <span id="plan-total-activities-count">{{ count($savedPlanItems) }} total stops</span>
            </div>
        </div>

        <!-- Master Days Container (Holds each Day's block) -->
        <div id="plan-days-container" class="space-y-6">
            @foreach(range(1, $maximumPlanDay) as $d)
                <div class="day-card rounded-2xl border border-slate-200 bg-slate-50/50 p-4 sm:p-5 transition" data-day-card="{{ $d }}">
                    <!-- Day Header -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3.5 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-xs font-black text-white shadow-sm">
                                D{{ $d }}
                            </span>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 tracking-tight">Day {{ $d }} Itinerary</h4>
                                <p class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span data-day-header-count="{{ $d }}">{{ $planItemsByDay->get($d, collect())->count() }} {{ \Illuminate\Support\Str::plural('activity', $planItemsByDay->get($d, collect())->count()) }}</span>
                                    <span>&bull;</span>
                                    <span data-day-header-time="{{ $d }}">Estimated time updating...</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" class="btn-add-place-to-day inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-bold shadow-sm transition cursor-pointer" data-day="{{ $d }}">
                                <i class="fa-solid fa-plus text-[10px]"></i> Add Place to Day {{ $d }}
                            </button>
                            <button type="button" class="btn-add-hotel-to-day inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-white px-2.5 py-1.5 text-xs font-bold text-blue-700 shadow-sm transition hover:bg-blue-50" data-day="{{ $d }}">
                                <i class="fa-solid fa-hotel text-[10px]"></i> Add Hotel Check-in
                            </button>
                            <select data-day-inclusion-catalog aria-label="Choose a service for Day {{ $d }}" class="max-w-40 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700">
                                <option value="">Choose service...</option>
                                @foreach($inclusions as $inclusion)
                                    <option value="{{ $inclusion->id }}" data-default-name="{{ $inclusion->name }}" data-default-scheduled-time="{{ $inclusion->scheduled_time ? substr((string) $inclusion->scheduled_time, 0, 5) : '' }}" data-default-place-from="{{ $inclusion->place_from }}" data-default-place-to="{{ $inclusion->place_to }}" data-default-description="{{ $inclusion->description }}">{{ $inclusion->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-add-inclusion-to-day inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-2.5 py-1.5 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50" data-day="{{ $d }}" title="Add service to Day {{ $d }}" @disabled($inclusions->isEmpty())>
                                <i class="fa-solid fa-utensils text-[10px]"></i> Add Service
                            </button>
                        </div>
                    </div>

                    <!-- Items container for this specific day -->
                    <div class="day-items-list space-y-3" data-day-items="{{ $d }}">
                        @foreach($planItemsByDay->get($d, collect()) as $planItem)
                            @php($planItemRowIndex = $nextPlanItemIndex++)
                            @include('admin.packages._plan-item-row', [
                                'rowIndex' => $planItemRowIndex,
                                'rowNumber' => $loop->iteration,
                                'rowType' => ($planItem['kind'] ?? 'place'),
                                'planItem' => $planItem,
                                'places' => $places,
                                'hotels' => $hotels,
                                'hotelRooms' => $hotelRooms,
                                'packageInclusionOptions' => $packageInclusionOptions,
                                'maxDays' => $maximumPlanDay,
                            ])
                        @endforeach
                    </div>

                    <!-- Empty state for this day -->
                    <div class="day-empty-state rounded-xl border border-dashed border-slate-300 bg-white/70 py-6 px-4 text-center mt-3 {{ $planItemsByDay->get($d, collect())->isNotEmpty() ? 'hidden' : '' }}" data-day-empty="{{ $d }}">
                        <span class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 mb-1.5">
                            <i class="fa-regular fa-calendar-plus text-xs"></i>
                        </span>
                        <p class="text-xs font-bold text-slate-700">No activities scheduled for Day {{ $d }} yet</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Click "Add Place to Day {{ $d }}" to schedule the first stop.</p>
                        <button type="button" class="btn-add-place-to-day mt-2.5 inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 text-xs font-bold transition cursor-pointer" data-day="{{ $d }}">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add First Stop to Day {{ $d }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Hidden Global Flat List for Templates / Orphan fallbacks -->
        <div id="plan-item-list" class="hidden"></div>

        <!-- Estimated time summary cards -->
        <section class="rounded-xl border border-slate-200 bg-slate-50 p-4" aria-live="polite">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-clock text-indigo-600"></i>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800">Estimated Schedule Summary by Day</h4>
                </div>
                <span class="text-[11px] text-slate-400">Calculated from place visit durations</span>
            </div>
            <div id="day-hours-summary" class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-4"></div>
        </section>

        <!-- Template for Place Stop -->
        <template id="plan-place-item-template">
            @include('admin.packages._plan-item-row', [
                'rowIndex' => '__INDEX__',
                'rowNumber' => '__ROW_NUMBER__',
                'planItem' => ['day_number' => '__TARGET_DAY__'],
                'rowType' => 'place',
                'places' => $places,
                'hotels' => $hotels,
                'hotelRooms' => $hotelRooms,
                'packageInclusionOptions' => $packageInclusionOptions,
                'maxDays' => $maximumPlanDay,
            ])
        </template>

        <!-- Template for Inclusion Stop -->
        <template id="plan-inclusion-item-template">
            @include('admin.packages._plan-item-row', [
                'rowIndex' => '__INDEX__',
                'rowNumber' => '__ROW_NUMBER__',
                'planItem' => ['day_number' => '__TARGET_DAY__'],
                'rowType' => 'inclusion',
                'places' => $places,
                'hotels' => $hotels,
                'hotelRooms' => $hotelRooms,
                'packageInclusionOptions' => $packageInclusionOptions,
                'maxDays' => $maximumPlanDay,
            ])
        </template>

        <!-- Template for Hotel Check-in -->
        <template id="plan-hotel-item-template">
            @include('admin.packages._plan-item-row', [
                'rowIndex' => '__INDEX__',
                'rowNumber' => '__ROW_NUMBER__',
                'planItem' => ['day_number' => '__TARGET_DAY__'],
                'rowType' => 'hotel',
                'places' => $places,
                'hotels' => $hotels,
                'hotelRooms' => $hotelRooms,
                'packageInclusionOptions' => $packageInclusionOptions,
                'maxDays' => $maximumPlanDay,
            ])
        </template>

        <!-- Template for Dynamic Day Card -->
        <template id="day-card-template">
            <div class="day-card rounded-2xl border border-slate-200 bg-slate-50/50 p-4 sm:p-5 transition" data-day-card="__DAY__">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3.5 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-xs font-black text-white shadow-sm">
                            D__DAY__
                        </span>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 tracking-tight">Day __DAY__ Itinerary</h4>
                            <p class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                                <span data-day-header-count="__DAY__">0 activities</span>
                                <span>&bull;</span>
                                <span data-day-header-time="__DAY__">No plan items</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-add-place-to-day inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-bold shadow-sm transition cursor-pointer" data-day="__DAY__">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add Place to Day __DAY__
                        </button>
                        <button type="button" class="btn-add-hotel-to-day inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-white px-2.5 py-1.5 text-xs font-bold text-blue-700 shadow-sm transition hover:bg-blue-50" data-day="__DAY__">
                            <i class="fa-solid fa-hotel text-[10px]"></i> Add Hotel Check-in
                        </button>
                        <select data-day-inclusion-catalog aria-label="Choose a service for Day __DAY__" class="max-w-40 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700">
                            <option value="">Choose service...</option>
                            @foreach($inclusions as $inclusion)
                                <option value="{{ $inclusion->id }}" data-default-name="{{ $inclusion->name }}" data-default-scheduled-time="{{ $inclusion->scheduled_time ? substr((string) $inclusion->scheduled_time, 0, 5) : '' }}" data-default-place-from="{{ $inclusion->place_from }}" data-default-place-to="{{ $inclusion->place_to }}" data-default-description="{{ $inclusion->description }}">{{ $inclusion->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn-add-inclusion-to-day inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-2.5 py-1.5 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50" data-day="__DAY__" title="Add service to Day __DAY__" @disabled($inclusions->isEmpty())>
                            <i class="fa-solid fa-utensils text-[10px]"></i> Add Service
                        </button>
                    </div>
                </div>

                <div class="day-items-list space-y-3" data-day-items="__DAY__"></div>
                <div class="day-empty-state rounded-xl border border-dashed border-slate-300 bg-white/70 py-6 px-4 text-center mt-3" data-day-empty="__DAY__">
                    <span class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 mb-1.5">
                        <i class="fa-regular fa-calendar-plus text-xs"></i>
                    </span>
                    <p class="text-xs font-bold text-slate-700">No activities scheduled for Day __DAY__ yet</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Click "Add Place to Day __DAY__" to schedule the first stop.</p>
                    <button type="button" class="btn-add-place-to-day mt-2.5 inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 text-xs font-bold transition cursor-pointer" data-day="__DAY__">
                        <i class="fa-solid fa-plus text-[10px]"></i> Add First Stop to Day __DAY__
                    </button>
                    <button type="button" class="btn-add-hotel-to-day mt-2.5 ml-1.5 inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-bold text-blue-700 transition hover:bg-blue-50" data-day="__DAY__">
                        <i class="fa-solid fa-hotel text-[10px]"></i> Add Hotel Check-in
                    </button>
                </div>
            </div>
        </template>

        <!-- Total Price Summary Card -->
        <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/70 via-white to-indigo-50/30 p-4 sm:p-5">
            <div class="mb-4 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm"><i class="fa-solid fa-calculator text-sm"></i></span>
                <div>
                    <h4 class="text-sm font-bold text-slate-900">Estimated Total per Traveler</h4>
                    <p class="text-xs text-slate-500">Recalculates automatically as you configure base price, attractions, and extras.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 border-t border-indigo-100 pt-3 sm:grid-cols-2 lg:grid-cols-5 sm:items-end">
                <div>
                    <p class="text-xs text-slate-500 font-medium">Base Adult Price</p>
                    <p id="plan-base-total" class="mt-1 text-sm font-bold text-slate-800">₹0.00</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Planned Place Fees & Extras</p>
                    <p id="plan-added-total" class="mt-1 text-sm font-bold text-slate-800">₹0.00</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Discount</p>
                    <p id="plan-discount-total" class="mt-1 text-sm font-bold text-emerald-700">—</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Tax</p>
                    <p id="plan-tax-total" class="mt-1 text-sm font-bold text-slate-800">—</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-indigo-700">Estimated Total</p>
                    <p id="plan-grand-total" aria-live="polite" class="mt-1 text-2xl font-black tracking-tight text-indigo-700">₹0.00</p>
                </div>
            </div>
        </div>
    </div>
</section>

@if($showInclusionsSection ?? true)
<!-- Section 05: Package Inclusions -->
<section id="package-inclusions" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm mt-5">
    <div class="flex items-start gap-3.5 border-b border-slate-100 px-5 py-5 sm:px-7 bg-slate-50/50">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-sm">
            05
        </span>
        <div>
            <h3 class="text-base font-bold text-slate-900">Package Inclusions & Amenities</h3>
            <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                Add meals, vehicles, flights, hotels, or other services included with this package.
            </p>
        </div>
    </div>

    <div class="p-5 sm:p-7">
        @include('admin.packages._inclusion-picker', [
            'inclusions' => $inclusions,
            'packageInclusionRows' => $packageInclusionRows,
        ])
    </div>
</section>
@endif

@push('scripts')
<script>
(function () {
    var manager = document.getElementById('package-plan');
    if (!manager) return;

    var daysContainer = manager.querySelector('#plan-days-container');
    var placeTemplate = manager.querySelector('#plan-place-item-template');
    var inclusionTemplate = manager.querySelector('#plan-inclusion-item-template');
    var hotelTemplate = manager.querySelector('#plan-hotel-item-template');
    var dayCardTemplate = manager.querySelector('#day-card-template');
    var basePriceInput = document.getElementById('base-price');
    var daysInput = document.getElementById('package-days');
    var nightsInput = document.getElementById('package-nights');
    var quickAddPlaceBtn = manager.querySelector('#quick-add-place');
    var dayNavTabs = manager.querySelector('#day-nav-tabs');

    var inclusionList = document.querySelector('#package-inclusion-list');
    var packageInclusionTemplate = document.querySelector('#package-inclusion-template');
    var emptyPackageInclusions = document.querySelector('#empty-package-inclusions');
    var packageForm = manager.closest('form');

    var nextInclusionIndex = 0;
    var nextIndex = Array.from(document.querySelectorAll('[data-plan-index]')).reduce(function (highest, row) {
        return Math.max(highest, Number(row.dataset.planIndex) + 1);
    }, 0);

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 }).format(amount);
    }

    function getSelectedCityCheckboxes() {
        return Array.from(document.querySelectorAll('.package-city:checked'));
    }

    function selectedCityIds() {
        return new Set(getSelectedCityCheckboxes().map(cb => cb.value));
    }

    function updateDestinationCitiesAlert() {
        var alertBox = document.getElementById('destination-cities-alert');
        var textSpan = document.getElementById('destination-cities-text');
        var checkedCities = getSelectedCityCheckboxes();

        if (checkedCities.length === 0) {
            if (alertBox) {
                alertBox.className = 'rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-xs leading-relaxed text-amber-900 flex items-start gap-3';
            }
            if (textSpan) {
                textSpan.innerHTML = '<strong class="text-amber-950 font-bold">No destination cities selected yet.</strong> Please check one or more cities in <a href="#package-destinations" class="underline font-bold text-amber-900">Step 03 (Destinations)</a> above to populate attractions for your itinerary stops.';
            }
        } else {
            var cityNames = checkedCities.map(cb => {
                var label = cb.closest('label');
                return label ? label.textContent.trim().split(/\s+/)[0] : 'City';
            }).filter((v, i, a) => a.indexOf(v) === i);

            if (alertBox) {
                alertBox.className = 'rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 text-xs leading-relaxed text-emerald-900 flex items-start gap-3';
            }
            if (textSpan) {
                textSpan.innerHTML = '<strong class="font-bold text-emerald-950">Active Destination Cities:</strong> <span class="font-semibold">' + cityNames.join(', ') + '</span>. Attractions below are filtered to these cities.';
            }
        }
    }

    function refreshPlaceOptions() {
        var allowedCities = selectedCityIds();
        updateDestinationCitiesAlert();

        document.querySelectorAll('[data-plan-place]').forEach(function (select) {
            var hasAnyAllowed = false;
            var currentVal = select.value;

            // Loop through optgroups
            select.querySelectorAll('optgroup').forEach(function (group) {
                var groupHasAllowed = false;
                group.querySelectorAll('option').forEach(function (opt) {
                    if (!opt.value) return;
                    var isAllowed = allowedCities.size === 0 || allowedCities.has(opt.dataset.cityId || '');
                    opt.hidden = !isAllowed;
                    opt.disabled = !isAllowed;
                    if (isAllowed) {
                        groupHasAllowed = true;
                        hasAnyAllowed = true;
                    }
                });
                group.hidden = !groupHasAllowed;
            });

            // If selected option became disabled, reset select
            var selectedOpt = select.selectedOptions[0];
            if (selectedOpt && selectedOpt.disabled) {
                select.value = '';
            }

            updateEstimatedEndTime(select.closest('[data-plan-row]'));
        });

        updateTotals();
    }

    function refreshHotelRoomOptions() {
        var allowedCities = selectedCityIds();

        document.querySelectorAll('[data-plan-hotel-city]').forEach(function (citySelect) {
            var row = citySelect.closest('[data-plan-row]');
            var hotelSelect = row?.querySelector('[data-plan-hotel]');
            var roomSelect = row?.querySelector('[data-plan-hotel-room]');
            if (!hotelSelect || !roomSelect) return;

            var availableCityOptions = Array.from(citySelect.options).filter(function (option) {
                if (!option.value) return false;

                var isAllowed = allowedCities.has(option.dataset.cityId || '');
                option.hidden = !isAllowed;
                option.disabled = !isAllowed;
                return isAllowed;
            });

            if (citySelect.selectedOptions[0]?.disabled) {
                citySelect.value = '';
            }

            if (!citySelect.value && availableCityOptions.length === 1) {
                citySelect.value = availableCityOptions[0].value;
            }

            var selectedCityId = citySelect.value;
            var availableHotelOptions = Array.from(hotelSelect.options).filter(function (option) {
                if (!option.value) return false;

                var isAllowed = option.dataset.cityId === selectedCityId && allowedCities.has(option.dataset.cityId || '');
                option.hidden = !isAllowed;
                option.disabled = !isAllowed;
                return isAllowed;
            });

            if (hotelSelect.selectedOptions[0]?.disabled) {
                hotelSelect.value = '';
            }

            var selectedHotelId = hotelSelect.value;
            Array.from(roomSelect.options).forEach(function (option) {
                if (!option.value) return;

                var isAllowed = option.dataset.hotelId === selectedHotelId
                    && option.dataset.cityId === selectedCityId
                    && allowedCities.has(option.dataset.cityId || '');
                option.hidden = !isAllowed;
                option.disabled = !isAllowed || option.dataset.hasCustomerRates !== '1';
            });

            var selectedOption = roomSelect.selectedOptions[0];
            if (selectedOption?.disabled) {
                roomSelect.value = '';
            }

            if (!selectedHotelId && availableHotelOptions.length === 1) {
                hotelSelect.value = availableHotelOptions[0].value;
                refreshHotelRoomOptionsForRow(row, selectedCityId, hotelSelect.value);
            }

            updateHotelRateSummary(row);
        });

        updateTotals();
    }

    function refreshHotelRoomOptionsForRow(row, selectedCityId, selectedHotelId) {
        var roomSelect = row.querySelector('[data-plan-hotel-room]');
        if (!roomSelect) return;

        Array.from(roomSelect.options).forEach(function (option) {
            if (!option.value) return;

            var isAllowed = option.dataset.hotelId === selectedHotelId
                && option.dataset.cityId === selectedCityId;
            option.hidden = !isAllowed;
            option.disabled = !isAllowed || option.dataset.hasCustomerRates !== '1';
        });
    }

    function updateDayTabsAndCards() {
        var numDays = Math.max(1, Number.parseInt(daysInput?.value || '1', 10) || 1);

        // Update badge
        var badge = manager.querySelector('#plan-duration-badge');
        if (badge) badge.textContent = numDays + (numDays === 1 ? ' Day Trip' : ' Days Trip');

        // Ensure Day Cards exist up to numDays
        for (var d = 1; d <= numDays; d++) {
            var existingCard = daysContainer.querySelector('[data-day-card="' + d + '"]');
            if (!existingCard && dayCardTemplate) {
                var markup = dayCardTemplate.innerHTML.replaceAll('__DAY__', String(d));
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = markup.trim();
                daysContainer.appendChild(tempDiv.firstElementChild);
            } else if (existingCard) {
                existingCard.style.display = '';
            }
        }

        // Hide cards beyond numDays if they are empty
        daysContainer.querySelectorAll('[data-day-card]').forEach(function (card) {
            var dayNum = Number.parseInt(card.dataset.dayCard, 10);
            if (dayNum > numDays) {
                var itemsCount = card.querySelectorAll('[data-plan-row]').length;
                if (itemsCount === 0) {
                    card.style.display = 'none';
                }
            }
        });

        // Rebuild Day Nav Tabs
        renderDayNavTabs(numDays);

        // Update select options for "Move to Day" in all rows
        document.querySelectorAll('[data-plan-day]').forEach(function (daySelect) {
            var currentDayVal = Number.parseInt(daySelect.value, 10) || 1;
            daySelect.innerHTML = '';
            for (var i = 1; i <= Math.max(numDays, currentDayVal); i++) {
                var opt = document.createElement('option');
                opt.value = String(i);
                opt.textContent = 'Day ' + i;
                opt.selected = (i === currentDayVal);
                daySelect.appendChild(opt);
            }
        });
    }

    function renderDayNavTabs(numDays) {
        if (!dayNavTabs) return;

        var activeTab = dayNavTabs.querySelector('.day-nav-tab.active')?.dataset.dayTab || 'all';
        var totalRows = document.querySelectorAll('[data-plan-row]').length;

        var html = '<button type="button" class="day-nav-tab ' + (activeTab === 'all' ? 'active bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') + ' rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer" data-day-tab="all">All Days <span class="ml-1 opacity-80">(' + totalRows + ')</span></button>';

        for (var d = 1; d <= numDays; d++) {
            var dayCount = document.querySelectorAll('[data-day-items="' + d + '"] [data-plan-row]').length;
            var isCurrent = (activeTab === String(d));
            html += '<button type="button" class="day-nav-tab ' + (isCurrent ? 'active bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') + ' rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer" data-day-tab="' + d + '">Day ' + d + ' <span class="ml-1 text-[11px] font-semibold opacity-75">(' + dayCount + ')</span></button>';
        }

        dayNavTabs.innerHTML = html;
    }

    function filterByDayTab(targetDay) {
        if (!dayNavTabs) return;

        dayNavTabs.querySelectorAll('.day-nav-tab').forEach(function (btn) {
            var isSelected = btn.dataset.dayTab === targetDay;
            btn.classList.toggle('active', isSelected);
            btn.classList.toggle('bg-indigo-600', isSelected);
            btn.classList.toggle('text-white', isSelected);
            btn.classList.toggle('shadow-sm', isSelected);
            btn.classList.toggle('bg-slate-100', !isSelected);
            btn.classList.toggle('text-slate-600', !isSelected);
        });

        daysContainer.querySelectorAll('[data-day-card]').forEach(function (card) {
            if (targetDay === 'all' || card.dataset.dayCard === targetDay) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function updateDayHoursSummary() {
        var summary = manager.querySelector('#day-hours-summary');
        var numberOfDays = Math.max(1, Number.parseInt(daysInput?.value || '1', 10) || 1);
        var totals = Array.from({ length: numberOfDays }, function () {
            return { minutes: 0, items: 0, missing: 0 };
        });

        document.querySelectorAll('[data-plan-row]').forEach(function (row) {
            var dayNum = Number.parseInt(row.querySelector('[data-plan-day]')?.value || '1', 10) || 1;
            if (dayNum > numberOfDays) return;
            var dayTotal = totals[dayNum - 1];
            if (!dayTotal) return;

            dayTotal.items++;
            var durationHours = 0;
            if (row.dataset.planKind === 'place') {
                durationHours = Number.parseFloat(row.querySelector('[data-plan-place]')?.selectedOptions[0]?.dataset.duration || '0') || 0;
            } else if (row.dataset.planKind === 'inclusion') {
                durationHours = Number.parseFloat(row.querySelector('[data-plan-inclusion-select]')?.selectedOptions[0]?.dataset.durationHours || '0') || 0;
            }

            if (durationHours > 0) {
                dayTotal.minutes += Math.round(durationHours * 60);
            } else {
                dayTotal.missing++;
            }
        });

        // Update each Day Card's header stats & empty state
        for (var d = 1; d <= numberOfDays; d++) {
            var dayItemsContainer = daysContainer.querySelector('[data-day-items="' + d + '"]');
            var emptyState = daysContainer.querySelector('[data-day-empty="' + d + '"]');
            var headerCount = daysContainer.querySelector('[data-day-header-count="' + d + '"]');
            var headerTime = daysContainer.querySelector('[data-day-header-time="' + d + '"]');

            var rowCount = dayItemsContainer ? dayItemsContainer.querySelectorAll('[data-plan-row]').length : 0;
            if (emptyState) emptyState.classList.toggle('hidden', rowCount > 0);
            if (headerCount) headerCount.textContent = rowCount + ' ' + (rowCount === 1 ? 'activity' : 'activities');

            var totalMins = totals[d - 1]?.minutes || 0;
            var hours = Math.floor(totalMins / 60);
            var mins = totalMins % 60;
            var formattedTime = totalMins > 0 ? (hours ? hours + 'h ' : '') + (mins ? mins + 'm' : '') + ' total' : 'Duration unstated';
            if (headerTime) headerTime.textContent = formattedTime;
        }

        // Update overall activities count
        var totalActivitiesCount = document.querySelectorAll('[data-plan-row]').length;
        var totalActivitiesElem = document.getElementById('plan-total-activities-count');
        if (totalActivitiesElem) {
            totalActivitiesElem.textContent = totalActivitiesCount + ' total ' + (totalActivitiesCount === 1 ? 'stop' : 'stops');
        }

        // Render summary grid
        if (summary) {
            summary.innerHTML = '';
            totals.forEach(function (day, index) {
                var card = document.createElement('div');
                card.className = 'rounded-xl border border-slate-200 bg-white p-3 shadow-xs';
                var hours = Math.floor(day.minutes / 60);
                var mins = day.minutes % 60;
                var timeStr = day.minutes > 0 ? (hours ? hours + ' hr' + (hours === 1 ? '' : 's') + ' ' : '') + (mins ? mins + ' min' : '') : (day.items ? 'Flexible timing' : 'No activities');

                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">Day ${index + 1}</span>
                        <span class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">${day.items} ${day.items === 1 ? 'stop' : 'stops'}</span>
                    </div>
                    <p class="mt-1 text-sm font-black text-slate-900">${timeStr}</p>
                `;
                summary.appendChild(card);
            });
        }
    }

    function updateTotals() {
        var basePrice = Number.parseFloat(basePriceInput?.value || '0') || 0;
        var addedPrice = 0;
        var discountPercentage = Number.parseFloat(document.getElementById('discount-percentage')?.value || '0') || 0;
        var taxPercentage = Number.parseFloat(document.getElementById('tax-percentage')?.value || '0') || 0;

        document.querySelectorAll('[data-plan-row]').forEach(function (row) {
            var placeSelect = row.querySelector('[data-plan-place]');
            var selectedPlace = placeSelect?.selectedOptions[0];
            var placePrice = row.dataset.planKind === 'place' ? (Number.parseFloat(selectedPlace?.dataset.price || '0') || 0) : 0;
            var extraPrice = Number.parseFloat(row.querySelector('[data-plan-extra-price]')?.value || '0') || 0;
            var roomSelect = row.querySelector('[data-plan-hotel-room]');
            var adultRoomPrice = Number.parseFloat(roomSelect?.selectedOptions[0]?.dataset.customerAdultPrice || '0') || 0;
            var hotelNightsInput = row.querySelector('[data-plan-hotel-nights]');
            var hotelNights = Number.parseInt(hotelNightsInput?.value || '1', 10) || 1;
            var hotelPrice = row.dataset.planKind === 'hotel' ? adultRoomPrice * hotelNights : 0;
            addedPrice += placePrice + extraPrice + hotelPrice;
        });

        manager.querySelector('#plan-base-total').textContent = formatCurrency(basePrice);
        manager.querySelector('#plan-added-total').textContent = formatCurrency(addedPrice);
        var subtotal = basePrice + addedPrice;
        var discount = subtotal * discountPercentage / 100;
        var afterDiscount = subtotal - discount;
        var tax = afterDiscount * taxPercentage / 100;
        manager.querySelector('#plan-discount-total').textContent = discount > 0 ? '−' + formatCurrency(discount) : '—';
        manager.querySelector('#plan-tax-total').textContent = tax > 0 ? formatCurrency(tax) : '—';
        manager.querySelector('#plan-grand-total').textContent = formatCurrency(afterDiscount + tax);
    }

    function updateHotelRateSummary(row) {
        if (!row) return;

        var select = row.querySelector('[data-plan-hotel-room]');
        var summary = row.querySelector('[data-hotel-rate-summary]');
        if (!select || !summary) return;

        var option = select.selectedOptions[0];
        summary.innerHTML = '';
        if (!select.value) {
            summary.textContent = 'Choose a city, hotel, and room with customer rates.';
            return;
        }

        var nightsInput = row.querySelector('[data-plan-hotel-nights]');
        var hotelNights = Number.parseInt(nightsInput?.value || '1', 10) || 1;
        var adultPrice = Number(option.dataset.customerAdultPrice || 0);
        var adultPackagePrice = adultPrice * hotelNights;
        [
            ['adult', option.dataset.customerAdultPrice],
            ['child', option.dataset.customerChildPrice],
            ['extra bed', option.dataset.customerExtraBedPrice]
        ].forEach(function (price) {
            var item = document.createElement('span');
            item.className = 'rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-slate-700';
            item.textContent = '₹' + Number(price[1]).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' per ' + price[0] + '/night';
            summary.appendChild(item);
        });

        var packagePrice = document.createElement('span');
        packagePrice.className = 'w-full font-semibold text-blue-800';
        packagePrice.textContent = 'Included in adult package price: ₹' + adultPackagePrice.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' per adult for ' + hotelNights + ' night' + (hotelNights === 1 ? '' : 's') + ' (₹' + (adultPackagePrice * 2).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' for 2 adults).';
        summary.appendChild(packagePrice);
    }

    function updateEstimatedEndTime(row) {
        if (!row) return;

        var placeSelect = row.querySelector('[data-plan-place]');
        var startTimeInput = row.querySelector('[data-plan-time]');
        var endTimePanel = row.querySelector('[data-estimated-end-time]');
        var startTimeValue = row.querySelector('[data-estimated-start-time-value]');
        var endTimeValue = row.querySelector('[data-estimated-end-time-value]');
        var endTimeHelp = row.querySelector('[data-estimated-end-time-help]');
        if (!endTimePanel) return;

        var selectedPlace = placeSelect?.selectedOptions[0];
        var startTime = startTimeInput?.value;
        var inclusionSelect = row.querySelector('[data-plan-inclusion-select]');
        var durationHours = placeSelect
            ? Number.parseFloat(selectedPlace?.dataset.duration || '0')
            : (Number.parseFloat(inclusionSelect?.selectedOptions[0]?.dataset.durationHours || '0') || 0);

        if ((placeSelect && !placeSelect.value) || !startTime || durationHours <= 0) {
            endTimePanel.classList.add('hidden');
            if (startTimeValue) startTimeValue.textContent = '';
            if (endTimeValue) endTimeValue.textContent = '';
            if (endTimeHelp) endTimeHelp.textContent = '';
            return;
        }

        var timeParts = startTime.split(':').map(Number);
        var startHour = timeParts[0];
        var startDisplayHour = startHour % 12 || 12;
        var startMeridiem = startHour >= 12 ? 'PM' : 'AM';
        var totalMinutes = (timeParts[0] * 60) + timeParts[1] + Math.round(durationHours * 60);
        var daysLater = Math.floor(totalMinutes / 1440);
        var minutesInDay = totalMinutes % 1440;
        var endHour = Math.floor(minutesInDay / 60);
        var endMinute = minutesInDay % 60;
        var displayHour = endHour % 12 || 12;
        var meridiem = endHour >= 12 ? 'PM' : 'AM';
        var nextDayLabel = daysLater > 0 ? ' (+' + daysLater + 'd)' : '';
        var formattedDuration = String(Number(durationHours.toFixed(1))) + ' hrs';

        startTimeValue.textContent = startDisplayHour + ':' + String(timeParts[1]).padStart(2, '0') + ' ' + startMeridiem;
        endTimeValue.textContent = displayHour + ':' + String(endMinute).padStart(2, '0') + ' ' + meridiem + nextDayLabel;
        if (endTimeHelp) {
            endTimeHelp.textContent = 'Approx. visit duration: ' + formattedDuration;
        }
        endTimePanel.classList.remove('hidden');
    }

    function calculateNextStartTimeForDay(targetDay) {
        var dayContainer = daysContainer.querySelector('[data-day-items="' + targetDay + '"]');
        if (!dayContainer) return '09:00';

        var rows = Array.from(dayContainer.querySelectorAll('[data-plan-row]'));
        if (rows.length === 0) return '09:00';

        // Find latest end time of existing items in this day
        var latestEndMinutes = 9 * 60; // 09:00 AM default
        rows.forEach(function (row) {
            var timeVal = row.querySelector('[data-plan-time]')?.value || '09:00';
            var parts = timeVal.split(':').map(Number);
            var startMins = (parts[0] * 60) + parts[1];

            var durationHours = 0;
            if (row.dataset.planKind === 'place') {
                durationHours = Number.parseFloat(row.querySelector('[data-plan-place]')?.selectedOptions[0]?.dataset.duration || '2') || 2;
            } else if (row.dataset.planKind === 'inclusion') {
                durationHours = Number.parseFloat(row.querySelector('[data-plan-inclusion-select]')?.selectedOptions[0]?.dataset.durationHours || '1.5') || 1.5;
            }

            var endMins = startMins + Math.round(durationHours * 60) + 30; // Add 30 mins buffer for travel/transit
            if (endMins > latestEndMinutes) latestEndMinutes = endMins;
        });

        // Cap to reasonable day hours (e.g. max 20:00)
        latestEndMinutes = Math.min(latestEndMinutes, 20 * 60);
        var h = Math.floor(latestEndMinutes / 60);
        var m = Math.floor((latestEndMinutes % 60) / 15) * 15; // round to nearest 15 mins
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    }

    function addPlanItem(kind, targetDay, inclusionKey) {
        targetDay = Number.parseInt(targetDay || '1', 10) || 1;
        var dayContainer = daysContainer.querySelector('[data-day-items="' + targetDay + '"]');
        if (!dayContainer) {
            updateDayTabsAndCards();
            dayContainer = daysContainer.querySelector('[data-day-items="' + targetDay + '"]');
        }

        var template = kind === 'inclusion' ? inclusionTemplate : (kind === 'hotel' ? hotelTemplate : placeTemplate);
        if (!template) return;

        var currentIndex = nextIndex++;
        var totalRows = document.querySelectorAll('[data-plan-row]').length + 1;

        var markup = template.innerHTML
            .replaceAll('__INDEX__', String(currentIndex))
            .replaceAll('__ROW_NUMBER__', String(totalRows))
            .replaceAll('__TARGET_DAY__', String(targetDay));

        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = markup.trim();
        var row = tempDiv.firstElementChild;
        if (kind === 'inclusion') {
            refreshInclusionOptions(row);
        }

        // Set Day & Time
        var daySelect = row.querySelector('[data-plan-day]');
        if (daySelect) daySelect.value = String(targetDay);

        var startTime = kind === 'hotel' ? '14:00' : calculateNextStartTimeForDay(targetDay);
        var timeInput = row.querySelector('[data-plan-time]');
        if (timeInput) timeInput.value = startTime;

        var dayTag = row.querySelector('[data-row-day-tag]');
        if (dayTag) dayTag.innerHTML = '<i class="fa-regular fa-calendar text-[10px]"></i> Day ' + targetDay;

        // If inclusion
        if (kind === 'inclusion' && inclusionKey) {
            var incSelect = row.querySelector('[data-plan-inclusion-select]');
            if (incSelect) {
                incSelect.value = inclusionKey;
                var scheduledTime = incSelect.selectedOptions[0]?.dataset.scheduledTime;
                if (scheduledTime) timeInput.value = scheduledTime;
            }
        }

        if (dayContainer) {
            dayContainer.appendChild(row);
            updateDayTabsAndCards();
            if (daySelect) {
                daySelect.value = String(targetDay);
                row.dataset.planDayValue = String(targetDay);
            }
            if (kind === 'inclusion') {
                placeInclusionDetailsInPlanRow(row);
            }
            if (kind === 'hotel') {
                refreshHotelRoomOptions();
            }
        }

        sortDayItems(targetDay);
        refreshPlaceOptions();
        updateDayHoursSummary();
        renderDayNavTabs(Math.max(1, Number.parseInt(daysInput?.value || '1', 10) || 1));
        updateTotals();

        row.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
        setTimeout(() => row.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50/20'), 1500);

        requestAnimationFrame(function () {
            row.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
            if (kind === 'hotel') {
                row.querySelector('[data-plan-hotel-city]')?.focus({ preventScroll: true });
            }
        });
    }

    function moveRowToDay(row, newDay) {
        newDay = Number.parseInt(newDay, 10) || 1;
        var targetContainer = daysContainer.querySelector('[data-day-items="' + newDay + '"]');
        if (!targetContainer) {
            updateDayTabsAndCards();
            targetContainer = daysContainer.querySelector('[data-day-items="' + newDay + '"]');
        }

        if (targetContainer && row.parentElement !== targetContainer) {
            targetContainer.appendChild(row);

            var dayTag = row.querySelector('[data-row-day-tag]');
            if (dayTag) dayTag.innerHTML = '<i class="fa-regular fa-calendar text-[10px]"></i> Day ' + newDay;

            row.dataset.planDayValue = String(newDay);
            sortDayItems(newDay);
            updateDayHoursSummary();
            renderDayNavTabs(Math.max(1, Number.parseInt(daysInput?.value || '1', 10) || 1));
        }
    }

    function sortDayItems(dayNum) {
        var dayContainer = daysContainer.querySelector('[data-day-items="' + dayNum + '"]');
        if (!dayContainer) return;

        var rows = Array.from(dayContainer.querySelectorAll('[data-plan-row]'));
        rows.sort(function (a, b) {
            var timeA = a.querySelector('[data-plan-time]')?.value || '00:00';
            var timeB = b.querySelector('[data-plan-time]')?.value || '00:00';
            return timeA.localeCompare(timeB);
        });

        rows.forEach(function (row, idx) {
            dayContainer.appendChild(row);
            var numElem = row.querySelector('[data-plan-row-number]');
            if (numElem) numElem.textContent = String(idx + 1);
        });
    }

    // Refresh Inclusion options in all inclusion selects
    function refreshInclusionOptions(rowScope) {
        var options = Array.from(document.querySelectorAll('[data-package-inclusion-row]')).map(function (row, index) {
            return {
                key: row.dataset.inclusionKey,
                name: row.querySelector('[data-inclusion-field="custom_name"]')?.value || 'Package inclusion',
                duration: row.querySelector('[data-inclusion-field="custom_duration_hours"]')?.value || '',
                scheduledTime: row.querySelector('[data-inclusion-field="custom_scheduled_time"]')?.value || '',
                index: index + 1
            };
        });

        var inclusionSelects = rowScope
            ? rowScope.querySelectorAll('[data-plan-inclusion-select]')
            : document.querySelectorAll('[data-plan-inclusion-select]');
        inclusionSelects.forEach(function (select) {
            var selectedKey = select.value;
            select.innerHTML = '<option value="">Choose a package inclusion entry...</option>';
            options.forEach(function (entry) {
                var opt = document.createElement('option');
                opt.value = entry.key;
                opt.dataset.durationHours = entry.duration;
                opt.dataset.scheduledTime = entry.scheduledTime;
                opt.textContent = entry.name + (entry.duration ? ' · ' + entry.duration + ' hrs' : '') + (entry.scheduledTime ? ' · ' + entry.scheduledTime : '');
                opt.selected = (entry.key === selectedKey);
                select.appendChild(opt);
            });
            updateEstimatedEndTime(select.closest('[data-plan-row]'));
        });
    }

    function addPackageInclusion(catalogOption) {
        if (!inclusionList || !packageInclusionTemplate) return null;

        var key = 'new_' + Date.now() + '_' + nextInclusionIndex++;
        var rowMarkup = packageInclusionTemplate.innerHTML
            .replaceAll('__KEY__', key)
            .replaceAll('__ROW_NUMBER__', String(document.querySelectorAll('[data-package-inclusion-row]').length + 1));
        var container = document.createElement('div');
        container.innerHTML = rowMarkup.trim();
        var row = container.firstElementChild;

        inclusionList.appendChild(row);
        if (emptyPackageInclusions) emptyPackageInclusions.classList.add('hidden');
        var catalogIdInput = row.querySelector('[data-inclusion-catalog-id]');
        if (catalogIdInput && catalogOption) {
            catalogIdInput.value = catalogOption.value;
            row.querySelector('[data-inclusion-field="custom_name"]').value = catalogOption.dataset.defaultName || '';
            row.querySelector('[data-inclusion-field="custom_scheduled_time"]').value = catalogOption.dataset.defaultScheduledTime || '';
            row.querySelector('[data-inclusion-field="custom_place_from"]').value = catalogOption.dataset.defaultPlaceFrom || '';
            row.querySelector('[data-inclusion-field="custom_place_to"]').value = catalogOption.dataset.defaultPlaceTo || '';
            row.querySelector('[data-inclusion-field="custom_description"]').value = catalogOption.dataset.defaultDescription || '';
        }
        refreshInclusionOptions();
        return key;
    }

    function placeInclusionDetailsInPlanRow(planRow) {
        var inclusionKey = planRow.querySelector('[data-plan-inclusion-select]')?.value;
        var inclusionDetails = Array.from(document.querySelectorAll('[data-package-inclusion-row]'))
            .find(function (inclusionRow) {
                return inclusionRow.dataset.inclusionKey === inclusionKey;
            });
        var detailsContainer = planRow.querySelector('[data-plan-inclusion-details]');
        if (inclusionDetails && detailsContainer && inclusionDetails.parentElement !== detailsContainer) {
            detailsContainer.appendChild(inclusionDetails);
        }
    }

    function placeScheduledInclusionDetailsInPlanRows() {
        var inclusionRows = Array.from(document.querySelectorAll('[data-package-inclusion-row]'));

        inclusionRows.forEach(function (inclusionRow) {
            var inclusionKey = inclusionRow.dataset.inclusionKey;
            var planSelect = Array.from(document.querySelectorAll('[data-plan-inclusion-select]'))
                .find(function (select) {
                    return select.value === inclusionKey;
                });
            var planRow = planSelect?.closest('[data-plan-row]');
            if (planRow) {
                placeInclusionDetailsInPlanRow(planRow);
            }
        });
    }

    // EVENT LISTENERS
    // Quick Add Place
    quickAddPlaceBtn?.addEventListener('click', function () {
        var activeTab = dayNavTabs?.querySelector('.day-nav-tab.active')?.dataset.dayTab;
        var targetDay = (activeTab && activeTab !== 'all') ? activeTab : '1';
        addPlanItem('place', targetDay);
    });

    // Day Container buttons
    daysContainer.addEventListener('click', function (e) {
        var addPlaceBtn = e.target.closest('.btn-add-place-to-day');
        if (addPlaceBtn) {
            addPlanItem('place', addPlaceBtn.dataset.day);
            return;
        }

        var addHotelBtn = e.target.closest('.btn-add-hotel-to-day');
        if (addHotelBtn) {
            addPlanItem('hotel', addHotelBtn.dataset.day);
            return;
        }

        var addInclusionBtn = e.target.closest('.btn-add-inclusion-to-day');
        if (addInclusionBtn) {
            var dayCard = addInclusionBtn.closest('[data-day-card]');
            var catalogSelect = dayCard?.querySelector('[data-day-inclusion-catalog]');
            if (catalogSelect) {
                var catalogId = catalogSelect.value;
                if (!catalogId) {
                    catalogSelect.focus();
                    return;
                }

                var catalogOption = catalogSelect.selectedOptions[0];
                var inclusionKey = addPackageInclusion(catalogOption);
                if (!inclusionKey) {
                    return;
                }

                addPlanItem('inclusion', addInclusionBtn.dataset.day, inclusionKey);
                catalogSelect.value = '';
                return;
            }

            addPlanItem('inclusion', addInclusionBtn.dataset.day);
            return;
        }

        var removeBtn = e.target.closest('[data-remove-plan-row]');
        if (removeBtn) {
            var row = removeBtn.closest('[data-plan-row]');
            var dayNum = row.querySelector('[data-plan-day]')?.value || '1';
            var inclusionKey = row.querySelector('[data-plan-inclusion-select]')?.value;
            var otherPlanRow = inclusionKey && Array.from(document.querySelectorAll('[data-plan-row]'))
                .find(function (otherRow) {
                    return otherRow !== row && otherRow.querySelector('[data-plan-inclusion-select]')?.value === inclusionKey;
                });
            if (inclusionKey) {
                var inclusionDetails = Array.from(document.querySelectorAll('[data-package-inclusion-row]'))
                    .find(function (detailsRow) {
                        return detailsRow.dataset.inclusionKey === inclusionKey;
                    });
                if (otherPlanRow) {
                    var otherDetailsContainer = otherPlanRow.querySelector('[data-plan-inclusion-details]');
                    if (inclusionDetails && otherDetailsContainer) {
                        otherDetailsContainer.appendChild(inclusionDetails);
                    }
                } else {
                    inclusionDetails?.remove();
                }
            }
            row.remove();
            refreshInclusionOptions();
            sortDayItems(dayNum);
            updateDayHoursSummary();
            renderDayNavTabs(Math.max(1, Number.parseInt(daysInput?.value || '1', 10) || 1));
            updateTotals();
            return;
        }
    });

    // Day Change / Move row
    daysContainer.addEventListener('change', function (e) {
        if (e.target.matches('[data-plan-day]')) {
            var row = e.target.closest('[data-plan-row]');
            moveRowToDay(row, e.target.value);
        }

        if (e.target.matches('[data-plan-place]')) {
            updateEstimatedEndTime(e.target.closest('[data-plan-row]'));
            updateTotals();
            updateDayHoursSummary();
        }

        if (e.target.matches('[data-plan-hotel-room]')) {
            updateHotelRateSummary(e.target.closest('[data-plan-row]'));
            updateTotals();
        }

        if (e.target.matches('[data-plan-hotel]')) {
            var hotelRow = e.target.closest('[data-plan-row]');
            var selectedHotelRoom = hotelRow.querySelector('[data-plan-hotel-room]');
            if (selectedHotelRoom) {
                selectedHotelRoom.value = '';
            }
            refreshHotelRoomOptions();
        }

        if (e.target.matches('[data-plan-hotel-city]')) {
            var hotelRow = e.target.closest('[data-plan-row]');
            var hotelSelect = hotelRow.querySelector('[data-plan-hotel]');
            var hotelRoomSelect = hotelRow.querySelector('[data-plan-hotel-room]');
            if (hotelSelect) {
                hotelSelect.value = '';
            }
            if (hotelRoomSelect) {
                hotelRoomSelect.value = '';
            }
            refreshHotelRoomOptions();
        }

        if (e.target.matches('[data-plan-time]')) {
            var r = e.target.closest('[data-plan-row]');
            updateEstimatedEndTime(r);
            sortDayItems(r.querySelector('[data-plan-day]')?.value || '1');
            updateDayHoursSummary();
        }

        if (e.target.matches('[data-plan-inclusion-select]')) {
            var selectedScheduledTime = e.target.selectedOptions[0]?.dataset.scheduledTime;
            var r2 = e.target.closest('[data-plan-row]');
            if (selectedScheduledTime) {
                var timeInp = r2.querySelector('[data-plan-time]');
                if (timeInp) timeInp.value = selectedScheduledTime;
            }
            updateEstimatedEndTime(r2);
            updateDayHoursSummary();
        }
    });

    daysContainer.addEventListener('input', function (e) {
        if (e.target.matches('[data-plan-extra-price]')) {
            updateTotals();
        }
        if (e.target.matches('[data-plan-hotel-nights]')) {
            var hotelRow = e.target.closest('[data-plan-row]');
            if (hotelRow) {
                updateHotelRateSummary(hotelRow);
            }
            updateTotals();
        }
    });

    // Day Navigation Tabs click
    dayNavTabs?.addEventListener('click', function (e) {
        var tabBtn = e.target.closest('.day-nav-tab');
        if (tabBtn) {
            filterByDayTab(tabBtn.dataset.dayTab);
        }
    });

    // Destination Checkboxes Change (Step 03)
    document.querySelectorAll('.package-country, .package-state, .package-city').forEach(function (cb) {
        cb.addEventListener('change', function () {
            refreshPlaceOptions();
            refreshHotelRoomOptions();
        });
    });

    // Pricing & Duration Inputs
    basePriceInput?.addEventListener('input', updateTotals);
    document.getElementById('discount-percentage')?.addEventListener('input', updateTotals);
    document.getElementById('tax-percentage')?.addEventListener('input', updateTotals);
    nightsInput?.addEventListener('input', function () {
        document.querySelectorAll('[data-plan-row]').forEach(updateHotelRateSummary);
        updateTotals();
    });
    daysInput?.addEventListener('input', function () {
        updateDayTabsAndCards();
        updateDayHoursSummary();
    });

    // Inclusion detail fields are embedded in their scheduled plan rows.
    packageForm?.addEventListener('change', function (e) {
        if (!e.target.closest('[data-package-inclusion-row]')) return;

        if (e.target.matches('[data-inclusion-image-input]')) {
            var fileName = e.target.closest('div')?.querySelector('[data-inclusion-image-name]');
            if (fileName) {
                fileName.textContent = e.target.files?.[0]?.name || 'No new image selected.';
            }
        }

        refreshInclusionOptions();
    });

    packageForm?.addEventListener('input', function (e) {
        if (!e.target.closest('[data-package-inclusion-row]')) return;
        refreshInclusionOptions();
    });

    // INITIALIZATION
    updateDayTabsAndCards();
    placeScheduledInclusionDetailsInPlanRows();
    refreshPlaceOptions();
    refreshHotelRoomOptions();
    refreshInclusionOptions();
    document.querySelectorAll('[data-plan-row]').forEach(updateEstimatedEndTime);
    updateDayHoursSummary();
    updateTotals();
})();
</script>
@endpush
