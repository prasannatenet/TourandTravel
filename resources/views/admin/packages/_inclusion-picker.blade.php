<div>
    <div class="mb-3 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
        <div>
            <h4 class="text-sm font-semibold text-slate-700">Package inclusion entries</h4>
            <p class="mt-1 text-xs text-slate-500">Each entry is independent. Add the same service again to give it separate details or schedule.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.inclusions.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Create catalog inclusion</a>
            <button type="button" data-add-package-inclusion class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                <i class="fa-solid fa-plus text-xs"></i> Add inclusion
            </button>
        </div>
    </div>

    @if($inclusions->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
            No inclusions have been created. <a href="{{ route('admin.inclusions.create') }}" class="font-medium text-indigo-600">Create one</a> to add meals, vehicles, and other services to packages.
        </div>
    @else
        <div id="package-inclusion-list" class="space-y-3">
            @foreach($packageInclusionRows as $inclusionKey => $packageInclusionRow)
                @include('admin.packages._package-inclusion-row', [
                    'inclusionKey' => $inclusionKey,
                    'packageInclusionRow' => $packageInclusionRow,
                    'inclusions' => $inclusions,
                    'rowNumber' => $loop->iteration,
                ])
            @endforeach
        </div>

        <p id="empty-package-inclusions" class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 {{ $packageInclusionRows ? 'hidden' : '' }}">
            No inclusions added. Use “Add inclusion” to add one or more entries.
        </p>

        <template id="package-inclusion-template">
            @include('admin.packages._package-inclusion-row', [
                'inclusionKey' => '__KEY__',
                'packageInclusionRow' => ['package_inclusion_id' => '', 'custom_name' => '', 'custom_scheduled_time' => '', 'custom_place_from' => '', 'custom_place_to' => '', 'custom_description' => '', 'custom_duration_hours' => ''],
                'inclusions' => $inclusions,
                'rowNumber' => '__ROW_NUMBER__',
            ])
        </template>
    @endif

    @foreach($errors->getMessages() as $field => $messages)
        @if(str_starts_with($field, 'inclusions.'))
            <p class="mt-2 text-xs text-red-600">{{ $messages[0] }}</p>
        @endif
    @endforeach
</div>
