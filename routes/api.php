<?php

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PlaceController;
use Illuminate\Support\Facades\Route;

Route::get('packages', [PackageController::class, 'index']);
Route::get('packages/{package:slug}', [PackageController::class, 'show']);
Route::get('places', [PlaceController::class, 'index']);
Route::get('places/{place:slug}', [PlaceController::class, 'show']);
Route::get('countries', [LocationController::class, 'countries'])->name('api.countries.index');
Route::get('countries/{country}', [LocationController::class, 'country'])->name('api.countries.show');
Route::get('states', [LocationController::class, 'states'])->name('api.states.index');
Route::get('states/{state}', [LocationController::class, 'state'])->name('api.states.show');
Route::get('cities', [LocationController::class, 'cities'])->name('api.cities.index');
Route::get('cities/{city:slug}', [LocationController::class, 'city'])->name('api.cities.show');
