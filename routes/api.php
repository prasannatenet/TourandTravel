<?php

use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PlaceController;
use Illuminate\Support\Facades\Route;

Route::get('packages', [PackageController::class, 'index']);
Route::get('packages/{package:slug}', [PackageController::class, 'show']);
Route::get('places', [PlaceController::class, 'index']);
Route::get('places/{place:slug}', [PlaceController::class, 'show']);
