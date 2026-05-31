<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/up', fn () => response()->json(['status' => 'ok']));

Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);

Route::middleware(['auth:sanctum', 'track-active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy']);
    Route::get('/me', [MeController::class, 'show']);
    Route::patch('/me', [MeController::class, 'update']);
    Route::delete('/me', [MeController::class, 'destroy']);
});
