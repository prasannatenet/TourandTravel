<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Packages') | {{ config('app.name', 'Tour & Travel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-white text-slate-900 min-h-screen flex flex-col">

    <!-- Navigation -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-lg">
                        <i class="fa-solid fa-plane-departure"></i>
                    </div>
                    <a href="{{ route('packages.index') }}" class="font-bold text-xl text-slate-900 hover:text-indigo-600 transition">Tour<span class="text-indigo-600">Travel</span></a>
                </div>

                <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                    <a href="{{ route('packages.index') }}" class="text-slate-600 hover:text-indigo-600 transition">Holiday Packages</a>
                    <a href="#destinations" class="text-slate-600 hover:text-indigo-600 transition">Destinations</a>
                    <a href="#about" class="text-slate-600 hover:text-indigo-600 transition">About Us</a>
                    <a href="#contact" class="text-slate-600 hover:text-indigo-600 transition">Contact</a>
                    <a href="{{ route('admin.login') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Admin Login</a>
                </div>

                <!-- Mobile menu toggle -->
                <div class="md:hidden flex items-center">
                    <button class="text-slate-600 hover:text-indigo-600">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="text-white font-semibold mb-3">Tour & Travel</h4>
                    <p class="text-sm text-slate-400">Explore the world with our carefully curated holiday packages.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('packages.index') }}" class="text-slate-400 hover:text-white transition">Holiday Packages</a></li>
                        <li><a href="#destinations" class="text-slate-400 hover:text-white transition">Destinations</a></li>
                        <li><a href="#about" class="text-slate-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#contact" class="text-slate-400 hover:text-white transition">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Categories</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-slate-400 hover:text-white transition">Family Packages</a></li>
                        <li><a href="#" class="text-slate-400 hover:text-white transition">Honeymoon Packages</a></li>
                        <li><a href="#" class="text-slate-400 hover:text-white transition">Adventure Tours</a></li>
                        <li><a href="#" class="text-slate-400 hover:text-white transition">Pilgrimage Tours</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Contact</h4>
                    <p class="text-sm text-slate-400 mb-1"><i class="fa-solid fa-phone mr-2 text-indigo-500"></i> +91 98765 43210</p>
                    <p class="text-sm text-slate-400 mb-1"><i class="fa-solid fa-envelope mr-2 text-indigo-500"></i> info@tourtravel.com</p>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-6 text-center text-sm text-slate-500">
                &copy; {{ now()->year }} Tour & Travel. All rights reserved.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>