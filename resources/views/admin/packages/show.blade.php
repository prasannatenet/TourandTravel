@extends('layouts.admin')
@section('title', $package->name)
@section('header', $package->name)
@section('subheader', 'Complete package details, inclusions, destinations, and day plan.')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50"><i class="fa-solid fa-arrow-left text-xs"></i> Back to packages</a>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold {{ $package->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $package->is_active ? 'Active' : 'Draft' }}</span>
            <a href="{{ route('admin.packages.edit', $package) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"><i class="fa-regular fa-pen-to-square text-xs"></i> Edit package</a>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($package->image)
            <img src="{{ asset('storage/'.$package->image) }}" alt="{{ $package->name }}" class="h-56 w-full object-cover sm:h-72">
        @endif
        @if($package->galleryImages->isNotEmpty())
            <div class="grid grid-cols-2 gap-2 p-3 sm:grid-cols-4">
                @foreach($package->galleryImages as $galleryImage)
                    <img src="{{ asset('storage/'.$galleryImage->image) }}" alt="{{ $package->name }} gallery photo" class="h-28 w-full rounded-lg object-cover sm:h-36">
                @endforeach
            </div>
        @endif
        <div class="p-5 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Tour package</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $package->name }}</h2>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if($package->category)<span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">{{ $package->category }}</span>@endif
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-700">{{ $package->tour_type }} tour</span>
                    </div>
                    @if($package->description)
                        <p class="mt-3 max-w-3xl whitespace-pre-line text-sm leading-6 text-slate-600">{{ $package->description }}</p>
                    @endif
                </div>
            </div>
            <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Departure city</dt><dd class="mt-1 font-semibold text-slate-800">{{ $package->departure_city ?: 'Not specified' }}</dd></div>
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Adult price / person</dt><dd class="mt-1 font-semibold text-slate-800">₹{{ number_format((float) $package->base_price, 2) }}</dd>@if($package->child_price !== null)<dd class="mt-1 text-xs text-slate-600">Child: ₹{{ number_format((float) $package->child_price, 2) }}</dd>@endif @if($package->infant_price !== null)<dd class="mt-1 text-xs text-slate-600">Infant: ₹{{ number_format((float) $package->infant_price, 2) }}</dd>@endif</div>
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Duration</dt><dd class="mt-1 font-semibold text-slate-800">{{ $package->days }} days / {{ $package->nights }} nights</dd></div>
                <div class="rounded-xl bg-indigo-50 p-4"><dt class="text-xs text-indigo-600">Estimated adult total / person</dt><dd class="mt-1 font-bold text-indigo-800">₹{{ number_format($estimatedTotal, 2) }}</dd><dd class="mt-1 text-xs text-indigo-600">{{ $package->discount_percentage }}% discount · {{ $package->tax_percentage }}% tax</dd></div>
            </dl>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-4"><h3 class="text-base font-bold text-slate-900">Departure dates and seat limits</h3><p class="mt-1 text-sm text-slate-500">Configured departure capacity and date-specific adult prices.</p></div>
        @if($package->availabilities->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">No departure dates configured.</p>
        @else
            <div class="overflow-x-auto"><table class="w-full min-w-[36rem] text-left text-sm"><thead><tr class="border-b border-slate-200 text-xs uppercase text-slate-500"><th class="px-3 py-2">Departure date</th><th class="px-3 py-2">Seat capacity</th><th class="px-3 py-2">Adult price / person</th></tr></thead><tbody>
                @foreach($package->availabilities as $departure)
                    <tr class="border-b border-slate-100"><td class="px-3 py-3 font-medium text-slate-800">{{ $departure->departure_date->format('d M Y') }}</td><td class="px-3 py-3 text-slate-700">{{ number_format($departure->seat_limit) }} seats</td><td class="px-3 py-3 text-slate-700">₹{{ number_format((float) ($departure->adult_price_override ?? $package->base_price), 2) }}{{ $departure->adult_price_override !== null ? ' (date price)' : ' (standard)' }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div><h3 class="text-base font-bold text-slate-900">Package inclusions</h3><p class="mt-1 text-sm text-slate-500">Package-specific meals, vehicles, and services.</p></div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $package->inclusionItems->sum('quantity') }} included</span>
        </div>
        @if($package->inclusionItems->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">No inclusions selected.</p>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach($package->inclusionItems as $item)
                    @php $inclusionDetail = $inclusionDetails[$item->id] ?? null; @endphp
                    <article class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start gap-3">
                            @if($inclusionDetail && ($inclusionDetail['image_one'] || $inclusionDetail['image_two']))
                                <div class="flex shrink-0 gap-1.5">
                                    @foreach([$inclusionDetail['image_one'], $inclusionDetail['image_two']] as $imagePath)
                                        @if($imagePath)<img src="{{ asset('storage/'.$imagePath) }}" alt="{{ $inclusionDetail['name'] ?? $item->inclusion->name }}" class="h-14 w-20 rounded-lg object-cover">@endif
                                    @endforeach
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-semibold text-slate-800">{{ $inclusionDetail['name'] ?? $item->inclusion->name }}</h4>
                                <p class="mt-1 text-xs text-slate-500">Quantity: {{ $item->quantity }}</p>
                                @if($inclusionDetail && !empty($inclusionDetail['duration_hours']))
                                    <p class="mt-1 text-xs text-indigo-600"><i class="fa-regular fa-hourglass-half mr-1"></i>{{ $inclusionDetail['duration_hours'] }} {{ (float) $inclusionDetail['duration_hours'] === 1.0 ? 'hour' : 'hours' }}</p>
                                @endif
                            </div>
                        </div>
                        @if($inclusionDetail && ($inclusionDetail['place_from'] || $inclusionDetail['place_to']))
                            <p class="mt-3 text-xs text-slate-500">{{ $inclusionDetail['place_from'] ?: 'Any location' }} â†’ {{ $inclusionDetail['place_to'] ?: '' }}</p>
                        @endif
                        @if($inclusionDetail && !empty($inclusionDetail['description']))
                            <p class="mt-2 whitespace-pre-line text-sm leading-5 text-slate-600">{{ $inclusionDetail['description'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div><h3 class="text-base font-bold text-slate-900">Destinations</h3><p class="mt-1 text-sm text-slate-500">Countries, states, and cities selected for this package.</p></div>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $package->destinations->count() }} selected</span>
        </div>
        @if($package->destinations->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">No destinations selected.</p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach($package->destinations as $destination)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1.5 text-sm text-indigo-800"><span>{{ $destination->destination?->name ?? $destination->destination_id }}</span><span class="text-xs capitalize text-indigo-500">{{ strtolower(class_basename($destination->destination_type)) }}</span></span>
                @endforeach
            </div>
        @endif
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-4"><h3 class="text-base font-bold text-slate-900">Day-by-day plan</h3><p class="mt-1 text-sm text-slate-500">Scheduled visits and inclusions for each day.</p></div>
        @if($package->planItems->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">No plan items added.</p>
        @else
            <div class="space-y-5">
                @foreach($package->planItems->groupBy('day_number') as $dayNumber => $items)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h4 class="mb-3 text-sm font-bold text-slate-800">Day {{ $dayNumber }}</h4>
                        <div class="space-y-3">
                            @foreach($items as $item)
                                @if($item->package_inclusion_id)
                                    <article class="flex gap-3 rounded-lg bg-emerald-50/60 p-3">
                                        <span class="w-16 shrink-0 pt-0.5 text-xs font-semibold text-emerald-700">{{ substr((string) $item->start_time, 0, 5) }}</span>
                                        <div class="min-w-0">
@php $planInclusionDetail = $inclusionDetails[$item->package_inclusion_package_id] ?? null; @endphp
                                            <h5 class="text-sm font-semibold text-slate-800">{{ $planInclusionDetail['name'] ?? $item->packageInclusion?->name ?? 'Package inclusion' }}</h5>
                                            @if(isset($planInclusionDetail) && ($planInclusionDetail['place_from'] || $planInclusionDetail['place_to']))<p class="mt-1 text-xs text-slate-500">{{ $planInclusionDetail['place_from'] ?: 'Any location' }}{{ $planInclusionDetail['place_to'] ? ' → '.$planInclusionDetail['place_to'] : '' }}</p>@endif
                                            @if(!empty($planInclusionDetail['duration_hours']))<p class="mt-1 text-xs text-indigo-600">Duration: {{ $planInclusionDetail['duration_hours'] }} {{ (float) $planInclusionDetail['duration_hours'] === 1.0 ? 'hour' : 'hours' }}</p>@endif
                                        </div>
                                    </article>
                                @else
                                    <article class="flex gap-3 rounded-lg bg-slate-50 p-3">
                                        <span class="w-16 shrink-0 pt-0.5 text-xs font-semibold text-indigo-700">{{ substr((string) $item->start_time, 0, 5) }}</span>
                                        <div class="min-w-0 flex-1">
                                            <h5 class="text-sm font-semibold text-slate-800">{{ $item->place?->name ?? 'Place stop' }}</h5>
                                            @if($item->place?->city)<p class="mt-1 text-xs text-slate-500">{{ $item->place->city->name }}</p>@endif
                                            @if((float) $item->additional_price > 0)<p class="mt-1 text-xs text-slate-500">Extra cost: ₹{{ number_format((float) $item->additional_price, 2) }}</p>@endif
                                        </div>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
