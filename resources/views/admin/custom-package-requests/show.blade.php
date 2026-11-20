@extends('layouts.admin')
@section('title', 'Review Custom Package')
@section('header', 'Custom Package Booking')
@section('subheader', 'Inspect the visitor itinerary and booking request, then send a response.')

@section('content')
<div class="space-y-5">
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>@endif
    <section class="rounded-2xl bg-white p-5 shadow sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div><h2 class="text-2xl font-black text-slate-900">{{ $customPackageRequest->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $customPackageRequest->departure_city }} &middot; {{ $customPackageRequest->days }} days / {{ $customPackageRequest->nights }} nights</p></div>
            <span class="rounded-full px-3 py-1 text-xs font-bold capitalize {{ $customPackageRequest->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($customPackageRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') }}">{{ $customPackageRequest->status }}</span>
        </div>
        @if($customPackageRequest->description)<p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $customPackageRequest->description }}</p>@endif
    </section>

    <section class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-indigo-700">Admin quote</p><p class="mt-1 text-2xl font-black text-indigo-950">{{ $customPackageRequest->admin_quote !== null ? '₹'.number_format((float) $customPackageRequest->admin_quote, 2) : 'Not quoted yet' }}</p><p class="mt-2 text-sm text-indigo-900">Estimated place admissions and state day charges: ₹{{ number_format($itineraryCost, 2) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Admin cost breakdown</p><p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $customPackageRequest->admin_cost_notes ?: 'No additional cost notes yet.' }}</p></div>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow sm:p-7">
        <h2 class="text-lg font-extrabold text-slate-900">Requested itinerary</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-indigo-50 p-4"><p class="text-xs font-semibold uppercase text-indigo-700">Estimated place and state costs</p><p class="mt-1 text-xl font-black text-indigo-950">₹{{ number_format($itineraryCost, 2) }}</p><p class="mt-1 text-xs text-indigo-800">Excludes hotels, transport, and services without a price.</p></div>
            <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase text-slate-500">Travelers</p><p class="mt-1 text-lg font-bold text-slate-900">{{ $customPackageRequest->booking_details['adult_count'] ?? $customPackageRequest->traveler_counts['adult_count'] ?? 1 }} adults, {{ $customPackageRequest->booking_details['child_count'] ?? $customPackageRequest->traveler_counts['child_count'] ?? 0 }} children</p></div>
            <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase text-slate-500">Trip schedule</p><p class="mt-1 text-lg font-bold text-slate-900">{{ $customPackageRequest->days }} days / {{ $customPackageRequest->nights }} nights</p></div>
        </div>
        <div class="mt-4 space-y-4">
            @foreach($customPackageRequest->itinerary as $day)
                <article class="rounded-xl border border-slate-200 p-4">
                    <h3 class="font-bold text-slate-900">Day {{ $day['day_number'] }}{{ $day['title'] ? ' · '.$day['title'] : '' }}</h3>
                    @if($day['description'])<p class="mt-1 text-sm text-slate-600">{{ $day['description'] }}</p>@endif
                    <p class="mt-1 text-xs font-semibold text-indigo-700">@if(!empty($day['start_time']))Starts {{ substr((string) $day['start_time'], 0, 5) }} · @endif{{ number_format((float) ($day['total_duration_hours'] ?? 0), 1) }} hours planned</p>
                    <ul class="mt-3 space-y-2">
                        @foreach($day['activities'] ?? ($day['places'] ?? []) as $activity)
                            @if(($activity['type'] ?? 'place') === 'place')
                                <li class="rounded-lg bg-slate-50 p-3"><p class="text-sm font-bold text-slate-800">{{ $activity['name'] }}</p><p class="text-xs text-slate-500">{{ $activity['city'] }}, {{ $activity['state'] }}, {{ $activity['country'] }}</p><p class="mt-1 text-xs text-slate-600">{{ $activity['description'] }}</p><div class="mt-2 flex flex-wrap gap-3 text-xs font-semibold text-slate-700"><span>Visit: {{ $activity['duration_hours'] ?? 0 }} hrs</span><span>Adult: ₹{{ number_format((float) ($activity['adult_price'] ?? 0), 2) }}</span><span>Child: ₹{{ number_format((float) ($activity['child_price'] ?? 0), 2) }}</span><span>State/day: ₹{{ number_format((float) ($activity['state_daily_price'] ?? 0), 2) }} per traveler</span></div></li>
                            @else
                                <li class="rounded-lg border border-emerald-100 bg-emerald-50/60 p-3">
                                    <p class="text-sm font-bold text-emerald-900">{{ $activity['name'] }}</p>
                                    @if($activity['description'])
                                        <p class="mt-1 text-xs text-slate-600">{{ $activity['description'] }}</p>
                                    @endif
                                    @if(!empty($activity['duration_hours']))
                                        <p class="mt-1 text-xs text-emerald-800">{{ $activity['duration_hours'] }} hours</p>
                                    @endif
                                    @if(!empty($activity['service_price']))
                                        <p class="mt-1 text-xs font-semibold text-emerald-800">Service cost: ₹{{ number_format((float) $activity['service_price'], 2) }} per package</p>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow sm:p-7">
        <h2 class="text-lg font-extrabold text-slate-900">Custom inclusions</h2>
        @forelse($customPackageRequest->inclusions ?? [] as $inclusion)
            <div class="mt-3 rounded-lg border border-emerald-100 bg-emerald-50/50 p-3"><p class="font-bold text-emerald-900">{{ $inclusion['name'] }}</p>@if($inclusion['description'])<p class="mt-1 text-sm text-slate-600">{{ $inclusion['description'] }}</p>@endif</div>
        @empty
            <p class="mt-2 text-sm text-slate-500">No custom inclusions provided.</p>
        @endforelse
    </section>

    @if($customPackageRequest->booking_details)
        <section class="rounded-2xl bg-white p-5 shadow sm:p-7">
            <h2 class="text-lg font-extrabold text-slate-900">Booking inquiry</h2>
            <div class="mt-3 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                <p><strong>Name:</strong> {{ $customPackageRequest->booking_details['customer_name'] }}</p>
                <p><strong>Email:</strong> {{ $customPackageRequest->booking_details['email'] }}</p>
                <p><strong>Mobile:</strong> {{ $customPackageRequest->booking_details['mobile'] }}</p>
                <p><strong>Preferred date:</strong> {{ ($customPackageRequest->booking_details['departure_date'] ?? null) ?: 'Flexible' }}</p>
                <p><strong>Travelers:</strong> {{ $customPackageRequest->booking_details['adult_count'] }} adults, {{ $customPackageRequest->booking_details['child_count'] }} children</p>
            </div>
            @if($customPackageRequest->booking_details['message'] ?? null)<p class="mt-3 whitespace-pre-line text-sm text-slate-600"><strong>Message:</strong> {{ $customPackageRequest->booking_details['message'] }}</p>@endif
        </section>
    @endif

    @if($customPackageRequest->admin_response)
        <section class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5"><h2 class="font-extrabold text-emerald-900">Message to customer</h2><p class="mt-2 whitespace-pre-line text-sm text-emerald-900">{{ $customPackageRequest->admin_response }}</p></section>
    @endif

    @if($errors->any())<div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="rounded-2xl bg-white p-5 shadow sm:p-7">
        <h2 class="text-lg font-extrabold text-slate-900">Edit package and send quote</h2>
        <p class="mt-1 text-sm text-slate-500">Update the package summary and day notes, enter the final price, then approve or decline the request.</p>
        <form method="POST" action="{{ route('admin.custom-package-requests.update', $customPackageRequest) }}" class="mt-5 space-y-5">
            @csrf @method('PATCH')
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label for="name" class="mb-1 block text-xs font-semibold text-slate-600">Package name</label><input id="name" name="name" value="{{ old('name', $customPackageRequest->name) }}" required maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="departure_city" class="mb-1 block text-xs font-semibold text-slate-600">Departure city</label><input id="departure_city" name="departure_city" value="{{ old('departure_city', $customPackageRequest->departure_city) }}" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="days" class="mb-1 block text-xs font-semibold text-slate-600">Days</label><input id="days" name="days" type="number" min="1" max="30" value="{{ old('days', $customPackageRequest->days) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label for="nights" class="mb-1 block text-xs font-semibold text-slate-600">Nights</label><input id="nights" name="nights" type="number" min="0" max="29" value="{{ old('nights', $customPackageRequest->nights) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div class="sm:col-span-2"><label for="description" class="mb-1 block text-xs font-semibold text-slate-600">Package description</label><textarea id="description" name="description" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $customPackageRequest->description) }}</textarea></div>
            </div>
            <div class="space-y-3">
                <h3 class="font-bold text-slate-900">Edit day headings and notes</h3>
                @foreach($customPackageRequest->itinerary as $index => $day)
                    <div class="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-2">
                        <p class="text-sm font-bold text-slate-800 sm:col-span-2">Day {{ $day['day_number'] }}</p>
                        <div><label for="day-title-{{ $index }}" class="mb-1 block text-xs font-semibold text-slate-600">Day heading</label><input id="day-title-{{ $index }}" name="day_titles[{{ $index }}]" value="{{ old('day_titles.'.$index, $day['title'] ?? '') }}" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                        <div><label for="day-note-{{ $index }}" class="mb-1 block text-xs font-semibold text-slate-600">Day notes</label><textarea id="day-note-{{ $index }}" name="day_notes[{{ $index }}]" rows="2" maxlength="1000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('day_notes.'.$index, $day['description'] ?? '') }}</textarea></div>
                        @foreach($day['activities'] ?? [] as $activityIndex => $activity)
                            <div class="space-y-3 rounded-lg bg-slate-50 p-3 sm:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-wide {{ ($activity['type'] ?? 'place') === 'place' ? 'text-indigo-700' : 'text-emerald-700' }}">{{ ($activity['type'] ?? 'place') === 'place' ? 'Place' : 'Service' }} {{ $activityIndex + 1 }}</p>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div><label class="mb-1 block text-xs font-semibold text-slate-600">Name</label><input name="activities[{{ $index }}][{{ $activityIndex }}][name]" value="{{ old('activities.'.$index.'.'.$activityIndex.'.name', $activity['name']) }}" required maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    <div><label class="mb-1 block text-xs font-semibold text-slate-600">Duration (hours)</label><input name="activities[{{ $index }}][{{ $activityIndex }}][duration_hours]" type="number" min="0" max="72" step="0.25" value="{{ old('activities.'.$index.'.'.$activityIndex.'.duration_hours', $activity['duration_hours'] ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    @if(($activity['type'] ?? 'place') === 'place')
                                        <div><label class="mb-1 block text-xs font-semibold text-slate-600">Adult entry (₹ / person)</label><input name="activities[{{ $index }}][{{ $activityIndex }}][adult_price]" type="number" min="0" step="0.01" value="{{ old('activities.'.$index.'.'.$activityIndex.'.adult_price', $activity['adult_price'] ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                        <div><label class="mb-1 block text-xs font-semibold text-slate-600">Child entry (₹ / person)</label><input name="activities[{{ $index }}][{{ $activityIndex }}][child_price]" type="number" min="0" step="0.01" value="{{ old('activities.'.$index.'.'.$activityIndex.'.child_price', $activity['child_price'] ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                        <div><label class="mb-1 block text-xs font-semibold text-slate-600">State charge (₹ / traveler / day)</label><input name="activities[{{ $index }}][{{ $activityIndex }}][state_daily_price]" type="number" min="0" step="0.01" value="{{ old('activities.'.$index.'.'.$activityIndex.'.state_daily_price', $activity['state_daily_price'] ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    @else
                                        <div><label class="mb-1 block text-xs font-semibold text-slate-600">Service cost (₹ / package)</label><input name="activities[{{ $index }}][{{ $activityIndex }}][service_price]" type="number" min="0" step="0.01" value="{{ old('activities.'.$index.'.'.$activityIndex.'.service_price', $activity['service_price'] ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                                    @endif
                                    <div class="sm:col-span-2 lg:col-span-3"><label class="mb-1 block text-xs font-semibold text-slate-600">Description</label><textarea name="activities[{{ $index }}][{{ $activityIndex }}][description]" rows="2" maxlength="1000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('activities.'.$index.'.'.$activityIndex.'.description', $activity['description'] ?? '') }}</textarea></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label for="admin_quote" class="mb-1 block text-xs font-semibold text-slate-600">Final package quote (₹)</label><input id="admin_quote" name="admin_quote" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('admin_quote', $customPackageRequest->admin_quote) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Enter total quote"></div>
                <div><label for="admin_cost_notes" class="mb-1 block text-xs font-semibold text-slate-600">Cost breakdown / inclusions</label><textarea id="admin_cost_notes" name="admin_cost_notes" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Hotel, transport, meals, service costs, or exclusions">{{ old('admin_cost_notes', $customPackageRequest->admin_cost_notes) }}</textarea></div>
                <div class="sm:col-span-2"><label for="admin_response" class="mb-1 block text-xs font-semibold text-slate-600">Message to customer</label><textarea id="admin_response" name="admin_response" rows="4" maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('admin_response', $customPackageRequest->admin_response) }}</textarea></div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button name="status" value="{{ $customPackageRequest->status }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Save changes and email customer</button>
                <button name="status" value="approved" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Save and approve</button>
                <button name="status" value="rejected" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-700">Save and decline</button>
            </div>
        </form>
    </section>

    <a href="{{ route('admin.bookings.index') }}" class="inline-flex rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-white">Back to bookings</a>
</div>
@endsection
