<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CourtController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\BookingController;

Route::prefix('v1')->group(function () {
    // Court routes
    Route::apiResource('courts', CourtController::class);
    
    // Schedule routes
    Route::apiResource('schedules', ScheduleController::class);
    Route::get('schedules-available', [ScheduleController::class, 'getAvailable']);
    
    // Booking routes
    Route::apiResource('bookings', BookingController::class);
});