<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\CabAssociateController;
use App\Http\Controllers\Admin\CabController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CityPlaceController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailSettingsController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\HotelRoomCustomerPriceController;
use App\Http\Controllers\Admin\PackageInclusionController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\TourPackageController;
use App\Http\Controllers\CabAssociate\CabAssociateAuthController;
use App\Http\Controllers\CabAssociate\CabAvailabilityController;
use App\Http\Controllers\CabAssociate\CabController as CabAssociateCabController;
use App\Http\Controllers\CabAssociate\CabDashboardController;
use App\Http\Controllers\Frontend\PackageFrontendController;
use App\Http\Controllers\Hotel\HotelAuthController;
use App\Http\Controllers\Hotel\HotelBookingRequestController;
use App\Http\Controllers\Hotel\HotelImageController;
use App\Http\Controllers\Hotel\HotelPolicyController;
use App\Http\Controllers\Hotel\HotelRoomController;
use App\Http\Controllers\Hotel\HotelRoomTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('packages.index');
});

Route::get('/packages', [PackageFrontendController::class, 'index'])->name('packages.index');
Route::get('/packages/{slug}', [PackageFrontendController::class, 'show'])->name('packages.show');

Route::get('/packages/{package}/book', [PackageFrontendController::class, 'book'])->name('packages.book');
Route::post('/packages/{package}/book', [PackageFrontendController::class, 'storeBook'])->name('packages.book.store');

Route::prefix('cab-associate')->name('cab-associate.')->group(function (): void {
    Route::get('login', [CabAssociateAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [CabAssociateAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware('cab-associate.auth')->group(function (): void {
        Route::get('dashboard', [CabDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [CabAssociateAuthController::class, 'logout'])->name('logout');
        Route::resource('cabs', CabAssociateCabController::class)->except(['show']);
        Route::get('cabs/{cab}/availability', [CabAvailabilityController::class, 'show'])->name('cabs.availability');
        Route::post('cabs/{cab}/availability', [CabAvailabilityController::class, 'update'])->name('cabs.availability.update');
    });
});

Route::prefix('hotel')->name('hotel.')->group(function (): void {
    Route::get('login', [HotelAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [HotelAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware('hotel.auth')->group(function (): void {
        Route::get('dashboard', [HotelAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('booking-requests', [HotelBookingRequestController::class, 'index'])->name('booking-requests.index');
        Route::patch('booking-requests/{booking}', [HotelBookingRequestController::class, 'respond'])->name('booking-requests.respond');
        Route::get('images', [HotelImageController::class, 'index'])->name('images.index');
        Route::put('images', [HotelImageController::class, 'update'])->name('images.update');
        Route::get('policies', [HotelPolicyController::class, 'edit'])->name('policies.edit');
        Route::put('policies', [HotelPolicyController::class, 'update'])->name('policies.update');
        Route::resource('room-types', HotelRoomTypeController::class)
            ->except(['show'])
            ->parameters(['room-types' => 'roomType']);
        Route::resource('rooms', HotelRoomController::class)->except(['show']);
        Route::get('rooms/{room}/calendar', [HotelRoomController::class, 'calendarData'])->name('rooms.calendar');
        Route::post('rooms/{room}/calendar', [HotelRoomController::class, 'toggleCalendarDate'])->name('rooms.calendar.toggle');
        Route::post('logout', [HotelAuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    });

    Route::middleware(['auth', 'admin', 'admin.permission'])->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('email-settings', [EmailSettingsController::class, 'edit'])->name('email-settings.edit');
        Route::put('email-settings', [EmailSettingsController::class, 'update'])->name('email-settings.update');
        Route::prefix('destinations')->name('destinations.')->group(function (): void {
            Route::get('/', function () {
                return redirect()->route('admin.countries.index');
            })->name('index');
        });
        Route::resource('countries', CountryController::class)->except(['show']);
        Route::resource('states', StateController::class)->except(['show']);
        Route::resource('cities', CityController::class)->except(['show']);
        Route::resource('places', CityPlaceController::class);
        Route::get('places/city/{city}', [CityPlaceController::class, 'cityPlaces'])->name('places.city.index');
        Route::get('countries/{country}/states', [CityPlaceController::class, 'statesByCountry'])->name('places.states-by-country');
        Route::get('states/{state}/cities', [CityPlaceController::class, 'citiesByState'])->name('places.cities-by-state');
        Route::resource('packages', TourPackageController::class);
        Route::get('hotels/{hotel}/rooms/{room}/customer-prices', [HotelRoomCustomerPriceController::class, 'redirectToHotelDetails'])
            ->name('hotels.rooms.customer-prices.show');
        Route::put('hotels/{hotel}/rooms/{room}/customer-prices', [HotelRoomCustomerPriceController::class, 'update'])
            ->name('hotels.rooms.customer-prices.update');
        Route::resource('hotels', HotelController::class);
        Route::get('hotels/{hotel}/rooms/{room}/calendar', [HotelController::class, 'roomCalendar'])->name('hotels.rooms.calendar');
        Route::get('bookings', [BookingsController::class, 'index'])->name('bookings.index');
        Route::get('bookings/processing', [BookingsController::class, 'processing'])->name('bookings.processing');
        Route::get('bookings/create', [BookingsController::class, 'create'])->name('bookings.create');
        Route::post('bookings', [BookingsController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}/process', [BookingsController::class, 'process'])->name('bookings.process');
        Route::put('bookings/{booking}/process', [BookingsController::class, 'updateProcessing'])->name('bookings.process.update');
        Route::get('bookings/{booking}', [BookingsController::class, 'show'])->name('bookings.show');
        Route::get('bookings/{booking}/edit', [BookingsController::class, 'edit'])->name('bookings.edit');
        Route::put('bookings/{booking}', [BookingsController::class, 'update'])->name('bookings.update');
        Route::patch('bookings/{booking}/confirm', [BookingsController::class, 'confirm'])->name('bookings.confirm');
        Route::get('hotel-booking-requests', [BookingsController::class, 'indexHotelRequests'])->name('hotel-booking-requests.index');
        Route::post('hotel-booking-requests/{booking}/send', [BookingsController::class, 'sendHotelRequest'])->name('hotel-booking-requests.send');
        Route::resource('inclusions', PackageInclusionController::class)->except(['show']);
        Route::resource('cabs', CabController::class)->except(['show']);
        Route::get('cabs/{cab}/availability', [CabController::class, 'availability'])->name('cabs.availability');
        Route::resource('cab-associates', CabAssociateController::class)->except(['show']);
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Role and Permission Management Routes
        Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions.index');
        Route::post('roles-permissions/create-role', [RolePermissionController::class, 'createRole'])->name('roles-permissions.create-role');
        Route::post('roles-permissions/update-role/{id}', [RolePermissionController::class, 'updateRole'])->name('roles-permissions.update-role');
        Route::post('roles-permissions/delete-role/{id}', [RolePermissionController::class, 'deleteRole'])->name('roles-permissions.delete-role');
        Route::post('roles-permissions/assign-role/{userId}', [RolePermissionController::class, 'assignRoleToUser'])->name('roles-permissions.assign-role');
        Route::get('roles-permissions/get-user-roles/{userId}', [RolePermissionController::class, 'getUserRoles'])->name('roles-permissions.get-user-roles');
    });
});
