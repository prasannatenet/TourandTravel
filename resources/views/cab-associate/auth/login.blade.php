@extends('layouts.cab-associate')
@section('title', 'Associate Sign In')
@section('content')
<div class="mx-auto max-w-lg overflow-hidden rounded-3xl bg-white p-6 shadow-xl sm:p-10">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Cab Associate Portal</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Sign in to your account</h1>
        <p class="mt-2 text-sm text-slate-500">Use the username and password provided by your administrator.</p>
    </div>
    <form method="POST" action="{{ route('cab-associate.login.post') }}" class="space-y-5">
        @csrf
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div>
            <label for="username" class="mb-1.5 block text-sm font-semibold text-slate-700">Username</label>
            <input id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" maxlength="50" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        </div>
        <div>
            <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        </div>
        <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700">
            <i class="fa-solid fa-right-to-bracket mr-2"></i>Sign in
        </button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Need access? Contact your Tour &amp; Travel administrator.</p>
</div>
@endsection
