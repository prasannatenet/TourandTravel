<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CityPlaceController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
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
        Route::get('countries/{country}/states', [CityPlaceController::class, 'statesByCountry'])->name('places.states-by-country');
        Route::get('states/{state}/cities', [CityPlaceController::class, 'citiesByState'])->name('places.cities-by-state');
        Route::resource('packages', TourPackageController::class)->except(['show']);
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});
