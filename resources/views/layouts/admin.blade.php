<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | {{ config('app.name', 'Tour & Travel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .sidebar-link.active { background: #1d4ed8; color: #fff; }
        .sidebar-sub.active { background: #1d4ed8; color: #fff; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 min-h-screen">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-200 hidden md:flex flex-col fixed inset-y-0">
        <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-plane-departure"></i>
            </div>
            <div>
                <p class="font-bold text-white leading-tight">Tour & Travel</p>
                <p class="text-xs text-slate-400">Admin Panel</p>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800">
                <i class="fa-solid fa-gauge-high w-5 text-center"></i> Dashboard
            </a>
            @php($destinationOpen = request()->routeIs('admin.countries.*', 'admin.states.*', 'admin.cities.*', 'admin.places.*'))
            <div class="rounded-lg">
                <button type="button" id="destinationToggle" aria-expanded="{{ $destinationOpen ? 'true' : 'false' }}" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-semibold uppercase tracking-widest text-slate-400 hover:text-slate-200 hover:bg-slate-800/60">
                    <i class="fa-solid fa-location-dot w-5 text-center text-sm"></i>
                    <span>Destination</span>
                    <i id="destinationChevron" class="fa-solid fa-chevron-down ml-auto text-[10px] transition-transform {{ $destinationOpen ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="destinationMenu" class="{{ $destinationOpen ? '' : 'hidden' }} ml-8 mt-1 mb-1 space-y-1 text-[13px]">
                    <a href="{{ route('admin.countries.index') }}" class="sidebar-sub {{ request()->routeIs('admin.countries.*') ? 'active' : '' }} block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white">1. Country</a>
                    <a href="{{ route('admin.states.index') }}" class="sidebar-sub {{ request()->routeIs('admin.states.*') ? 'active' : '' }} block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white">2. State</a>
                    <a href="{{ route('admin.cities.index') }}" class="sidebar-sub {{ request()->routeIs('admin.cities.*') ? 'active' : '' }} block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white">3. City</a>
                    <a href="{{ route('admin.places.index') }}" class="sidebar-sub {{ request()->routeIs('admin.places.*') ? 'active' : '' }} block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white">4. Places</a>
                </div>
            </div>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 opacity-70">
                <i class="fa-solid fa-map-location-dot w-5 text-center"></i> Tours
                <span class="ml-auto text-[10px] bg-slate-700 px-2 py-0.5 rounded-full">Soon</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 opacity-70">
                <i class="fa-solid fa-ticket w-5 text-center"></i> Bookings
                <span class="ml-auto text-[10px] bg-slate-700 px-2 py-0.5 rounded-full">Soon</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 opacity-70">
                <i class="fa-solid fa-users w-5 text-center"></i> Customers
                <span class="ml-auto text-[10px] bg-slate-700 px-2 py-0.5 rounded-full">Soon</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 opacity-70">
                <i class="fa-solid fa-gear w-5 text-center"></i> Settings
                <span class="ml-auto text-[10px] bg-slate-700 px-2 py-0.5 rounded-full">Soon</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button title="Logout" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-red-600 hover:text-white flex items-center justify-center">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 md:ml-64 flex flex-col min-h-screen">
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 md:px-8 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900">@yield('header', 'Dashboard')</h1>
                    <p class="text-sm text-slate-500">@yield('subheader', 'Welcome back, manage your travel business.')</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-full font-semibold"><i class="fa-solid fa-circle text-[8px] mr-1.5 self-center"></i> Online</span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="md:hidden">
                        @csrf
                        <button class="px-3 py-2 bg-slate-900 text-white rounded-lg text-sm">Logout</button>
                    </form>
                </div>
            </div>
            <!-- Mobile nav -->
            <nav class="md:hidden px-4 pb-3 flex gap-2 overflow-x-auto text-sm">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg whitespace-nowrap">Dashboard</a>
                <a href="{{ route('admin.countries.index') }}" class="px-3 py-1.5 bg-slate-100 rounded-lg whitespace-nowrap">Country</a>
                <a href="{{ route('admin.states.index') }}" class="px-3 py-1.5 bg-slate-100 rounded-lg whitespace-nowrap">State</a>
                <a href="{{ route('admin.cities.index') }}" class="px-3 py-1.5 bg-slate-100 rounded-lg whitespace-nowrap">City</a>
                <a href="{{ route('admin.places.index') }}" class="px-3 py-1.5 bg-slate-100 rounded-lg whitespace-nowrap">Places</a>
            </nav>
        </header>
        <main class="p-4 md:p-8 flex-1">
            @yield('content')
        </main>
        <footer class="px-8 py-4 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'Tour & Travel') }} Admin Panel. All rights reserved.
        </footer>
    </div>
</div>
@stack('scripts')
<script>
(function () {
    var toggle = document.getElementById('destinationToggle');
    var menu = document.getElementById('destinationMenu');
    var chevron = document.getElementById('destinationChevron');
    if (!toggle || !menu) {
        return;
    }
    try {
        var saved = localStorage.getItem('destinationMenuOpen');
        if (saved === '1') {
            menu.classList.remove('hidden');
            if (chevron) {
                chevron.classList.add('rotate-180');
            }
            toggle.setAttribute('aria-expanded', 'true');
        } else if (saved === '0' && toggle.getAttribute('aria-expanded') !== 'true') {
            menu.classList.add('hidden');
            if (chevron) {
                chevron.classList.remove('rotate-180');
            }
            toggle.setAttribute('aria-expanded', 'false');
        }
    } catch (e) {}
    toggle.addEventListener('click', function () {
        var isHidden = menu.classList.toggle('hidden');
        if (chevron) {
            chevron.classList.toggle('rotate-180', !isHidden);
        }
        toggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
        try {
            localStorage.setItem('destinationMenuOpen', isHidden ? '0' : '1');
        } catch (e) {}
    });
})();
</script>
</body>
</html>
