<div class="top-bar">
    <div class="container">
        <div class="top-bar-inner">
            <div class="top-bar-contact">
                <a href="tel:+918685818865"><span class="material-symbols-outlined" aria-hidden="true">call</span>+91-8685818865</a>
                <a href="mailto:info@travel.com"><span class="material-symbols-outlined" aria-hidden="true">mail</span>info@travel.com</a>
            </div>
            <div class="top-bar-social">
                <span>Follow Us:</span>
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="travel-header" id="travelHeader">
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="site-logo">
                    <a href="{{ route('packages.index') }}" aria-label="{{ config('app.name', 'Tour & Travel') }} home">
                        <img src="{{ asset('assets/logo1.png') }}" alt="{{ config('app.name', 'Tour & Travel') }}">
                    </a>
                </div>

                <button class="nav-hamburger" id="navHamburger" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="categoryNav">
                    <span></span><span></span><span></span>
                </button>

                <form class="header-search-form" method="GET" action="{{ route('packages.index') }}">
                    <label class="search-field search-name">
                        <span class="material-symbols-outlined" aria-hidden="true">person_search</span>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name" aria-label="Search packages by name">
                    </label>
                    <label class="search-field">
                        <span class="material-symbols-outlined" aria-hidden="true">location_on</span>
                        <select name="destination" aria-label="Destination">
                            <option value="">Destination</option>
                            @if(isset($destinations))
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected((string) request('destination') === (string) $destination->id)>{{ $destination->name }}{{ isset($destination->city) ? ', '.$destination->city->name : '' }}</option>
                                @endforeach
                            @elseif(isset($package))
                                @foreach($package->destinations as $packageDestination)
                                    @if($packageDestination->destination)
                                        <option value="{{ $packageDestination->destination->id }}">{{ $packageDestination->destination->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </label>
                    <label class="search-field">
                        <span class="material-symbols-outlined" aria-hidden="true">schedule</span>
                        <select name="duration" aria-label="Trip duration">
                            <option value="">Duration</option>
                            @foreach([1, 2, 3, 4, 5, 6, 7, 10, 14] as $duration)
                                <option value="{{ $duration }}" @selected((string) request('duration') === (string) $duration)>{{ $duration }} days</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="search-submit" aria-label="Search packages"><span class="material-symbols-outlined" aria-hidden="true">send</span></button>
                </form>

                <div class="header-contact">
                    <div class="header-contact-icon"><span class="material-symbols-outlined" aria-hidden="true">call</span></div>
                    <div class="header-contact-info"><span>Call Us Anytime</span><a href="tel:+918685818865">+91-86858-18865</a></div>
                </div>
            </div>
        </div>
    </header>

    <nav class="category-nav" id="categoryNav" aria-label="Main navigation">
        <ul class="category-menu">
            <li><a href="{{ route('packages.index') }}">HOME</a></li>
            <li><a href="{{ route('packages.index') }}">ABOUT</a></li>
            <li class="has-dropdown">
                <button class="dropdown-toggle" type="button" aria-expanded="false">SERVICES <span class="material-symbols-outlined" aria-hidden="true">keyboard_arrow_down</span></button>
                <ul class="category-dropdown">
                    <li><a href="{{ route('packages.index') }}">Holiday Packages</a></li>
                    <li><a href="{{ route('custom-packages.create') }}">Build a Custom Package</a></li>
                </ul>
            </li>
            <li class="has-dropdown">
                <button class="dropdown-toggle" type="button" aria-expanded="false">TOURS <span class="material-symbols-outlined" aria-hidden="true">keyboard_arrow_down</span></button>
                <ul class="category-dropdown">
                    <li><a href="{{ route('packages.index') }}">All Tours</a></li>
                    <li><a href="{{ route('packages.index', ['search' => 'group']) }}">Group Tours</a></li>
                    <li><a href="{{ route('custom-packages.create') }}">Customized Tours</a></li>
                </ul>
            </li>
            <li><a href="{{ route('packages.index') }}">DESTINATIONS</a></li>
            <li><a href="mailto:info@travel.com">CONTACT</a></li>
        </ul>
    </nav>
</div>
