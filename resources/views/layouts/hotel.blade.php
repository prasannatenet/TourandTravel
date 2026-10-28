<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hotel Dashboard') | {{ config('app.name', 'Tour & Travel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('hotel.dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg text-white"><i class="fa-solid fa-hotel"></i></span>
                <span>
                    <span class="block font-bold">Hotel Partner Portal</span>
                    <span class="block text-xs text-slate-500">{{ config('app.name', 'Tour & Travel') }}</span>
                </span>
            </a>
            @if (session()->has('hotel_id'))
                <form method="POST" action="{{ route('hotel.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i>Sign out
                    </button>
                </form>
            @endif
        </div>
        @if (session()->has('hotel_id'))
            <nav class="border-t border-slate-100">
                <div class="mx-auto flex max-w-7xl flex-wrap gap-2 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ route('hotel.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('hotel.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">Dashboard</a>
                    <a href="{{ route('hotel.images.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('hotel.images.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">Hotel Photos</a>
                    <a href="{{ route('hotel.room-types.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('hotel.room-types.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">Room Types</a>
                    <a href="{{ route('hotel.rooms.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('hotel.rooms.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">Rooms</a>
                    <a href="{{ route('hotel.policies.edit') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('hotel.policies.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">Policies</a>
                </div>
            </nav>
        @endif
    </header>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
    <footer class="mx-auto max-w-7xl px-4 py-6 text-center text-xs text-slate-400 sm:px-6 lg:px-8">
        &copy; {{ now()->year }} {{ config('app.name', 'Tour & Travel') }} Hotel Partner Portal
    </footer>
</body>
</html>
