@extends('layouts.hotel')
@section('title', 'Rooms')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Room setup</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Rooms</h1>
        <p class="mt-2 text-slate-500">Manage room details, photos, highlights, key points, and amenities.</p>
    </div>
    <a href="{{ route('hotel.rooms.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fa-solid fa-plus mr-2"></i>Add Room</a>
</div>
<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    @if ($rooms->isEmpty())
        <div class="px-6 py-14 text-center">
            <i class="fa-solid fa-bed text-3xl text-slate-300"></i>
            <h2 class="mt-4 font-bold text-slate-900">No rooms yet</h2>
            <p class="mt-1 text-sm text-slate-500">Create a room type, then add your first room.</p>
            <a href="{{ route('hotel.room-types.index') }}" class="mt-4 inline-flex font-semibold text-indigo-600 hover:text-indigo-800">Manage Room Types<i class="fa-solid fa-arrow-right ml-2 mt-1 text-xs"></i></a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-6 py-4">Room</th><th class="px-6 py-4">Room Type</th><th class="px-6 py-4">Adult</th><th class="px-6 py-4">Child (0–12)</th><th class="px-6 py-4">Extra Bed</th><th class="px-6 py-4">Description</th><th class="px-6 py-4 text-right">Actions</th><th class="px-6 py-4 text-right">Calendar</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rooms as $room)
                        <?php $roomUnavailableDates = $unavailableDates[$room->id] ?? collect(); ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if (! empty($room->images))
                                        <img src="{{ asset('storage/'.$room->images[0]) }}" alt="{{ $room->name }}" class="h-14 w-20 rounded-lg object-cover">
                                    @else
                                        <span class="flex h-14 w-20 items-center justify-center rounded-lg bg-slate-100 text-slate-400"><i class="fa-solid fa-image"></i></span>
                                    @endif
                                    <div><p class="font-semibold text-slate-900">{{ $room->name }}</p><p class="mt-1 text-xs text-slate-500">{{ count($room->images ?? []) }} image(s)</p></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $room->roomType->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $room->adult_cost !== null ? '₹'.number_format((float) $room->adult_cost, 2) : 'Not set' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $room->child_cost !== null ? '₹'.number_format((float) $room->child_cost, 2) : 'Not set' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $room->extra_bed_cost !== null ? '₹'.number_format((float) $room->extra_bed_cost, 2) : 'Not set' }}</td>
                            <td class="max-w-sm px-6 py-4 text-slate-600">{{ \Illuminate\Support\Str::limit($room->description, 110) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('hotel.rooms.edit', $room) }}" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('hotel.rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room and its uploaded photos?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button type="button"
                                        class="rounded-lg border border-indigo-200 px-3 py-2 font-semibold text-indigo-700 hover:bg-indigo-50"
                                        onclick="openRoomCalendar({{ $room->id }}, '{{ $room->name }}')">
                                    Calendar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $rooms->links() }}</div>
    @endif
</div>

<!-- Room Calendar Modal -->
<div id="room-calendar-modal"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="calendar-room-name"
     aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeRoomCalendar()"></div>
    <div class="absolute inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div id="calendar-panel"
                 class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 sm:max-w-lg"
                 tabindex="-1">

                <!-- Header -->
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 bg-slate-50 px-5 py-4">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Availability</p>
                        <h3 id="calendar-room-name" class="truncate text-base font-bold text-slate-900">Room Calendar</h3>
                    </div>
                    <button type="button" aria-label="Close calendar" class="-mr-1 shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" onclick="closeRoomCalendar()">
                        <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Calendar body -->
                <div class="px-4 pb-4 pt-4 sm:px-5">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1">
                            <button type="button" id="calendar-prev-month" aria-label="Previous month" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600">
                                <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i>
                            </button>
                            <p id="calendar-month-year" aria-live="polite" class="min-w-[8.5rem] text-center text-sm font-semibold text-slate-900">October 2026</p>
                            <button type="button" id="calendar-next-month" aria-label="Next month" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600">
                                <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
                            </button>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50" onclick="resetCalendarToToday()">
                            Today
                        </button>
                    </div>

                    <!-- Weekday headers -->
                    <div class="grid grid-cols-7 gap-1 pb-1 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-400" aria-hidden="true">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>

                    <!-- Calendar grid -->
                    <div id="calendar-grid" class="grid grid-cols-7 gap-1"></div>

                    <p id="calendar-status" class="mt-3 hidden text-xs font-medium" role="status" aria-live="polite"></p>

                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-3 text-[11px] font-medium text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-slate-200 bg-white"></span>Available</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-red-200 bg-red-50"></span>Booked / blocked</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-slate-200 bg-slate-50"></span>Past date</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-5 py-3">
                    <p class="text-xs text-slate-500">Click a date to block or reopen it.</p>
                    <button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2" onclick="closeRoomCalendar()">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const calendarModal = document.getElementById('room-calendar-modal');
    const calendarPanel = document.getElementById('calendar-panel');
    const calendarGrid = document.getElementById('calendar-grid');
    const calendarMonthYear = document.getElementById('calendar-month-year');
    const calendarRoomName = document.getElementById('calendar-room-name');
    const calendarStatus = document.getElementById('calendar-status');
    const calendarPrevMonth = document.getElementById('calendar-prev-month');
    const calendarNextMonth = document.getElementById('calendar-next-month');

    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    const weekdayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    const roomUnavailableDates = @json($unavailableDates->map(fn ($dates) => $dates->all())->all());
    const calendarEndpoints = {
        show: '{{ route('hotel.rooms.calendar', ['room' => '__ROOM__']) }}',
        toggle: '{{ route('hotel.rooms.calendar.toggle', ['room' => '__ROOM__']) }}',
    };

    let activeRoomId = null;
    let unavailableDates = new Set();
    let visibleMonth = new Date();
    let isSavingDate = false;
    let lastFocusedElement = null;
    let statusTimer = null;

    /** Route URLs are generated with a placeholder so room ids never collide with digits in the host. */
    function endpointFor(endpoint, roomId) {
        return endpoint.replace('__ROOM__', roomId);
    }

    /** Availability is stored as a plain Y-m-d date, so the key must be local, not UTC. */
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

    function setCalendarStatus(message, type = 'error') {
        calendarStatus.textContent = message;
        calendarStatus.classList.toggle('hidden', message === '');
        calendarStatus.classList.toggle('text-red-600', type === 'error');
        calendarStatus.classList.toggle('text-emerald-600', type === 'success');

        if (statusTimer !== null) {
            window.clearTimeout(statusTimer);
            statusTimer = null;
        }

        if (message !== '' && type === 'success') {
            statusTimer = window.setTimeout(() => setCalendarStatus(''), 4000);
        }
    }

    function openRoomCalendar(roomId, roomName) {
        activeRoomId = roomId;
        lastFocusedElement = document.activeElement;
        visibleMonth = new Date();

        calendarRoomName.textContent = roomName;
        unavailableDates = new Set(roomUnavailableDates[String(roomId)] || []);
        setCalendarStatus('');
        renderCalendar();

        calendarModal.classList.remove('hidden');
        calendarModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        calendarPanel.focus({ preventScroll: true });

        refreshRoomAvailability(roomId);
    }

    function closeRoomCalendar() {
        calendarModal.classList.add('hidden');
        calendarModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        activeRoomId = null;
        setCalendarStatus('');

        if (lastFocusedElement instanceof HTMLElement) {
            lastFocusedElement.focus({ preventScroll: true });
        }
    }

    function refreshRoomAvailability(roomId) {
        calendarGrid.classList.add('opacity-50');

        fetch(endpointFor(calendarEndpoints.show, roomId), {
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
                renderCalendar();
            })
            .catch(() => {
                if (activeRoomId === roomId) {
                    setCalendarStatus('Live availability could not be loaded. Showing the dates saved with this page.');
                }
            })
            .finally(() => calendarGrid.classList.remove('opacity-50'));
    }

    function resetCalendarToToday() {
        visibleMonth = new Date();
        renderCalendar();
    }

    function shiftMonth(offset) {
        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + offset, 1);
        renderCalendar();
    }

    function renderCalendar() {
        const year = visibleMonth.getFullYear();
        const month = visibleMonth.getMonth();
        const firstWeekday = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPreviousMonth = new Date(year, month, 0).getDate();

        calendarMonthYear.textContent = monthNames[month] + ' ' + year;

        const cells = [];

        for (let offset = firstWeekday; offset > 0; offset--) {
            const day = daysInPreviousMonth - offset + 1;
            cells.push(createCalendarCell(day, new Date(year, month - 1, day), true));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            cells.push(createCalendarCell(day, new Date(year, month, day), false));
        }

        for (let day = 1; cells.length < 42; day++) {
            cells.push(createCalendarCell(day, new Date(year, month + 1, day), true));
        }

        calendarGrid.replaceChildren(...cells);
    }

    function createCalendarCell(day, date, isOutsideMonth) {
        const dateKey = toDateKey(date);
        const isPast = date < startOfToday();
        const isToday = date.getTime() === startOfToday().getTime();
        const isBlocked = unavailableDates.has(dateKey);
        const isInteractive = ! isOutsideMonth && ! isPast;

        const cell = document.createElement('button');
        cell.type = 'button';
        cell.dataset.date = dateKey;
        cell.textContent = String(day);
        cell.disabled = ! isInteractive;
        cell.title = dateKey + (isBlocked ? ' - Blocked' : ' - Available');
        cell.setAttribute('aria-pressed', String(isBlocked));
        cell.setAttribute(
            'aria-label',
            weekdayNames[date.getDay()] + ' ' + day + ' ' + monthNames[date.getMonth()] + ' ' + date.getFullYear()
                + (isPast ? ', past date' : isBlocked ? ', blocked' : ', available'),
        );

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

        if (isToday) {
            classes.push('font-bold', 'outline', 'outline-2', 'outline-offset-1', 'outline-indigo-500');
        }

        cell.className = classes.join(' ');

        if (isInteractive) {
            cell.addEventListener('click', () => toggleDate(dateKey, isBlocked));
        }

        return cell;
    }

    function toggleDate(dateKey, isBlocked) {
        if (activeRoomId === null || isSavingDate) {
            return;
        }

        const roomId = activeRoomId;
        isSavingDate = true;
        calendarGrid.classList.add('pointer-events-none', 'opacity-60');

        fetch(endpointFor(calendarEndpoints.toggle, roomId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ date: dateKey, action: isBlocked ? 'unblock' : 'block' }),
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(payload.message || 'This date could not be updated.');
                }

                return payload;
            })
            .then((payload) => {
                unavailableDates = new Set(payload.unavailable_dates || []);
                setCalendarStatus(payload.message || '', 'success');
                renderCalendar();
            })
            .catch((error) => setCalendarStatus(error.message || 'This date could not be updated.'))
            .finally(() => {
                isSavingDate = false;
                calendarGrid.classList.remove('pointer-events-none', 'opacity-60');
            });
    }

    function trapCalendarFocus(event) {
        const focusable = Array.from(calendarModal.querySelectorAll('button:not([disabled])'));

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (! event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    calendarPrevMonth.addEventListener('click', () => shiftMonth(-1));
    calendarNextMonth.addEventListener('click', () => shiftMonth(1));

    document.addEventListener('keydown', (event) => {
        if (calendarModal.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeRoomCalendar();

            return;
        }

        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            shiftMonth(event.key === 'ArrowLeft' ? -1 : 1);
        }

        if (event.key === 'Tab') {
            trapCalendarFocus(event);
        }
    });
</script>
@endsection
