@extends('layouts.admin')
@section('title', $hotel->name)
@section('header', 'Hotel Details')
@section('subheader', 'Hotel profile and rooms managed by the hotel partner.')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.hotels.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800"><i class="fa-solid fa-arrow-left mr-2"></i>Back to Hotels</a>
    <a href="{{ route('admin.hotels.edit', $hotel) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"><i class="fa-solid fa-pen mr-2"></i>Edit Hotel</a>
</div>

<section class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Hotel profile</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $hotel->name }}</h1>
            <p class="mt-2 text-slate-500">{{ $hotel->city->name }}, {{ $hotel->city->state->name }}, {{ $hotel->city->state->country->name }}</p>
        </div>
        <span class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800"><i class="fa-solid fa-star mr-1"></i>{{ $hotel->star_rating }} star{{ $hotel->star_rating === 1 ? '' : 's' }}</span>
    </div>
    <div class="grid gap-8 pt-6 lg:grid-cols-2">
        <div>
            <h2 class="font-bold text-slate-900">Partner login</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hotel code</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800">{{ $hotel->hotel_code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Username</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $hotel->username ?: 'Not set' }}</dd>
                </div>
            </dl>
        </div>
        <div>
            <h2 class="font-bold text-slate-900">Full address</h2>
            <p class="mt-4 whitespace-pre-line leading-6 text-slate-700">{{ $hotel->address }}</p>
        </div>
    </div>
    <div class="mt-8 border-t border-slate-100 pt-6">
        <h2 class="font-bold text-slate-900">Hotel Photos</h2>
        @if ($hotel->primary_image)
            <div class="mt-4 max-w-2xl overflow-hidden rounded-xl">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Primary Image</p>
                <a href="{{ asset('storage/'.$hotel->primary_image) }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('storage/'.$hotel->primary_image) }}" alt="{{ $hotel->name }} primary image" class="h-72 w-full rounded-xl object-cover">
                </a>
            </div>
        @else
            <p class="mt-3 text-sm text-slate-500">No primary hotel image has been uploaded yet.</p>
        @endif
        @if ($hotel->secondary_images)
            <div class="mt-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Secondary Images</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($hotel->secondary_images as $image)
                        <a href="{{ asset('storage/'.$image) }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-lg">
                            <img src="{{ asset('storage/'.$image) }}" alt="{{ $hotel->name }}" class="h-36 w-full object-cover transition hover:scale-105">
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <p class="mt-3 text-sm text-slate-500">No secondary hotel images have been uploaded yet.</p>
        @endif
    </div>
    <div class="mt-8 grid gap-5 border-t border-slate-100 pt-6 lg:grid-cols-2">
        <div>
            <h2 class="font-bold text-slate-900">Hotel Policy</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $hotel->hotel_policy ?: 'No hotel policy added yet.' }}</p>
        </div>
        <div>
            <h2 class="font-bold text-slate-900">Room Policy</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $hotel->room_policy ?: 'No room policy added yet.' }}</p>
        </div>
    </div>
</section>

<div class="mb-4 mt-10 flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Hotel partner content</p>
        <h2 class="mt-1 text-2xl font-bold text-slate-900">Room Types &amp; Rooms</h2>
    </div>
    <p class="text-sm text-slate-500">{{ $hotel->roomTypes->count() }} room type(s), {{ $hotel->roomTypes->sum(fn ($roomType) => $roomType->rooms->count()) }} room(s)</p>
</div>

@forelse ($hotel->roomTypes as $roomType)
    <section class="mb-5 overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">{{ $roomType->name }}</h3>
            @if ($roomType->description)
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $roomType->description }}</p>
            @endif
        </div>
        <div class="grid gap-5 p-6">
            @forelse ($roomType->rooms as $room)
                <article class="rounded-xl border border-slate-200 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="text-base font-bold text-slate-900">{{ $room->name }}</h4>
                            <p class="mt-1 text-sm leading-6 text-slate-600 whitespace-pre-line">{{ $room->description }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $roomType->name }}</span>
                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50" onclick="openAdminRoomCalendar({{ $room->id }}, {{ json_encode($room->name) }})">
                                Calendar
                            </button>
                        </div>
                    </div>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Per Adult</dt>
                            <dd class="mt-1 font-bold text-slate-900">{{ $room->adult_cost !== null ? '₹'.number_format((float) $room->adult_cost, 2) : 'Not set' }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Per Child (0–12 years)</dt>
                            <dd class="mt-1 font-bold text-slate-900">{{ $room->child_cost !== null ? '₹'.number_format((float) $room->child_cost, 2) : 'Not set' }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Extra Bed</dt>
                            <dd class="mt-1 font-bold text-slate-900">{{ $room->extra_bed_cost !== null ? '₹'.number_format((float) $room->extra_bed_cost, 2) : 'Not set' }}</dd>
                        </div>
                    </dl>
                    <form method="POST" action="{{ route('admin.hotels.rooms.customer-prices.update', [$hotel, $room]) }}" class="mt-5 rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <h5 class="font-semibold text-slate-900">Customer prices</h5>
                            <p class="mt-1 text-sm text-slate-600">Set the amounts charged to the customer booking this room. These are separate from the hotel’s rates above.</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach ([
                                'customer_adult_price' => 'Per Adult / Night (₹)',
                                'customer_child_price' => 'Per Child / Night (0–12 years, ₹)',
                                'customer_extra_bed_price' => 'Extra Bed / Night (₹)',
                            ] as $field => $label)
                                <div>
                                    <label for="{{ $field }}-{{ $room->id }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
                                    <input id="{{ $field }}-{{ $room->id }}" name="{{ $field }}" type="number" min="0" max="99999999.99" step="0.01" value="{{ old($field, $room->{$field}) }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Customer Prices</button>
                        </div>
                    </form>
                    @if ($room->images)
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($room->images as $image)
                                <a href="{{ asset('storage/'.$image) }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-lg">
                                    <img src="{{ asset('storage/'.$image) }}" alt="{{ $room->name }}" class="h-32 w-full object-cover transition hover:scale-105">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-5 grid gap-5 md:grid-cols-3">
                        @foreach (['highlights' => 'Highlights', 'key_points' => 'Key Points', 'amenities' => 'Things Included / Amenities'] as $field => $label)
                            @if ($room->{$field})
                                <div>
                                    <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</h5>
                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $room->{$field} }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </article>
            @empty
                <p class="rounded-lg bg-slate-50 px-4 py-5 text-sm text-slate-500">No rooms have been added to this type yet.</p>
            @endforelse
        </div>
    </section>
@empty
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
        <i class="fa-solid fa-bed text-3xl text-slate-300"></i>
        <h3 class="mt-4 font-bold text-slate-900">No room details yet</h3>
        <p class="mt-1 text-sm text-slate-500">Room types and rooms created by this hotel will appear here.</p>
    </div>
@endforelse

<!-- Room Calendar Modal (shared across all rooms of this hotel) -->
<div id="admin-room-calendar-modal"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="admin-calendar-room-name"
     aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeAdminRoomCalendar()"></div>
    <div class="absolute inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div id="admin-calendar-panel"
                 class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 sm:max-w-lg"
                 tabindex="-1">
                <!-- Header -->
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 bg-slate-50 px-5 py-4">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Room Availability</p>
                        <h3 id="admin-calendar-room-name" class="truncate text-base font-bold text-slate-900">Room Calendar</h3>
                    </div>
                    <button type="button" aria-label="Close calendar" class="-mr-1 shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" onclick="closeAdminRoomCalendar()">
                        <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Calendar body -->
                <div class="px-4 pb-4 pt-4 sm:px-5">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1">
                            <button type="button" id="admin-calendar-prev-month" aria-label="Previous month" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600">
                                <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i>
                            </button>
                            <p id="admin-calendar-month-year" aria-live="polite" class="min-w-[8.5rem] text-center text-sm font-semibold text-slate-900">October 2026</p>
                            <button type="button" id="admin-calendar-next-month" aria-label="Next month" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600">
                                <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
                            </button>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50" onclick="resetAdminCalendarToToday()">
                            Today
                        </button>
                    </div>

                    <!-- Weekday headers -->
                    <div class="grid grid-cols-7 gap-1 pb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-400" aria-hidden="true">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>

                    <!-- Calendar grid -->
                    <div id="admin-calendar-grid" class="grid grid-cols-7 gap-1"></div>

                    <p id="admin-calendar-status" class="mt-3 hidden text-xs font-medium" role="status" aria-live="polite"></p>

                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-3 text-[11px] font-medium text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-slate-200 bg-white"></span>Available</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-red-200 bg-red-50"></span>Booked / blocked</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-slate-200 bg-slate-50"></span>Past date</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-5 py-3">
                    <p class="text-xs text-slate-500">Reflects the hotel partner’s calendar. Confirmed bookings and blocked dates appear here.</p>
                    <button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700" onclick="closeAdminRoomCalendar()">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const adminCalendarModal = document.getElementById('admin-room-calendar-modal');
    const adminCalendarPanel = document.getElementById('admin-calendar-panel');
    const adminCalendarGrid = document.getElementById('admin-calendar-grid');
    const adminCalendarMonthYear = document.getElementById('admin-calendar-month-year');
    const adminCalendarRoomName = document.getElementById('admin-calendar-room-name');
    const adminCalendarStatus = document.getElementById('admin-calendar-status');
    const adminCalendarPrevMonth = document.getElementById('admin-calendar-prev-month');
    const adminCalendarNextMonth = document.getElementById('admin-calendar-next-month');

    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    const weekdayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    let activeRoomId = null;
    let unavailableDates = new Set();
    let visibleMonth = new Date();
    let statusTimer = null;

    function toDateKey(date) {
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return date.getFullYear() + '-' + month + '-' + day;
    }

    function startOfToday() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        return today;
    }

    function setAdminCalendarStatus(message, type = 'error') {
        adminCalendarStatus.textContent = message;
        adminCalendarStatus.classList.toggle('hidden', message === '');
        adminCalendarStatus.classList.toggle('text-red-600', type === 'error');
        adminCalendarStatus.classList.toggle('text-emerald-600', type === 'success');

        if (statusTimer !== null) {
            window.clearTimeout(statusTimer);
            statusTimer = null;
        }

        if (message !== '' && type === 'success') {
            statusTimer = window.setTimeout(() => setAdminCalendarStatus(''), 4000);
        }
    }

    function openAdminRoomCalendar(roomId, roomName) {
        activeRoomId = roomId;
        visibleMonth = new Date();

        adminCalendarRoomName.textContent = roomName;
        unavailableDates = new Set();
        setAdminCalendarStatus('');
        renderAdminCalendar();

        adminCalendarModal.classList.remove('hidden');
        adminCalendarModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        adminCalendarPanel.focus({ preventScroll: true });

        refreshAdminRoomAvailability(roomId);
    }

    function closeAdminRoomCalendar() {
        adminCalendarModal.classList.add('hidden');
        adminCalendarModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        activeRoomId = null;
        setAdminCalendarStatus('');
    }

    function refreshAdminRoomAvailability(roomId) {
        adminCalendarGrid.classList.add('opacity-50');

        fetch('{{ route('admin.hotels.rooms.calendar', ['hotel' => $hotel, 'room' => '__ROOM__']) }}'.replace('__ROOM__', roomId), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => (response.ok ? response.json() : Promise.reject(new Error('unavailable'))))
            .then((data) => {
                if (activeRoomId !== roomId) {
                    return;
                }

                unavailableDates = new Set(data.unavailable_dates || []);
                renderAdminCalendar();
            })
            .catch(() => {
                if (activeRoomId === roomId) {
                    setAdminCalendarStatus('Live availability could not be loaded.');
                }
            })
            .finally(() => adminCalendarGrid.classList.remove('opacity-50'));
    }

    function resetAdminCalendarToToday() {
        visibleMonth = new Date();
        renderAdminCalendar();
    }

    function shiftAdminMonth(offset) {
        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + offset, 1);
        renderAdminCalendar();
    }

    function renderAdminCalendar() {
        const year = visibleMonth.getFullYear();
        const month = visibleMonth.getMonth();
        const firstWeekday = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPreviousMonth = new Date(year, month, 0).getDate();

        adminCalendarMonthYear.textContent = monthNames[month] + ' ' + year;

        const cells = [];

        for (let offset = firstWeekday; offset > 0; offset--) {
            const day = daysInPreviousMonth - offset + 1;
            cells.push(createAdminCalendarCell(day, new Date(year, month - 1, day), true));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            cells.push(createAdminCalendarCell(day, new Date(year, month, day), false));
        }

        for (let day = 1; cells.length < 42; day++) {
            cells.push(createAdminCalendarCell(day, new Date(year, month + 1, day), true));
        }

        adminCalendarGrid.replaceChildren(...cells);
    }

    function createAdminCalendarCell(day, date, isOutsideMonth) {
        const dateKey = toDateKey(date);
        const isPast = date < startOfToday();
        const isBlocked = unavailableDates.has(dateKey);

        const cell = document.createElement('button');
        cell.type = 'button';
        cell.dataset.date = dateKey;
        cell.textContent = String(day);
        cell.disabled = isOutsideMonth || isPast;
        cell.title = dateKey + (isBlocked ? ' - Booked/blocked' : ' - Available');
        cell.setAttribute('aria-label', weekdayNames[date.getDay()] + ' ' + day + ' ' + monthNames[date.getMonth()] + ' ' + date.getFullYear()
            + (isPast ? ', past date' : isBlocked ? ', booked' : ', available'));

        const classes = [
            'flex', 'aspect-square', 'items-center', 'justify-center', 'rounded-lg', 'text-sm', 'transition',
            'focus:outline-none', 'focus-visible:ring-2', 'focus-visible:ring-indigo-500', 'focus-visible:ring-offset-1',
        ];

        if (isOutsideMonth) {
            classes.push('cursor-default', 'text-slate-300');
        } else if (isPast) {
            classes.push('cursor-not-allowed', 'bg-slate-50', 'font-normal', 'text-slate-400');
        } else if (isBlocked) {
            classes.push('bg-red-50', 'font-semibold', 'text-red-600', 'ring-1', 'ring-inset', 'ring-red-200', 'hover:bg-red-100');
        } else {
            classes.push('font-medium', 'text-slate-700', 'hover:bg-indigo-50', 'hover:text-indigo-700');
        }

        cell.className = classes.join(' ');

        return cell;
    }

    adminCalendarPrevMonth.addEventListener('click', () => shiftAdminMonth(-1));
    adminCalendarNextMonth.addEventListener('click', () => shiftAdminMonth(1));

    document.addEventListener('keydown', (event) => {
        if (adminCalendarModal.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeAdminRoomCalendar();
        }

        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            shiftAdminMonth(event.key === 'ArrowLeft' ? -1 : 1);
        }
    });
</script>
@endsection
