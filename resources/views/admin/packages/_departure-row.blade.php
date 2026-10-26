<div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4" data-departure-row>
    @if(!empty($departure['id']))<input type="hidden" name="availabilities[{{ $index }}][id]" value="{{ $departure['id'] }}">@endif
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-600">Departure date <span class="text-red-500">*</span></label>
        <input type="date" name="availabilities[{{ $index }}][departure_date]" value="{{ $departure['departure_date'] ?? '' }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
        @error('availabilities.'.$index.'.departure_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-600">Seat capacity <span class="text-red-500">*</span></label>
        <input type="number" name="availabilities[{{ $index }}][seat_limit]" value="{{ $departure['seat_limit'] ?? 20 }}" required min="1" max="10000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
        @error('availabilities.'.$index.'.seat_limit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-600">Adult price on this date <span class="font-normal text-slate-400">(optional)</span></label>
        <div class="relative"><span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-400">₹</span><input type="number" name="availabilities[{{ $index }}][adult_price_override]" value="{{ $departure['adult_price_override'] ?? '' }}" min="0" step="0.01" placeholder="Use standard adult price" class="w-full rounded-lg border border-slate-300 py-2 pl-7 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"></div>
        @error('availabilities.'.$index.'.adult_price_override')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-end justify-end"><button type="button" data-remove-departure class="rounded-lg px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">Remove date</button></div>
</div>
