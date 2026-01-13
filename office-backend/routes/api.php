<?php

use App\Http\Controllers\Api\BookingTransactionController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\OfficeSpaceController;
use App\Http\Resources\Api\BookingTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('api_key')->group(function () {
    Route::get('/city/{city:slug}', [CityController::class, 'show']);
    Route::apiResource('/cities', CityController::class);

    Route::get('/offices/{officeSpace:slug}', [OfficeSpaceController::class, 'show']);
    Route::apiResource('/offices', OfficeSpaceController::class);

    Route::post('/booking-transation', [BookingTransactionController::class, 'store']);

    Route::get('/check-transation', [BookingTransactionController::class, 'booking_details']);
});


// Route::post('/booking-transaction', [Booking::class, 'store']);
// Route::post('/checking-booking', [BookingTransactionResource::class],'booking_details');
