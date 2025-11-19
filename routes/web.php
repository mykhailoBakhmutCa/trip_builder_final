<?php

use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TripController::class, 'index']);
Route::get('/trips/search', [TripController::class, 'search'])->name('trip.search');
