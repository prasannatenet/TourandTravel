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
            'kind' => $item->package_inclusion_package_id ? 'inclusion' : 'place',
            'day_number' => $item->day_number,
            'start_time' => $item->start_time,
            'city_place_id' => $item->city_place_id,
            'package_inclusion_item_key' => $item->package_inclusion_package_id ? 'item_'.$item->package_inclusion_package_id : '',
            'additional_price' => $item->additional_price,
        ])->all();
    }

    $savedPlanItems ??= [];
    $maximumPlanDay = old('days', $package->days ?? 1);
@endphp

<section id="package-plan" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-package-plan-manager>
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
        <div class="flex items-start gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-sm font-bold text-violet-700">04</span>
            <div>
                <h3 class="text-base font-bold text-slate-900">Package plan manager</h3>
                <p class="mt-1 text-sm text-slate-500">Build the day-by-day schedule with a start time and places from your selected cities.</p>
            </div>
        </div>
        <button type="button" id="add-place-stop" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20"><i class="fa-solid fa-plus text-xs"></i> Add place stop</button>
    </div>

    <div class="space-y-4 px-5 py-5 sm:px-7">
        <div class="rounded-xl border border-sky-100 bg-sky-50/70 px-4 py-3 text-xs leading-5 text-sky-900">
            Set a duration for each meal, break, ride, or service and it will count in the day total. Inclusions are scheduled automatically; when no suggested time is set, a new item starts at the previous item's estimated finish. Places added to the plan use that same next time.
        </div>

        <section id="package-inclusions" class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
            <div class="mb-3 flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm font-bold text-emerald-700"><i class="fa-solid fa-list-check"></i></span>
                <div>
                    <h4 class="text-sm font-bold text-slate-900">What’s included</h4>
                    <p class="mt-1 text-xs text-slate-500">Add each meal, vehicle, or service as a separate entry. You can add the same catalog item more than once.</p>
                </div>
            </div>
            @include('admin.packages._inclusion-picker', [
                'inclusions' => $inclusions,
                'packageInclusionRows' => $packageInclusionRows,
            ])
        </section>

        @foreach($errors->getMessages() as $field => $messages)
            @if(str_starts_with($field, 'plan_items.'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">{{ $messages[0] }}</div>
            @endif
        @endforeach

        <div id="plan-item-list" class="space-y-3">
            @foreach($savedPlanItems as $rowIndex => $planItem)
                @include('admin.packages._plan-item-row', [
                    'rowIndex' => $rowIndex,
                    'rowNumber' => $loop->iteration,
                    'rowType' => ($planItem['kind'] ?? 'place'),
                    'planItem' => $planItem,
                    'places' => $places,
                    'packageInclusionOptions' => $packageInclusionOptions,
                    'maxDays' => $maximumPlanDay,
                ])
            @endforeach
        </div>

        <div id="empty-plan-message" class="rounded-xl border border-dashed border-slate-300 px-4 py-7 text-center {{ count($savedPlanItems) ? 'hidden' : '' }}">
            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400"><i class="fa-regular fa-calendar"></i></span>
            <p class="mt-2 text-sm font-medium text-slate-600">No plan items yet</p>
            <p class="mt-1 text-xs text-slate-400">Add the first activity to start building the daily schedule.</p>
        </div>

        <section class="rounded-xl border border-slate-200 bg-slate-50 p-4" aria-live="polite">
            <div class="mb-3 flex items-center gap-2">
                <i class="fa-regular fa-clock text-indigo-600"></i>
                <h4 class="text-sm font-bold text-slate-800">Estimated time by day</h4>
            </div>
            <div id="day-hours-summary" class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3"></div>
            <p class="mt-2 text-xs text-slate-500">Calculated from each selected place or inclusion duration. Add a duration to an item to include it in the total.</p>
        </section>

        <template id="plan-place-item-template">
            @include('admin.packages._plan-item-row', [
                'rowIndex' => '__INDEX__',
                'rowNumber' => '__ROW_NUMBER__',
                'planItem' => [],
                'rowType' => 'place',
                'places' => $places,
                'packageInclusionOptions' => $packageInclusionOptions,
                'maxDays' => $maximumPlanDay,
            ])
        </template>
        <template id="plan-inclusion-item-template">
            @include('admin.packages._plan-item-row', [
                'rowIndex' => '__INDEX__',
                'rowNumber' => '__ROW_NUMBER__',
                'planItem' => [],
                'rowType' => 'inclusion',
                'places' => $places,
                'packageInclusionOptions' => $packageInclusionOptions,
                'maxDays' => $maximumPlanDay,
            ])
        </template>

        <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-4 sm:p-5">
            <div class="mb-4 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white"><i class="fa-solid fa-calculator text-sm"></i></span>
                <div>
                    <h4 class="text-sm font-bold text-slate-900">Estimated total per person</h4>
                    <p class="text-xs text-slate-500">Updates as you change the base price or plan.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 border-t border-indigo-100 pt-3 sm:grid-cols-2 lg:grid-cols-5 sm:items-end">
                <div>
                    <p class="text-xs text-slate-500">Base package price</p>
                    <p id="plan-base-total" class="mt-1 text-sm font-semibold text-slate-700">₹0.00</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Planned place fees and extras</p>
                    <p id="plan-added-total" class="mt-1 text-sm font-semibold text-slate-700">₹0.00</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Discount</p>
                    <p id="plan-discount-total" class="mt-1 text-sm font-semibold text-emerald-700">—</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Tax</p>
                    <p id="plan-tax-total" class="mt-1 text-sm font-semibold text-slate-700">—</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs font-semibold text-indigo-700">Estimated package total</p>
                    <p id="plan-grand-total" aria-live="polite" class="mt-1 text-2xl font-bold tracking-tight text-indigo-700">₹0.00</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    var manager = document.getElementById('package-plan');
    if (!manager) {
        return;
    }

    var list = manager.querySelector('#plan-item-list');
    var placeTemplate = manager.querySelector('#plan-place-item-template');
    var inclusionTemplate = manager.querySelector('#plan-inclusion-item-template');
    var emptyMessage = manager.querySelector('#empty-plan-message');
    var inclusionList = manager.querySelector('#package-inclusion-list');
    var packageInclusionTemplate = manager.querySelector('#package-inclusion-template');
    var emptyPackageInclusions = manager.querySelector('#empty-package-inclusions');
    var addPackageInclusionButton = manager.querySelector('[data-add-package-inclusion]');
    var addPlaceButton = manager.querySelector('#add-place-stop');
    var basePriceInput = document.getElementById('base-price');
    var daysInput = document.getElementById('package-days');
    var nextInclusionIndex = 0;
    var nextIndex = Array.from(list.querySelectorAll('[data-plan-index]')).reduce(function (highestIndex, row) {
        return Math.max(highestIndex, Number(row.dataset.planIndex) + 1);
    }, 0);

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
    }

    function selectedCityIds() {
        return new Set(Array.from(document.querySelectorAll('.package-city:checked')).map(function (checkbox) {
            return checkbox.value;
        }));
    }

    function refreshPlaceOptions() {
        var selectedCities = selectedCityIds();
        list.querySelectorAll('[data-plan-place]').forEach(function (select) {
            var hasAvailablePlace = false;

            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                var isAllowed = selectedCities.has(option.dataset.cityId || '');
                option.hidden = !isAllowed;
                option.disabled = !isAllowed;
                hasAvailablePlace = hasAvailablePlace || isAllowed;

                if (!isAllowed && option.selected) {
                    select.value = '';
                }
            });

            select.disabled = !hasAvailablePlace;
            updateEstimatedEndTime(select.closest('[data-plan-row]'));
        });

        updateTotals();
    }

    function updateTotals() {
        var basePrice = Number.parseFloat(basePriceInput?.value || '0') || 0;
        var addedPrice = 0;
        var discountPercentage = Number.parseFloat(document.getElementById('discount-percentage')?.value || '0') || 0;
        var taxPercentage = Number.parseFloat(document.getElementById('tax-percentage')?.value || '0') || 0;

        list.querySelectorAll('[data-plan-row]').forEach(function (row) {
            var placeSelect = row.querySelector('[data-plan-place]');
            var selectedPlace = placeSelect?.selectedOptions[0];
            var placePrice = row.dataset.planKind === 'place' ? (Number.parseFloat(selectedPlace?.dataset.price || '0') || 0) : 0;
            var extraPrice = Number.parseFloat(row.querySelector('[data-plan-extra-price]')?.value || '0') || 0;
            addedPrice += placePrice + extraPrice;
        });

        manager.querySelector('#plan-base-total').textContent = formatCurrency(basePrice);
        manager.querySelector('#plan-added-total').textContent = formatCurrency(addedPrice);
        var subtotal = basePrice + addedPrice;
        var discount = subtotal * discountPercentage / 100;
        var afterDiscount = subtotal - discount;
        var tax = afterDiscount * taxPercentage / 100;
        manager.querySelector('#plan-discount-total').textContent = '−' + formatCurrency(discount);
        manager.querySelector('#plan-tax-total').textContent = formatCurrency(tax);
        manager.querySelector('#plan-grand-total').textContent = formatCurrency(afterDiscount + tax);
    }

    function updateDayHoursSummary() {
        var summary = manager.querySelector('#day-hours-summary');
        if (!summary) {
            return;
        }

        var numberOfDays = Math.max(1, Number.parseInt(daysInput?.value || '1', 10) || 1);
        var totals = Array.from({ length: numberOfDays }, function () { return { minutes: 0, inclusionMinutes: 0, missing: 0, items: 0 }; });

        list.querySelectorAll('[data-plan-row]').forEach(function (row) {
            var dayNumber = Number.parseInt(row.querySelector('[data-plan-day]')?.value || '1', 10) || 1;
            var dayTotal = totals[dayNumber - 1];
            if (!dayTotal) {
                return;
            }

            dayTotal.items++;
            var durationHours = 0;
            if (row.dataset.planKind === 'place') {
                var placeOption = row.querySelector('[data-plan-place]')?.selectedOptions[0];
                durationHours = Number.parseFloat(placeOption?.dataset.duration || '0') || 0;
            } else {
                var inclusionOption = row.querySelector('[data-plan-inclusion-select]')?.selectedOptions[0];
                durationHours = Number.parseFloat(inclusionOption?.dataset.durationHours || '0') || 0;
            }

            if (durationHours > 0) {
                var durationMinutes = Math.round(durationHours * 60);
                dayTotal.minutes += durationMinutes;
                if (row.dataset.planKind === 'inclusion') {
                    dayTotal.inclusionMinutes += durationMinutes;
                }
            } else {
                dayTotal.missing++;
            }
        });

        summary.innerHTML = '';
        totals.forEach(function (day, index) {
            var card = document.createElement('div');
            card.className = 'rounded-lg border border-slate-200 bg-white px-3 py-2.5';
            var title = document.createElement('p');
            title.className = 'text-xs font-semibold text-slate-600';
            title.textContent = 'Day ' + (index + 1);
            var total = document.createElement('p');
            total.className = 'mt-1 text-base font-bold text-indigo-700';
            var hours = Math.floor(day.minutes / 60);
            var minutes = day.minutes % 60;
            total.textContent = day.minutes > 0
                ? (hours ? hours + ' hr' + (hours === 1 ? '' : 's') : '') + (hours && minutes ? ' ' : '') + (minutes ? minutes + ' min' : '')
                : (day.items ? 'Duration not set' : 'No plan items');
            card.append(title, total);
            if (day.inclusionMinutes > 0) {
                var inclusionTime = document.createElement('p');
                var inclusionHours = Math.floor(day.inclusionMinutes / 60);
                var inclusionRemainder = day.inclusionMinutes % 60;
                inclusionTime.className = 'mt-1 text-[11px] text-emerald-700';
                inclusionTime.textContent = 'Includes ' + (inclusionHours ? inclusionHours + ' hr' + (inclusionHours === 1 ? '' : 's') : '') + (inclusionHours && inclusionRemainder ? ' ' : '') + (inclusionRemainder ? inclusionRemainder + ' min' : '') + ' of package inclusions';
                card.appendChild(inclusionTime);
            }
            if (day.missing > 0) {
                var note = document.createElement('p');
                note.className = 'mt-1 text-[11px] text-amber-700';
                note.textContent = day.missing + ' item' + (day.missing === 1 ? '' : 's') + ' missing duration';
                card.appendChild(note);
            }
            summary.appendChild(card);
        });

        refreshInclusionScheduleStatuses();
    }

    function refreshInclusionScheduleStatuses() {
        if (!inclusionList) {
            return;
        }

        inclusionList.querySelectorAll('[data-package-inclusion-row]').forEach(function (inclusionRow) {
            var inclusionKey = inclusionRow.dataset.inclusionKey;
            var status = inclusionRow.querySelector('[data-inclusion-schedule-status]');
            var addButton = inclusionRow.querySelector('[data-add-inclusion-to-plan]');
            if (!status) {
                return;
            }

            var scheduledRows = Array.from(list.querySelectorAll('[data-plan-row]')).filter(function (planRow) {
                var select = planRow.querySelector('[data-plan-inclusion-select]');
                return select?.value === inclusionKey;
            });

            if (scheduledRows.length === 0) {
                status.textContent = 'Not scheduled; hours are not assigned to a day';
                status.className = 'mb-3 text-[11px] font-medium text-amber-700';
                if (addButton) {
                    addButton.disabled = false;
                addButton.textContent = 'Add to plan';
                    addButton.classList.remove('cursor-not-allowed', 'opacity-60');
                }
                return;
            }

            var scheduledDays = Array.from(new Set(scheduledRows.map(function (planRow) {
                return Number.parseInt(planRow.querySelector('[data-plan-day]')?.value || '1', 10) || 1;
            }))).sort(function (first, second) { return first - second; });
            var duration = Number.parseFloat(inclusionRow.querySelector('[data-inclusion-field="custom_duration_hours"]')?.value || '0') || 0;
            var totalMinutes = Math.round(duration * 60) * scheduledRows.length;
            var hours = Math.floor(totalMinutes / 60);
            var minutes = totalMinutes % 60;
            var countedTime = totalMinutes > 0
                ? ' · ' + (hours ? hours + ' hr' + (hours === 1 ? '' : 's') : '') + (hours && minutes ? ' ' : '') + (minutes ? minutes + ' min' : '') + ' counted'
                : ' · set duration to count hours';

            status.textContent = 'Scheduled on Day ' + scheduledDays.join(', Day ') + countedTime;
            status.className = totalMinutes > 0
                ? 'mb-3 text-[11px] font-medium text-emerald-700'
                : 'mb-3 text-[11px] font-medium text-amber-700';
            if (addButton) {
                addButton.disabled = true;
                addButton.textContent = 'Added to plan';
                addButton.classList.add('cursor-not-allowed', 'opacity-60');
            }
        });
    }

    function refreshInclusionOptions() {
        if (!inclusionList) {
            return;
        }

        var options = Array.from(inclusionList.querySelectorAll('[data-package-inclusion-row]')).map(function (row, index) {
            var key = row.dataset.inclusionKey;
            var name = row.querySelector('[data-inclusion-field="custom_name"]')?.value || 'Package inclusion';
            var duration = row.querySelector('[data-inclusion-field="custom_duration_hours"]')?.value || '';
            var scheduledTime = row.querySelector('[data-inclusion-field="custom_scheduled_time"]')?.value || '';
            return { key: key, name: name, duration: duration, scheduledTime: scheduledTime, index: index + 1 };
        });

        list.querySelectorAll('[data-plan-inclusion-select]').forEach(function (select) {
            var selectedKey = select.value;
            select.innerHTML = '<option value="">Choose a package inclusion entry</option>';
            options.forEach(function (entry) {
                var option = document.createElement('option');
                option.value = entry.key;
                option.dataset.durationHours = entry.duration;
                option.dataset.scheduledTime = entry.scheduledTime;
                option.textContent = entry.name + (entry.duration ? ' · ' + entry.duration + ' hrs' : '') + (entry.scheduledTime ? ' · ' + entry.scheduledTime : '') + ' · Entry ' + entry.index;
                option.selected = entry.key === selectedKey;
                select.appendChild(option);
            });
            if (selectedKey && !options.some(function (entry) { return entry.key === selectedKey; })) {
                select.value = '';
            }
            updateEstimatedEndTime(select.closest('[data-plan-row]'));
        });
        updateDayHoursSummary();
    }

    function addPackageInclusion() {
        if (!inclusionList || !packageInclusionTemplate) {
            return;
        }

        var key = 'new_' + Date.now() + '_' + nextInclusionIndex++;
        var rowMarkup = packageInclusionTemplate.innerHTML
            .replaceAll('__KEY__', key)
            .replaceAll('__ROW_NUMBER__', String(inclusionList.querySelectorAll('[data-package-inclusion-row]').length + 1));
        var container = document.createElement('div');
        container.innerHTML = rowMarkup.trim();
        var row = container.firstElementChild;
        inclusionList.appendChild(row);
        emptyPackageInclusions?.classList.add('hidden');
        row.querySelector('[data-inclusion-catalog]')?.focus();
        refreshInclusionOptions();
    }

    function updateEstimatedEndTime(row) {
        var placeSelect = row.querySelector('[data-plan-place]');
        var startTimeInput = row.querySelector('[data-plan-time]');
        var endTimePanel = row.querySelector('[data-estimated-end-time]');
        var startTimeValue = row.querySelector('[data-estimated-start-time-value]');
        var endTimeValue = row.querySelector('[data-estimated-end-time-value]');
        var endTimeHelp = row.querySelector('[data-estimated-end-time-help]');
        if (!endTimePanel) {
            return;
        }
        var selectedPlace = placeSelect?.selectedOptions[0];
        var startTime = startTimeInput.value;
        var inclusionSelect = row.querySelector('[data-plan-inclusion-select]');
        var durationHours = placeSelect
            ? Number.parseFloat(selectedPlace?.dataset.duration || '0')
            : (Number.parseFloat(inclusionSelect?.selectedOptions[0]?.dataset.durationHours || '0') || 0);

        if ((placeSelect && !placeSelect.value) || !startTime || durationHours <= 0) {
            endTimePanel.classList.add('hidden');
            startTimeValue.textContent = '';
            endTimeValue.textContent = '';
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
        var nextDayLabel = daysLater > 0 ? ' (+' + daysLater + ' ' + (daysLater === 1 ? 'day' : 'days') + ')' : '';
        var formattedDuration = String(Number(durationHours.toFixed(2)));
        var durationUnit = Number(formattedDuration) === 1 ? 'hour' : 'hours';

        startTimeValue.textContent = startDisplayHour + ':' + String(timeParts[1]).padStart(2, '0') + ' ' + startMeridiem;
        endTimeValue.textContent = displayHour + ':' + String(endMinute).padStart(2, '0') + ' ' + meridiem + nextDayLabel;
        endTimeHelp.textContent = 'Duration: ' + formattedDuration + ' ' + durationUnit + (placeSelect
            ? ' · Based on the selected place visit duration.'
            : ' · Based on this package inclusion duration.');
        endTimePanel.classList.remove('hidden');
    }

    function updateRowNumbers() {
        var rows = Array.from(list.querySelectorAll('[data-plan-row]'));
        rows.forEach(function (row, index) {
            row.querySelector('[data-plan-row-number]').textContent = String(index + 1).padStart(2, '0');
            row.querySelector('[data-plan-row-label]').textContent = String(index + 1);
            row.querySelector('[data-plan-day]').max = daysInput?.value || '99';
        });

        emptyMessage.classList.toggle('hidden', rows.length > 0);
        updateDayHoursSummary();
    }

    function sortPlanItems() {
        var rows = Array.from(list.querySelectorAll('[data-plan-row]'));
        rows.sort(function (first, second) {
            var dayDifference = Number(first.querySelector('[data-plan-day]').value || 1) - Number(second.querySelector('[data-plan-day]').value || 1);
            if (dayDifference !== 0) {
                return dayDifference;
            }
            return (first.querySelector('[data-plan-time]').value || '').localeCompare(second.querySelector('[data-plan-time]').value || '');
        });
        rows.forEach(function (row) { list.appendChild(row); });
        updateRowNumbers();
    }

    function nextPlanSlot() {
        var latestEndMinute = 9 * 60;

        list.querySelectorAll('[data-plan-row]').forEach(function (row) {
            var dayNumber = Number.parseInt(row.querySelector('[data-plan-day]')?.value || '1', 10) || 1;
            var timeValue = row.querySelector('[data-plan-time]')?.value || '';
            if (!timeValue) {
                return;
            }

            var timeParts = timeValue.split(':').map(Number);
            var startMinute = ((dayNumber - 1) * 1440) + (timeParts[0] * 60) + timeParts[1];
            var durationHours = 0;
            if (row.dataset.planKind === 'place') {
                durationHours = Number.parseFloat(row.querySelector('[data-plan-place]')?.selectedOptions[0]?.dataset.duration || '0') || 0;
            } else {
                durationHours = Number.parseFloat(row.querySelector('[data-plan-inclusion-select]')?.selectedOptions[0]?.dataset.durationHours || '0') || 0;
            }

            latestEndMinute = Math.max(latestEndMinute, startMinute + Math.round(durationHours * 60));
        });

        var dayNumber = Math.floor(latestEndMinute / 1440) + 1;
        var minutesInDay = latestEndMinute % 1440;
        var hour = String(Math.floor(minutesInDay / 60)).padStart(2, '0');
        var minute = String(minutesInDay % 60).padStart(2, '0');

        return { day: dayNumber, time: hour + ':' + minute };
    }

    function addPlanItem(kind, inclusionId) {
        var nextSlot = nextPlanSlot();
        var template = kind === 'inclusion' ? inclusionTemplate : placeTemplate;
        var rowMarkup = template.innerHTML
            .replaceAll('__INDEX__', String(nextIndex++))
            .replaceAll('__ROW_NUMBER__', String(list.querySelectorAll('[data-plan-row]').length + 1).padStart(2, '0'));
        var container = document.createElement('div');
        container.innerHTML = rowMarkup.trim();
        var row = container.firstElementChild;
        list.appendChild(row);
        row.querySelector('[data-plan-day]').value = String(nextSlot.day);
        row.querySelector('[data-plan-time]').value = nextSlot.time;
        if (kind === 'inclusion') {
            refreshInclusionOptions();
            var inclusionSelect = row.querySelector('[data-plan-inclusion-select]');
            inclusionSelect.value = inclusionId;
            var scheduledTime = inclusionSelect.selectedOptions[0]?.dataset.scheduledTime || '';
            row.querySelector('[data-plan-time]').value = scheduledTime || nextSlot.time;
            row.dataset.inclusionAutoStartTime = scheduledTime || nextSlot.time;
            updateEstimatedEndTime(row);
        }
        sortPlanItems();
        refreshPlaceOptions();
        updateDayHoursSummary();
    }

    function scheduleInclusionWhenReady(inclusionRow) {
        if (!inclusionRow) {
            return;
        }

        var inclusionKey = inclusionRow.dataset.inclusionKey;
        var catalogSelect = inclusionRow.querySelector('[data-inclusion-catalog]');
        var startTime = inclusionRow.querySelector('[data-inclusion-field="custom_scheduled_time"]')?.value || '';
        var duration = Number.parseFloat(inclusionRow.querySelector('[data-inclusion-field="custom_duration_hours"]')?.value || '0') || 0;
        var scheduledRows = Array.from(list.querySelectorAll('[data-plan-row]')).filter(function (planRow) {
            return planRow.querySelector('[data-plan-inclusion-select]')?.value === inclusionKey;
        });

        if (!catalogSelect?.value || duration <= 0) {
            return;
        }

        if (scheduledRows.length === 0) {
            addPlanItem('inclusion', inclusionKey);
            return;
        }

        scheduledRows.forEach(function (planRow) {
            var startTimeInput = planRow.querySelector('[data-plan-time]');
            if (!startTimeInput.value) {
                startTimeInput.value = startTime || '09:00';
                planRow.dataset.inclusionAutoStartTime = startTime || '09:00';
                updateEstimatedEndTime(planRow);
            }
        });
    }

    addPlaceButton.addEventListener('click', function () { addPlanItem('place'); });
    addPackageInclusionButton?.addEventListener('click', addPackageInclusion);
    inclusionList?.addEventListener('change', function (event) {
        if (event.target.matches('[data-inclusion-catalog]')) {
            var row = event.target.closest('[data-package-inclusion-row]');
            var option = event.target.selectedOptions[0];
            var values = {
                custom_name: option?.dataset.defaultName || '',
                custom_scheduled_time: option?.dataset.defaultScheduledTime || '',
                custom_place_from: option?.dataset.defaultPlaceFrom || '',
                custom_place_to: option?.dataset.defaultPlaceTo || '',
                custom_description: option?.dataset.defaultDescription || ''
            };
            Object.keys(values).forEach(function (field) {
                var control = row.querySelector('[data-inclusion-field="' + field + '"]');
                if (control) {
                    control.value = values[field];
                }
            });
        }
        refreshInclusionOptions();
        scheduleInclusionWhenReady(event.target.closest('[data-package-inclusion-row]'));
    });
    inclusionList?.addEventListener('input', function (event) {
        refreshInclusionOptions();
        if (event.target.matches('[data-inclusion-field="custom_duration_hours"], [data-inclusion-field="custom_scheduled_time"]')) {
            var inclusionRow = event.target.closest('[data-package-inclusion-row]');
            if (event.target.matches('[data-inclusion-field="custom_scheduled_time"]')) {
                var inclusionKey = inclusionRow.dataset.inclusionKey;
                var suggestedTime = event.target.value || '09:00';
                list.querySelectorAll('[data-plan-row]').forEach(function (planRow) {
                    var inclusionSelect = planRow.querySelector('[data-plan-inclusion-select]');
                    var startTimeInput = planRow.querySelector('[data-plan-time]');
                    if (inclusionSelect?.value === inclusionKey && planRow.dataset.inclusionAutoStartTime && startTimeInput.value === planRow.dataset.inclusionAutoStartTime) {
                        startTimeInput.value = suggestedTime;
                        planRow.dataset.inclusionAutoStartTime = suggestedTime;
                        updateEstimatedEndTime(planRow);
                    }
                });
            }
            scheduleInclusionWhenReady(inclusionRow);
        }
    });
    inclusionList?.addEventListener('click', function (event) {
        var addToPlan = event.target.closest('[data-add-inclusion-to-plan]');
        if (addToPlan) {
            addPlanItem('inclusion', addToPlan.dataset.addInclusionToPlan);
            return;
        }
        var removeButton = event.target.closest('[data-remove-package-inclusion]');
        if (!removeButton) {
            return;
        }
        var row = removeButton.closest('[data-package-inclusion-row]');
        var key = row.dataset.inclusionKey;
        if (Array.from(list.querySelectorAll('[data-plan-inclusion-select]')).some(function (select) { return select.value === key; })) {
            window.alert('Remove this entry from the day plan before deleting it.');
            return;
        }
        row.remove();
        emptyPackageInclusions?.classList.toggle('hidden', inclusionList.querySelectorAll('[data-package-inclusion-row]').length > 0);
        refreshInclusionOptions();
    });
    list.addEventListener('change', function (event) {
        if (event.target.matches('[data-plan-inclusion-select]')) {
            var scheduledTime = event.target.selectedOptions[0]?.dataset.scheduledTime || '';
            if (scheduledTime) {
                event.target.closest('[data-plan-row]').querySelector('[data-plan-time]').value = scheduledTime;
            }
            updateEstimatedEndTime(event.target.closest('[data-plan-row]'));
            updateDayHoursSummary();
        }
    });
    list.addEventListener('click', function (event) {
        var removeButton = event.target.closest('[data-remove-plan-row]');
        if (!removeButton) {
            return;
        }

        removeButton.closest('[data-plan-row]').remove();
        updateRowNumbers();
        updateTotals();
    });
    list.addEventListener('change', function (event) {
        if (event.target.matches('[data-plan-place]')) {
            updateEstimatedEndTime(event.target.closest('[data-plan-row]'));
            updateTotals();
        }

        if (event.target.matches('[data-plan-time]')) {
            updateEstimatedEndTime(event.target.closest('[data-plan-row]'));
            sortPlanItems();
        }

        if (event.target.matches('[data-plan-day]')) {
            sortPlanItems();
            updateDayHoursSummary();
        }

    });
    list.addEventListener('input', function (event) {
        if (event.target.matches('[data-plan-time]')) {
            updateEstimatedEndTime(event.target.closest('[data-plan-row]'));
        }

        if (event.target.matches('[data-plan-extra-price]')) {
            updateTotals();
        }

        if (event.target.matches('[data-plan-day]')) {
            updateDayHoursSummary();
        }
    });

    document.querySelectorAll('.package-country, .package-state, .package-city').forEach(function (checkbox) {
        checkbox.addEventListener('change', refreshPlaceOptions);
    });
    basePriceInput?.addEventListener('input', updateTotals);
    document.getElementById('discount-percentage')?.addEventListener('input', updateTotals);
    document.getElementById('tax-percentage')?.addEventListener('input', updateTotals);
    daysInput?.addEventListener('input', updateRowNumbers);

    updateRowNumbers();
    refreshInclusionOptions();
    inclusionList?.querySelectorAll('[data-package-inclusion-row]').forEach(scheduleInclusionWhenReady);
    refreshPlaceOptions();
    updateDayHoursSummary();
    list.querySelectorAll('[data-plan-inclusion-select]').forEach(function (select) { updateEstimatedEndTime(select.closest('[data-plan-row]')); });
})();
</script>
@endpush
