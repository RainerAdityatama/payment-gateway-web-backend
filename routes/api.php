<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\LapanganPublicController;
use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('public')->middleware('throttle:60,1')->group(function () {
    Route::get('/lapangan', [LapanganPublicController::class, 'getLapanganPublic']);
    Route::get('/lapangan/{lapangan:slug}/availability', [LapanganPublicController::class, 'detailLapanganSlot']);
    Route::get('/lapangan/{lapangan:slug}/kalender', [LapanganPublicController::class, 'getKalenderBulanan']);
    Route::post('/checkout', [CheckoutController::class, 'process']);
    Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handleWebhook']);
});

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {

    Route::middleware('throttle:10,1')->group(function () {
        // lapangan route
        Route::get('/lapangan', [LapanganController::class, 'getLapanganAdmin']);
        Route::post('/lapangan', [LapanganController::class, 'tambahLapangan']);
        Route::put('/lapangan/{lapangan}', [LapanganController::class, 'editLapangan']);
        Route::delete('/lapangan/{lapangan}', [LapanganController::class, 'hapusLapangan']);
    });
});
