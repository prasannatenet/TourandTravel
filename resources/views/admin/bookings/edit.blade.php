@extends('layouts.admin')

@section('title', 'Update Booking')
@section('header', 'Update Booking #'.$booking->id)
@section('subheader', 'Change package and guest details. The total will be recalculated when saved.')

@section('content')
<div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-sm">
    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label for="package_id" class="mb-1 block text-sm font-semibold">Package</label>
            <select id="package_id" name="package_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach ($packages as $package)
                    <option value="{{ $package->id }}" @selected((string) old('package_id', $booking->package_id) === (string) $package->id)>{{ $package->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-semibold">Booking status</label>
            <select id="status" name="status" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $booking->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="adult_count" class="mb-1 block text-sm font-semibold">Adults</label><input id="adult_count" type="number" name="adult_count" min="1" max="99" required value="{{ old('adult_count', $booking->adult_count) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
            <div><label for="child_count" class="mb-1 block text-sm font-semibold">Children (ages 3–15)</label><input id="child_count" type="number" name="child_count" min="0" max="99" required value="{{ old('child_count', $booking->child_count) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
            <div><label for="student_count" class="mb-1 block text-sm font-semibold">Students (included in adults)</label><input id="student_count" type="number" name="student_count" min="0" max="99" required value="{{ old('student_count', $booking->student_count) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
            <div><label for="departure_date" class="mb-1 block text-sm font-semibold">Package departure</label><select id="departure_date" name="departure_date" class="w-full rounded-lg border border-slate-300 px-3 py-2"><option value="">No configured date</option>@foreach ($packages->firstWhere('id', (int) old('package_id', $booking->package_id))?->availabilities ?? [] as $availability)<option value="{{ $availability->departure_date->format('Y-m-d') }}" @selected(old('departure_date', $booking->check_in_date?->format('Y-m-d')) === $availability->departure_date->format('Y-m-d'))>{{ $availability->departure_date->format('M d, Y') }}</option>@endforeach</select></div>
            <div><label for="room_count" class="mb-1 block text-sm font-semibold">Rooms requested</label><input id="room_count" type="number" name="room_count" min="1" max="20" required value="{{ old('room_count', $booking->room_count) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
            <div><label for="extra_bed_count" class="mb-1 block text-sm font-semibold">Extra beds</label><input id="extra_bed_count" type="number" name="extra_bed_count" min="0" max="20" required value="{{ old('extra_bed_count', $booking->extra_bed_count) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
        </div>
        <div class="flex flex-wrap justify-between gap-3 border-t pt-5">
            <a href="{{ route('admin.bookings.show', $booking) }}" class="rounded-lg bg-slate-100 px-4 py-2 font-semibold text-slate-700">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 font-semibold text-white hover:bg-indigo-700">Save Booking Changes</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const packageSelect = document.getElementById('package_id');
    const departureSelect = document.getElementById('departure_date');
    const packageDepartures = @json($packages->mapWithKeys(fn ($package): array => [$package->id => $package->availabilities->map(fn ($availability): array => ['value' => $availability->departure_date->format('Y-m-d'), 'label' => $availability->departure_date->format('M d, Y')])->all()]));

    packageSelect.addEventListener('change', function () {
        departureSelect.replaceChildren(new Option('No configured date', ''));
        (packageDepartures[packageSelect.value] || []).forEach(function (departure) {
            departureSelect.add(new Option(departure.label, departure.value));
        });
    });
</script>
@endpush
