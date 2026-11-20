@extends('layouts.admin')

@section('title', 'Company Profile')
@section('header', 'Company Profile')
@section('subheader', 'Set the company name and logo shown throughout the admin dashboard.')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
            <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.company-profile.update') }}" enctype="multipart/form-data" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @csrf
        @method('PUT')
        <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid fa-building"></i></span>
                <div><h2 class="font-bold text-slate-900">Dashboard branding</h2><p class="mt-1 text-sm text-slate-500">This name and logo appear in the admin sidebar and mobile header.</p></div>
            </div>
        </div>

        <div class="grid gap-6 p-5 sm:grid-cols-[minmax(0,1fr)_220px] sm:p-7">
            <div class="space-y-5">
                <div>
                    <label for="company_name" class="mb-1.5 block text-sm font-semibold text-slate-800">Company name <span class="text-rose-600">*</span></label>
                    <input id="company_name" name="company_name" type="text" required maxlength="150" value="{{ old('company_name', $companyProfile->company_name) }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="logo" class="mb-1.5 block text-sm font-semibold text-slate-800">Company logo</label>
                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700">
                    <p class="mt-1.5 text-xs text-slate-500">PNG, JPG, or WebP. Maximum file size: 2 MB.</p>
                </div>
                @if($companyProfile->logo_path)
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Remove the current logo
                    </label>
                @endif
            </div>

            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center">
                <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">Current logo</p>
                @if($companyProfile->logo_path)
                    <img src="{{ asset('storage/'.$companyProfile->logo_path) }}" alt="{{ $companyProfile->company_name }} logo" class="max-h-32 max-w-full object-contain">
                @else
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-3xl text-white"><i class="fa-solid fa-plane-departure"></i></span>
                    <span class="mt-3 max-w-full truncate text-sm font-bold text-slate-800">{{ $companyProfile->company_name }}</span>
                @endif
            </div>
        </div>

        <div class="flex justify-end border-t border-slate-100 px-5 py-4 sm:px-7">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-floppy-disk"></i> Save company profile</button>
        </div>
    </form>
</div>
@endsection
