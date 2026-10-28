@php
    $rowType = $rowType ?? 'place';
    $placesByCity = $places->groupBy(function ($place) {
        return $place->city->name ?? 'Other Destinations';
    });
    $currentDay = (int) ($planItem['day_number'] ?? 1);
    $maxDaysNumber = max(1, (int) ($maxDays ?? 1));
@endphp
<article class="package-plan-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md" data-plan-row data-plan-index="{{ $rowIndex }}" data-plan-kind="{{ $rowType }}" data-plan-day-value="{{ $currentDay }}">
    <input type="hidden" name="plan_items[{{ $rowIndex }}][kind]" value="{{ $rowType }}">

    <!-- Row Header -->
    <div class="mb-3.5 flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-md bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-800" data-row-day-tag>
                <i class="fa-regular fa-calendar text-[10px]"></i> Day {{ $currentDay }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-md {{ $rowType === 'inclusion' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-sky-50 text-sky-700 border border-sky-100' }} px-2 py-0.5 text-[11px] font-semibold">
                <i class="{{ $rowType === 'inclusion' ? 'fa-solid fa-utensils' : 'fa-solid fa-landmark' }} text-[10px]"></i>
                {{ $rowType === 'inclusion' ? 'Included Service' : 'Sightseeing Stop' }}
            </span>
            <span class="text-xs text-slate-400 font-medium ml-1">
                Stop <span data-plan-row-number>{{ $rowNumber }}</span>
            </span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Day Reassignment -->
            <div class="flex items-center gap-1.5 text-xs text-slate-600">
                <span class="font-medium text-[11px]">Move to:</span>
                <select id="plan-day-{{ $rowIndex }}" name="plan_items[{{ $rowIndex }}][day_number]" required data-plan-day class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-bold text-indigo-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    @for($d = 1; $d <= max($currentDay, $maxDaysNumber); $d++)
                        <option value="{{ $d }}" @selected($currentDay === $d)>Day {{ $d }}</option>
                    @endfor
                </select>
            </div>

            <!-- Remove Button -->
            <button type="button" data-remove-plan-row class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition" title="Remove this activity from the day plan">
                <i class="fa-regular fa-trash-can text-xs"></i>
                <span class="hidden sm:inline">Remove</span>
            </button>
        </div>
    </div>

    <!-- Row Controls Grid -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 items-start">
        <!-- Start Time -->
        <div class="sm:col-span-3">
            <label for="plan-time-{{ $rowIndex }}" class="mb-1 block text-xs font-semibold text-slate-700">Start Time <span class="text-red-500">*</span></label>
            <input id="plan-time-{{ $rowIndex }}" type="time" name="plan_items[{{ $rowIndex }}][start_time]" value="{{ isset($planItem['start_time']) ? substr((string) $planItem['start_time'], 0, 5) : '09:00' }}" required data-plan-time class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            <div data-estimated-end-time class="mt-1.5 hidden flex items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700 border border-indigo-100">
                <i class="fa-regular fa-clock text-[10px]"></i>
                <span data-estimated-start-time-value></span> &ndash; <span data-estimated-end-time-value></span>
            </div>
        </div>

        <!-- Place / Inclusion Select -->
        @if($rowType !== 'inclusion')
            <div class="sm:col-span-6">
                <label for="plan-place-{{ $rowIndex }}" class="mb-1 block text-xs font-semibold text-slate-700">
                    Sightseeing Attraction <span class="text-red-500">*</span>
                    <span class="font-normal text-slate-400 text-[11px]">(from destination cities)</span>
                </label>
                <select id="plan-place-{{ $rowIndex }}" name="plan_items[{{ $rowIndex }}][city_place_id]" required data-plan-place class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Choose an attraction...</option>
                    @foreach($placesByCity as $cityName => $cityPlaces)
                        <optgroup label="📍 {{ $cityName }} ({{ count($cityPlaces) }} places)" data-city-group="{{ $cityName }}">
                            @foreach($cityPlaces as $place)
                                <option value="{{ $place->id }}"
                                    data-city-id="{{ $place->city_id }}"
                                    data-city-name="{{ $cityName }}"
                                    data-price="{{ $place->price_per_person }}"
                                    data-duration="{{ $place->visit_duration_hours }}"
                                    @selected((string) ($planItem['city_place_id'] ?? '') === (string) $place->id)>
                                    {{ $place->name }} &mdash; ₹{{ number_format((float) $place->price_per_person, 0) }} ({{ $place->visit_duration_hours ? $place->visit_duration_hours.' hrs' : 'duration unstated' }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <p data-estimated-end-time-help class="mt-1 text-[11px] text-slate-500"></p>
            </div>
        @else
            <div class="sm:col-span-6">
                <label for="plan-inclusion-{{ $rowIndex }}" class="mb-1 block text-xs font-semibold text-slate-700">
                    Package Inclusion Service <span class="text-red-500">*</span>
                </label>
                <select id="plan-inclusion-{{ $rowIndex }}" name="plan_items[{{ $rowIndex }}][package_inclusion_item_key]" required data-plan-inclusion-select class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Choose a package inclusion entry...</option>
                    @foreach($packageInclusionOptions as $inclusionKey => $inclusionOption)
                        <option value="{{ $inclusionKey }}"
                            data-duration-hours="{{ $inclusionOption['duration_hours'] }}"
                            data-scheduled-time="{{ $inclusionOption['scheduled_time'] ? substr((string) $inclusionOption['scheduled_time'], 0, 5) : '' }}"
                            data-inclusion-name="{{ $inclusionOption['name'] }}"
                            @selected((string) ($planItem['package_inclusion_item_key'] ?? '') === (string) $inclusionKey)>
                            {{ $inclusionOption['name'] }}{{ $inclusionOption['duration_hours'] ? ' · '.$inclusionOption['duration_hours'].' hrs' : '' }}{{ $inclusionOption['scheduled_time'] ? ' · '.substr((string) $inclusionOption['scheduled_time'], 0, 5) : '' }} · Entry {{ $loop->iteration }}
                        </option>
                    @endforeach
                </select>
                <p data-estimated-end-time-help class="mt-1 text-[11px] text-slate-500"></p>
            </div>
        @endif

        <!-- Extra Price -->
        <div class="sm:col-span-3">
            <label for="plan-extra-price-{{ $rowIndex }}" class="mb-1 block text-xs font-semibold text-slate-700">
                Extra Cost <span class="font-normal text-slate-400">(optional)</span>
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs text-slate-400">₹</span>
                <input id="plan-extra-price-{{ $rowIndex }}" type="number" name="plan_items[{{ $rowIndex }}][additional_price]" value="{{ $planItem['additional_price'] ?? 0 }}" min="0" step="0.01" data-plan-extra-price placeholder="0.00" class="w-full rounded-lg border border-slate-300 py-2 pl-7 pr-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Per traveler fee</p>
        </div>
    </div>

    @if($rowType === 'inclusion')
        <div class="mt-4" data-plan-inclusion-details></div>
    @endif

    <!-- Swap Activity / Change Place Toggle (for place stops) -->
    @if($rowType === 'place')
        <div class="mt-3.5 pt-3 border-t border-slate-100">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-indigo-100 bg-indigo-50/50 px-3.5 py-2.5 transition hover:bg-indigo-50/80">
                <label for="plan-show-charge-{{ $rowIndex }}" class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input
                        id="plan-show-charge-{{ $rowIndex }}"
                        type="checkbox"
                        name="plan_items[{{ $rowIndex }}][show_charge_button]"
                        value="1"
                        data-plan-show-charge
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        {{ ($planItem['show_charge_button'] ?? false) ? 'checked' : '' }}
                    >
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate text-indigo-600 text-xs"></i>
                        Allow user to swap/change this place on website
                    </span>
                </label>
                <span class="text-[11px] text-indigo-600 font-medium flex items-center gap-1" title="Travelers will see a 'Change Activity' button on the website to swap this attraction with another place from the same city.">
                    <i class="fa-solid fa-circle-info text-indigo-500 text-xs"></i> Scoped to attractions in same city
                </span>
            </div>
        </div>
    @endif
</article>
