<?php

use App\Http\Controllers\ChangeEmailController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MeAvatarController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\MeEmailController;
use App\Http\Controllers\MePasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/up', fn () => response()->json(['status' => 'ok']));

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'store']);
    Route::post('/login', [LoginController::class, 'store']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store']);
    Route::post('/reset-password', [ResetPasswordController::class, 'store']);
    Route::post('/change-email', [ChangeEmailController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'track-active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy']);
    Route::post('/token/refresh', [LoginController::class, 'update']);
    Route::post('/verify', [VerificationController::class, 'verify']);
    Route::post('/verify/resend', [VerificationController::class, 'resend']);

    Route::middleware('verified')->group(function () {
        Route::get('/me', [MeController::class, 'show']);
        Route::patch('/me', [MeController::class, 'update']);
        Route::delete('/me', [MeController::class, 'destroy']);
        Route::post('/me/email', [MeEmailController::class, 'store']);
        Route::patch('/me/password', [MePasswordController::class, 'update']);
        Route::post('/me/avatar', [MeAvatarController::class, 'store']);
        Route::delete('/me/avatar', [MeAvatarController::class, 'destroy']);
    });
});
