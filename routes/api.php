<?php

use App\Http\Controllers\Account\AvatarController;
use App\Http\Controllers\Account\BookmarkController;
use App\Http\Controllers\Account\CategoryController;
use App\Http\Controllers\Account\EmailController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\VerificationController;
use App\Http\Controllers\Admin\UserAvatarController as AdminUserAvatarController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserForceDeleteController as AdminUserForceDeleteController;
use App\Http\Controllers\Admin\UserPasswordResetController as AdminUserPasswordResetController;
use App\Http\Controllers\Admin\UserRestoreController as AdminUserRestoreController;
use App\Http\Controllers\Admin\UserRoleController as AdminUserRoleController;
use App\Http\Controllers\Auth\ChangeEmailController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/up', fn () => response()->json(['status' => 'ok']));

Route::prefix('auth')->group(function () {
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
    });
});

Route::prefix('account')->middleware(['auth:sanctum', 'track-active'])->group(function () {
    Route::middleware('verified')->group(function () {
        Route::post('/verify', [VerificationController::class, 'verify']);
        Route::post('/verify/resend', [VerificationController::class, 'resend']);
        Route::patch('/password', [PasswordController::class, 'update']);
    });

    Route::middleware(['verified', 'password-updated'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::delete('/profile', [ProfileController::class, 'destroy']);
        Route::post('/email', [EmailController::class, 'store']);
        Route::post('/avatar', [AvatarController::class, 'store']);
        Route::delete('/avatar', [AvatarController::class, 'destroy']);

        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::get('/bookmarks', [BookmarkController::class, 'index']);
        Route::post('/bookmarks', [BookmarkController::class, 'store']);
        Route::put('/bookmarks/{bookmark}', [BookmarkController::class, 'update']);
        Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);
    });
});

Route::prefix('admin')->middleware(['auth:sanctum', 'track-active', 'verified', 'password-updated', 'admin'])->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::patch('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    Route::delete('/users/{user}/avatar', [AdminUserAvatarController::class, 'destroy']);
    Route::delete('/users/{user}/force', [AdminUserForceDeleteController::class, 'destroy'])->withTrashed();
    Route::patch('/users/{user}/restore', [AdminUserRestoreController::class, 'update'])->withTrashed();
    Route::patch('/users/{user}/role', [AdminUserRoleController::class, 'update']);
    Route::post('/users/{user}/password-reset', [AdminUserPasswordResetController::class, 'store']);
});
