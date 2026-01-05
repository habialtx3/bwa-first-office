<?php

use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\OfficeSpaceController;
use App\Http\Resources\Api\BookingTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/city/{city:slug}', [CityController::class, 'show']);
Route::apiResource('/cities', CityController::class);

Route::get('/office/{officeSpace:slug}', [OfficeSpaceController::class, 'show']);
Route::apiResource('/office', OfficeSpaceController::class);

// Route::post('/booking-transaction', [Booking::class, 'store']);
// Route::post('/checking-booking', [BookingTransactionResource::class],'booking_details');
