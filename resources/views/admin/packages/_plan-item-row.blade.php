@php
    $rowType = $rowType ?? 'place';
@endphp
<article class="package-plan-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" data-plan-row data-plan-index="{{ $rowIndex }}" data-plan-kind="{{ $rowType }}">
    <input type="hidden" name="plan_items[{{ $rowIndex }}][kind]" value="{{ $rowType }}">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-700" data-plan-row-number>{{ $rowNumber }}</span>
            <div>
                <h4 class="text-sm font-semibold text-slate-800">{{ $rowType === 'inclusion' ? 'Included item' : 'Place stop' }} <span data-plan-row-label>{{ $rowNumber }}</span></h4>
                <p class="text-xs text-slate-400">{{ $rowType === 'inclusion' ? 'Schedule one specific package inclusion entry.' : 'Set a visit from one of your selected cities.' }}</p>
            </div>
        </div>
        <button type="button" data-remove-plan-row class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-500 transition hover:bg-red-50 hover:text-red-600"><i class="fa-regular fa-trash-can"></i><span class="hidden sm:inline">Remove</span></button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="plan-day-{{ $rowIndex }}" class="mb-1.5 block text-xs font-semibold text-slate-600">Day <span class="text-red-500">*</span></label>
            <input id="plan-day-{{ $rowIndex }}" type="number" name="plan_items[{{ $rowIndex }}][day_number]" value="{{ $planItem['day_number'] ?? 1 }}" required min="1" max="{{ $maxDays }}" data-plan-day class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
        </div>
        @if($rowType !== 'inclusion')
            <div class="sm:col-span-2">
                <label for="plan-place-{{ $rowIndex }}" class="mb-1.5 block text-xs font-semibold text-slate-600">Place <span class="text-red-500">*</span></label>
                <select id="plan-place-{{ $rowIndex }}" name="plan_items[{{ $rowIndex }}][city_place_id]" required data-plan-place class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                    <option value="">Choose a place from selected cities</option>
                    @foreach($places as $place)
                        <option value="{{ $place->id }}" data-city-id="{{ $place->city_id }}" data-price="{{ $place->price_per_person }}" data-duration="{{ $place->visit_duration_hours }}" @selected((string) ($planItem['city_place_id'] ?? '') === (string) $place->id)>{{ $place->name }} — {{ $place->city->name ?? 'City' }} (₹{{ number_format((float) $place->price_per_person, 2) }})</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="sm:col-span-2">
                <label for="plan-inclusion-{{ $rowIndex }}" class="mb-1.5 block text-xs font-semibold text-slate-600">Package inclusion entry <span class="text-red-500">*</span></label>
                <select id="plan-inclusion-{{ $rowIndex }}" name="plan_items[{{ $rowIndex }}][package_inclusion_item_key]" required data-plan-inclusion-select class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                    <option value="">Choose a package inclusion entry</option>
                    @foreach($packageInclusionOptions as $inclusionKey => $inclusionOption)
                        <option value="{{ $inclusionKey }}" data-duration-hours="{{ $inclusionOption['duration_hours'] }}" data-scheduled-time="{{ $inclusionOption['scheduled_time'] ? substr((string) $inclusionOption['scheduled_time'], 0, 5) : '' }}" data-inclusion-name="{{ $inclusionOption['name'] }}" @selected((string) ($planItem['package_inclusion_item_key'] ?? '') === (string) $inclusionKey)>{{ $inclusionOption['name'] }}{{ $inclusionOption['duration_hours'] ? ' · '.$inclusionOption['duration_hours'].' hrs' : '' }}{{ $inclusionOption['scheduled_time'] ? ' · '.substr((string) $inclusionOption['scheduled_time'], 0, 5) : '' }} · Entry {{ $loop->iteration }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label for="plan-time-{{ $rowIndex }}" class="mb-1.5 block text-xs font-semibold text-slate-600">Start time <span class="text-red-500">*</span></label>
            <input id="plan-time-{{ $rowIndex }}" type="time" name="plan_items[{{ $rowIndex }}][start_time]" value="{{ isset($planItem['start_time']) ? substr((string) $planItem['start_time'], 0, 5) : '' }}" required data-plan-time class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
        </div>
    </div>

    @if($rowType === 'place')
        <div class="mt-4 max-w-xs border-t border-slate-100 pt-4">
            <label for="plan-extra-price-{{ $rowIndex }}" class="mb-1.5 block text-xs font-semibold text-slate-600">Extra cost per person <span class="font-normal text-slate-400">(optional)</span></label>
            <div class="relative"><span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs text-slate-400">₹</span><input id="plan-extra-price-{{ $rowIndex }}" type="number" name="plan_items[{{ $rowIndex }}][additional_price]" value="{{ $planItem['additional_price'] ?? 0 }}" min="0" step="0.01" data-plan-extra-price class="w-full rounded-lg border border-slate-300 py-2 pl-7 pr-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"></div>
        </div>
    @endif

    <div data-estimated-end-time class="mt-4 flex items-start gap-2 rounded-lg border border-indigo-100 bg-indigo-50/60 px-3 py-2.5" hidden>
        <i class="fa-regular fa-clock mt-0.5 text-xs text-indigo-600"></i>
        <div>
            <p class="text-xs font-semibold text-indigo-900">Scheduled time: <span data-estimated-start-time-value></span> – <span data-estimated-end-time-value></span></p>
            <p data-estimated-end-time-help class="mt-0.5 text-[11px] text-indigo-700"></p>
        </div>
    </div>
</article>
