@extends('layouts.admin')
@section('title', 'Package Inclusions')
@section('header', 'Package Inclusions')
@section('subheader', 'Create reusable meals, transport, and other package services.')

@section('content')
<div class="overflow-hidden bg-white shadow rounded-2xl">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Inclusion Catalog</h2>
            <p class="text-xs text-slate-500 mt-0.5">Create entries once, then choose any combination for each package.</p>
        </div>
        <a href="{{ route('admin.inclusions.create') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 whitespace-nowrap">
            <i class="fas fa-plus mr-1.5"></i> New Inclusion
        </a>
    </div>

    <div class="p-4 sm:p-6">
        @if(session('success'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-3">
            @forelse($inclusions as $inclusion)
                <div class="border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-800">{{ $inclusion->name }}</h3>
                        @if($inclusion->image_one || $inclusion->image_two)
                            <div class="flex gap-2 mt-2">
                                @foreach([$inclusion->image_one, $inclusion->image_two] as $imagePath)
                                    @if($imagePath)
                                        <img src="{{ asset('storage/'.$imagePath) }}" alt="{{ $inclusion->name }}" class="h-14 w-20 rounded-lg border border-slate-200 object-cover">
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        @if($inclusion->description)
                            <p class="text-sm text-slate-600 mt-2 whitespace-pre-line">{{ $inclusion->description }}</p>
                        @endif
                        <p class="text-xs text-slate-400 mt-2">Used by {{ $inclusion->packages_count }} {{ \Illuminate\Support\Str::plural('package', $inclusion->packages_count) }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.inclusions.edit', $inclusion) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-600 hover:bg-indigo-50">Edit</a>
                        <form method="POST" action="{{ route('admin.inclusions.destroy', $inclusion) }}" onsubmit="return confirm('Delete this inclusion from the catalog and all packages using it?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-400">
                    <p class="text-sm">No package inclusions created yet.</p>
                    <a href="{{ route('admin.inclusions.create') }}" class="inline-block mt-3 text-sm font-medium text-indigo-600">Create the first inclusion</a>
                </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $inclusions->links() }}</div>
    </div>
</div>
@endsection
