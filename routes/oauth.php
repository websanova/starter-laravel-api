<?php

use Illuminate\Support\Facades\Route;

Route::get('/oauth/consent', [App\Http\Controllers\OAuth\ConsentController::class, 'show']);

Route::middleware(['auth:sanctum', 'track-active', 'verified', 'password-updated'])->group(function () {
    Route::post('/oauth/consent', [App\Http\Controllers\OAuth\ConsentController::class, 'store']);
});
