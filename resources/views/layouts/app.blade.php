<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Packages') | {{ config('app.name', 'Tour & Travel') }}</title>
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('site/style.css') }}">
   
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    @if(request()->routeIs('packages.index', 'packages.show', 'packages.book', 'custom-packages.create'))
        <style>
            :root {
                --dark-color: #10223b;
                --white-color: #fff;
                --primary-color: #315fa8;
                --primary-light: #edf3ff;
                --accent-orange: #f47b35;
                --accent-orange-light: #fff0e6;
                --accent-yellow: #f8bd4b;
                --accent-yellow-light: #fff2cc;
                --light-bg: #f7f9fc;
                --border-color: #dfe5ee;
                --text-color: #5c6675;
                --heading-color: #18263a;
                --body-font: 'Instrument Sans', sans-serif;
                --transition: all .25s ease;
                --btn-hover: #244d8b;
            }
            .travel-header .container,
            .top-bar .container { width: calc(100% - 32px); max-width: 1360px; margin: 0 auto; }
            .top-bar { padding: 10px 0; }
            .top-bar-contact,
            .top-bar-social { gap: 24px; }
            .top-bar-contact a,
            .top-bar-social a,
            .top-bar-social > span { font-size: 13px; }
            .top-bar-contact a { gap: 8px; }
            .top-bar-contact .material-symbols-outlined { font-size: 17px; }
            .travel-header .site-header { padding: 15px 0; }
            .travel-header .header-inner { gap: 24px; }
            .travel-header .site-logo { flex: 0 0 210px; }
            .travel-header .site-logo a { display: inline-flex; align-items: center; text-decoration: none; white-space: nowrap; }
            .travel-header .site-logo img { display: block; width: 100%; max-width: 210px; max-height: 62px; object-fit: contain; object-position: left center; }
            .travel-header .header-search-form { max-width: 750px; padding: 4px; gap: 4px; }
            .travel-header .search-field { gap: 8px; padding: 0 12px; }
            .travel-header .search-field .material-symbols-outlined { font-size: 20px; }
            .travel-header .search-field input,
            .travel-header .search-field select { font-size: 13px; padding: 10px 0; }
            .travel-header .search-submit { flex-basis: 48px; width: 48px; height: 48px; }
            .travel-header .search-submit .material-symbols-outlined { font-size: 22px; }
            .travel-header .header-contact { gap: 12px; }
            .travel-header .header-contact-icon { width: 48px; height: 48px; }
            .travel-header .header-contact-icon .material-symbols-outlined { font-size: 24px; }
            .travel-header .header-contact-info { gap: 4px; }
            .travel-header .header-contact-info span { font-size: 12px; }
            .travel-header .header-contact-info a { font-size: 16px; }
            .travel-header .category-menu > li > a,
            .travel-header .dropdown-toggle { font-size: 13px; }
            @media (max-width: 1199px) {
                .travel-header .header-inner { flex-wrap: wrap; }
                .travel-header .site-logo { flex: 1 1 180px; }
                .travel-header .header-search-form { order: 3; flex: 1 1 100%; max-width: 100%; }
                .travel-header .header-contact-info { display: none; }
            }
            @media (max-width: 991px) {
                .travel-header .header-inner { gap: 12px; }
                .travel-header .site-logo { flex: 1 1 150px; }
                .travel-header .nav-hamburger { flex: 0 0 42px; width: 42px; height: 40px; padding: 8px; opacity: 1; visibility: visible; pointer-events: auto; }
            }
            @media (max-width: 575px) {
                .top-bar-contact { gap: 6px 14px; }
                .top-bar-contact a { font-size: 12px; }
                .travel-header .site-header { padding: 10px 0; }
                .travel-header .site-logo { flex-basis: 120px; }
                .travel-header .header-contact-icon { width: 40px; height: 40px; }
                .travel-header .header-inner { gap: 8px; }
                .travel-header .search-field { flex: 1 1 38%; min-width: 0; padding: 0 6px; }
                .travel-header .search-name { flex: 1 1 100%; }
                .travel-header .search-submit { flex: 0 0 44px; width: 44px; height: 44px; }
            }
        </style>
    @endif
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        @media (max-width: 640px) {
            main input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
            main select,
            main textarea { font-size: 16px; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-white text-slate-900 min-h-screen flex flex-col">

    @if(request()->routeIs('packages.index', 'packages.show', 'packages.book', 'custom-packages.create'))
        @include('layouts.partials.travel-header')
    @else
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

                <div class="hidden xl:flex flex-wrap justify-end items-center gap-x-5 gap-y-2 text-sm font-medium">
                    <a href="{{ route('packages.index') }}" class="text-slate-600 hover:text-indigo-600 transition">Holiday Packages</a>
                    <a href="{{ route('custom-packages.create') }}" class="text-slate-600 hover:text-indigo-600 transition">Build a Package</a>
                    @if(session()->has('custom_package_owner_token'))
                        <a href="{{ route('custom-packages.index') }}" class="text-slate-600 hover:text-indigo-600 transition">My Custom Packages</a>
                    @endif
                    <a href="#destinations" class="text-slate-600 hover:text-indigo-600 transition">Destinations</a>
                    <a href="#about" class="text-slate-600 hover:text-indigo-600 transition">About Us</a>
                    <a href="#contact" class="text-slate-600 hover:text-indigo-600 transition">Contact</a>
                    <a href="{{ route('hotel.login') }}" class="text-slate-600 hover:text-indigo-600 transition">Hotel Partner Login</a>
                    <a href="{{ route('admin.login') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Admin Login</a>
                </div>

                <!-- Mobile menu toggle -->
                <div class="xl:hidden flex items-center gap-3">
                    <button id="publicMobileNavToggle" type="button" aria-expanded="false" aria-controls="publicMobileNav" class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100" aria-label="Open navigation menu">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <nav id="publicMobileNav" class="hidden border-b border-slate-200 bg-white px-4 py-3 shadow-md xl:hidden" aria-label="Mobile navigation">
        <div class="mx-auto flex max-w-7xl flex-col gap-1 text-sm font-medium">
            <a href="{{ route('packages.index') }}" class="rounded-lg px-3 py-2.5 text-slate-700 hover:bg-slate-50">Holiday Packages</a>
            <a href="{{ route('custom-packages.create') }}" class="rounded-lg px-3 py-2.5 text-slate-700 hover:bg-slate-50">Build a Package</a>
            @if(session()->has('custom_package_owner_token'))
                <a href="{{ route('custom-packages.index') }}" class="rounded-lg px-3 py-2.5 text-slate-700 hover:bg-slate-50">My Custom Packages</a>
            @endif
            <a href="{{ route('hotel.login') }}" class="rounded-lg px-3 py-2.5 text-slate-700 hover:bg-slate-50">Hotel Partner Login</a>
            <a href="{{ route('admin.login') }}" class="rounded-lg px-3 py-2.5 font-semibold text-indigo-700 hover:bg-indigo-50">Admin Login</a>
        </div>
    </nav>
    @endif

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
    <main class="min-w-0 w-full flex-1">
        @yield('content')
    </main>

    <!-- User website footer -->
    <section class="footer-newsletter mt-50">
        <div class="container newsletter-container">
            <div class="newsletter-content">
                <h2>Subscribe <span>Now!</span></h2>
                <p>Sign up to our weekly newsletter to get the latest updates.</p>
            </div>
            <div class="newsletter-form" role="group" aria-label="Newsletter signup">
                <input type="email" placeholder="Email address..." aria-label="Email address">
                <button type="button" aria-label="Subscribe"><span class="material-symbols-outlined" aria-hidden="true">search</span></button>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="footer-overlay"></div>
        <div class="container footer-container">
            <div class="footer-about">
                <a href="{{ route('packages.index') }}" class="footer-logo" aria-label="{{ config('app.name', 'Tour & Travel') }} home">
                    <img src="{{ asset('assets/logo-white.png') }}" alt="{{ config('app.name', 'Tour & Travel') }}">
                </a>
                <p>Explore beautiful destinations with carefully curated travel packages and personalized journeys.</p>
                <div class="footer-socials">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h3>Explore</h3>
                <ul>
                    <li><a href="{{ route('packages.index') }}">Holiday packages</a></li>
                    <li><a href="{{ route('custom-packages.create') }}">Build a custom package</a></li>
                    <li><a href="{{ route('custom-packages.index') }}">My custom packages</a></li>
                    <li><a href="{{ route('hotel.login') }}">Hotel partner login</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Destinations</h3>
                <ul>
                    <li><a href="{{ route('packages.index', ['search' => 'Kashmir']) }}">Kashmir</a></li>
                    <li><a href="{{ route('packages.index', ['search' => 'Himachal']) }}">Himachal Pradesh</a></li>
                    <li><a href="{{ route('packages.index', ['search' => 'Manali']) }}">Manali</a></li>
                    <li><a href="{{ route('packages.index', ['search' => 'Shimla']) }}">Shimla</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Helpful links</h3>
                <ul>
                    <li><a href="{{ route('packages.index') }}">About us</a></li>
                    <li><a href="{{ route('packages.index') }}">FAQs</a></li>
                    <li><a href="{{ route('packages.index') }}">Contact</a></li>
                    <li><a href="{{ route('admin.login') }}">Admin login</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <div class="footer-contact-item">
                    <span class="footer-contact-icon material-symbols-outlined" aria-hidden="true">call</span>
                    <div><a href="tel:+918685818865" class="footer-phone">+91-86858-18865</a></div>
                </div>
                <div class="footer-contact-item">
                    <span class="footer-contact-icon material-symbols-outlined" aria-hidden="true">mail</span>
                    <div>
                        <a href="mailto:info@travel.com">info@travel.com</a>
                        <a href="mailto:support@travel.com">support@travel.com</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p>&copy; {{ now()->year }} <a href="{{ route('packages.index') }}">{{ config('app.name', 'Tour & Travel') }}</a>. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
    <script>
        (() => {
            const toggle = document.getElementById('publicMobileNavToggle');
            const menu = document.getElementById('publicMobileNav');
            if (!toggle || !menu) return;
            toggle.addEventListener('click', () => {
                const isOpen = toggle.getAttribute('aria-expanded') !== 'true';
                toggle.setAttribute('aria-expanded', String(isOpen));
                menu.classList.toggle('hidden', !isOpen);
                toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
            });
        })();
    </script>
    @if(request()->routeIs('packages.index', 'packages.show', 'packages.book'))
        <script>
            (() => {
                const header = document.getElementById('travelHeader');
                const hamburger = document.getElementById('navHamburger');
                if (!header || !hamburger) return;
                const syncScrollState = () => header.classList.toggle('is-scrolled', window.scrollY > 40);
                syncScrollState();
                window.addEventListener('scroll', syncScrollState, { passive: true });
                hamburger.addEventListener('click', () => {
                    const isOpen = header.classList.toggle('menu-open');
                    hamburger.setAttribute('aria-expanded', String(isOpen));
                });
                header.querySelectorAll('.dropdown-toggle').forEach((button) => {
                    button.addEventListener('click', () => {
                        const item = button.closest('.has-dropdown');
                        const isOpen = item.classList.toggle('dropdown-open');
                        button.setAttribute('aria-expanded', String(isOpen));
                    });
                });
            })();
        </script>
    @endif
</body>
</html>
