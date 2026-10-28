@extends('layouts.hotel')
@section('title', 'Hotel Partner Login')
@section('content')
<div class="mx-auto grid max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl md:grid-cols-2">
    <div class="hidden bg-gradient-to-br from-indigo-700 via-blue-700 to-slate-900 p-10 text-white md:flex md:flex-col md:justify-between">
        <div>
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-2xl"><i class="fa-solid fa-hotel"></i></span>
            <h1 class="mt-8 text-3xl font-bold">Welcome, hotel partners</h1>
            <p class="mt-3 leading-7 text-blue-100">Sign in to view your hotel profile, location, star rating, and current rates.</p>
        </div>
        <p class="text-sm text-blue-100">Use the hotel code or username provided by your administrator.</p>
    </div>
    <div class="p-6 sm:p-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Hotel Partner Portal</p>
            <h2 class="mt-2 text-2xl font-bold">Sign in to your account</h2>
            <p class="mt-2 text-sm text-slate-500">Enter your hotel ID or username and password.</p>
        </div>
        <form method="POST" action="{{ route('hotel.login.post') }}" class="space-y-5">
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
                <label for="login_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Hotel ID or username</label>
                <input id="login_id" name="login_id" value="{{ old('login_id') }}" required autofocus autocomplete="username" maxlength="255" placeholder="HTL-000001 or hotel.username" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
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
        <p class="mt-4 text-center text-sm"><a href="{{ route('packages.index') }}" class="font-medium text-indigo-600 hover:text-indigo-700">Back to Tour &amp; Travel</a></p>
    </div>
</div>
@endsection
