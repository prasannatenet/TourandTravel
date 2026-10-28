<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CityPlaceController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\HotelRoomCustomerPriceController;
use App\Http\Controllers\Admin\PackageInclusionController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\TourPackageController;
use App\Http\Controllers\Frontend\PackageFrontendController;
use App\Http\Controllers\Hotel\HotelAuthController;
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

Route::prefix('hotel')->name('hotel.')->group(function (): void {
    Route::get('login', [HotelAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [HotelAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware('hotel.auth')->group(function (): void {
        Route::get('dashboard', [HotelAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('images', [HotelImageController::class, 'index'])->name('images.index');
        Route::put('images', [HotelImageController::class, 'update'])->name('images.update');
        Route::get('policies', [HotelPolicyController::class, 'edit'])->name('policies.edit');
        Route::put('policies', [HotelPolicyController::class, 'update'])->name('policies.update');
        Route::resource('room-types', HotelRoomTypeController::class)
            ->except(['show'])
            ->parameters(['room-types' => 'roomType']);
        Route::resource('rooms', HotelRoomController::class)->except(['show']);
        Route::post('logout', [HotelAuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
        Route::resource('inclusions', PackageInclusionController::class)->except(['show']);
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});
