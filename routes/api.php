<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CourtController;
use App\Http\Controllers\API\BookingController;

Route::prefix('v1')->group(function () {
    // Court routes
    Route::apiResource('courts', CourtController::class);
    
    // Booking routes
    Route::apiResource('bookings', BookingController::class);
    Route::post('bookings/check-availability', [BookingController::class, 'checkAvailability']);
});
