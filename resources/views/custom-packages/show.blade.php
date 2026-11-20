@extends('layouts.app')

@section('title', $customPackage->name)

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    @php($statusClasses = ['draft' => 'bg-slate-100 text-slate-700', 'pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800'])
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Private custom package</p>
                <h1 class="mt-1 text-3xl font-black text-slate-900">{{ $customPackage->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $customPackage->departure_city }} &middot; {{ $customPackage->days }} days / {{ $customPackage->nights }} nights</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-bold capitalize {{ $statusClasses[$customPackage->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $customPackage->status }}</span>
        </div>

        @if($customPackage->description)<p class="mt-5 text-sm leading-6 text-slate-700">{{ $customPackage->description }}</p>@endif

        @if($customPackage->status === 'draft')
            <div class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                <p class="text-sm leading-6 text-indigo-900">Your package is private and saved in this browser. Continue to the booking form to send your itinerary and booking details to the admin for review.</p>
                <a href="{{ route('custom-packages.booking', $customPackage) }}" class="mt-3 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Continue to booking form</a>
            </div>
        @elseif($customPackage->status === 'pending')
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Your package request has been sent. The admin team will review it and contact you using the booking details below.</div>
        @endif

        @if($customPackage->admin_response)
            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-extrabold text-slate-800">Admin response</h2>
                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $customPackage->admin_response }}</p>
            </div>
        @endif

        @if($customPackage->admin_quote !== null || $customPackage->admin_cost_notes)
            <section class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-indigo-700">Package quote</p><p class="mt-1 text-2xl font-black text-indigo-950">{{ $customPackage->admin_quote !== null ? '₹'.number_format((float) $customPackage->admin_quote, 2) : 'Being prepared' }}</p></div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-600">Cost details</p><p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $customPackage->admin_cost_notes ?: 'The team will share the detailed cost breakdown.' }}</p></div>
            </section>
        @endif

        <section class="mt-8">
            <h2 class="text-lg font-extrabold text-slate-900">Your itinerary</h2>
            <div class="mt-3 space-y-4">
                @foreach($customPackage->itinerary as $day)
                    <article class="rounded-xl border border-slate-200 p-4">
                        <h3 class="font-bold text-slate-900">Day {{ $day['day_number'] }}{{ $day['title'] ? ' · '.$day['title'] : '' }}</h3>
                        @if($day['description'])<p class="mt-1 text-sm text-slate-600">{{ $day['description'] }}</p>@endif
                        @if(!empty($day['start_time']) || !empty($day['total_duration_hours']))
                            <p class="mt-2 text-xs font-semibold text-indigo-700">
                                @if(!empty($day['start_time'])) Starts {{ substr((string) $day['start_time'], 0, 5) }} @endif
                                @if(!empty($day['total_duration_hours'])) · Estimated visit time {{ floor((float) $day['total_duration_hours']) }}h {{ ((int) round(((float) $day['total_duration_hours'] - floor((float) $day['total_duration_hours'])) * 60)) }}m @endif
                            </p>
                        @endif
                        <div class="mt-3 space-y-2">
                            @foreach($day['activities'] ?? [] as $activity)
                                @if(($activity['type'] ?? '') === 'place')
                                    <div class="flex gap-3 rounded-lg bg-slate-50 p-3">
                                        @if($activity['image_path'])<img src="{{ asset('storage/'.$activity['image_path']) }}" alt="" class="h-16 w-20 rounded-md object-cover">@endif
                                        <div><p class="text-sm font-bold text-slate-800">{{ $activity['name'] }}</p><p class="text-xs text-slate-500">{{ $activity['city'] }}, {{ $activity['state'] }}</p><p class="mt-1 text-xs text-slate-600">{{ $activity['description'] }}</p></div>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-emerald-100 bg-emerald-50/60 p-3">
                                        <p class="text-sm font-bold text-emerald-900">{{ $activity['name'] }}</p>
                                        @if($activity['description'])<p class="mt-1 text-xs text-slate-600">{{ $activity['description'] }}</p>@endif
                                        @if(!empty($activity['duration_hours']))<p class="mt-1 text-xs text-emerald-800">{{ $activity['duration_hours'] }} hours</p>@endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-8">
            <h2 class="text-lg font-extrabold text-slate-900">Your inclusions</h2>
            @if(count($customPackage->inclusions ?? []))
                <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach($customPackage->inclusions as $inclusion)
                        <li class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4"><p class="font-bold text-emerald-900">{{ $inclusion['name'] }}</p>@if($inclusion['description'])<p class="mt-1 text-sm text-slate-600">{{ $inclusion['description'] }}</p>@endif</li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2 text-sm text-slate-500">No custom inclusions added.</p>
            @endif
        </section>

        @if($customPackage->booking_details)
            <section class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="font-extrabold text-slate-900">Booking details sent to the admin</h2>
                <div class="mt-3 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
                    <p><span class="font-semibold">Name:</span> {{ $customPackage->booking_details['customer_name'] }}</p>
                    <p><span class="font-semibold">Email:</span> {{ $customPackage->booking_details['email'] }}</p>
                    <p><span class="font-semibold">Mobile:</span> {{ $customPackage->booking_details['mobile'] }}</p>
                    <p><span class="font-semibold">Travel date:</span> {{ ($customPackage->booking_details['departure_date'] ?? null) ?: 'Flexible' }}</p>
                    <p><span class="font-semibold">Travelers:</span> {{ $customPackage->booking_details['adult_count'] }} adults, {{ $customPackage->booking_details['child_count'] }} children</p>
                </div>
                @if($customPackage->booking_details['message'] ?? null)<p class="mt-3 text-sm text-slate-600"><span class="font-semibold">Message:</span> {{ $customPackage->booking_details['message'] }}</p>@endif
            </section>
        @endif

        <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('custom-packages.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">My custom packages</a>
            <a href="{{ route('packages.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Browse regular packages</a>
        </div>
    </div>
</div>
@endsection
