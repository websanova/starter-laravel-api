<?php

use App\Http\Controllers\Admin\UserAvatarController as AdminUserAvatarController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserForceDeleteController as AdminUserForceDeleteController;
use App\Http\Controllers\Admin\UserPasswordResetController as AdminUserPasswordResetController;
use App\Http\Controllers\Admin\UserRestoreController as AdminUserRestoreController;
use App\Http\Controllers\Admin\UserRoleController as AdminUserRoleController;
use App\Http\Controllers\ChangeEmailController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MeAvatarController;
use App\Http\Controllers\MeBookmarkController;
use App\Http\Controllers\MeCategoryController;
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
        Route::patch('/me/password', [MePasswordController::class, 'update']);
    });

    Route::middleware(['verified', 'password-updated'])->group(function () {
        Route::get('/me', [MeController::class, 'show']);
        Route::patch('/me', [MeController::class, 'update']);
        Route::delete('/me', [MeController::class, 'destroy']);
        Route::post('/me/email', [MeEmailController::class, 'store']);
        Route::post('/me/avatar', [MeAvatarController::class, 'store']);
        Route::delete('/me/avatar', [MeAvatarController::class, 'destroy']);

        Route::get('/me/categories', [MeCategoryController::class, 'index']);
        Route::post('/me/categories', [MeCategoryController::class, 'store']);
        Route::put('/me/categories/{category}', [MeCategoryController::class, 'update']);
        Route::delete('/me/categories/{category}', [MeCategoryController::class, 'destroy']);

        Route::get('/me/bookmarks', [MeBookmarkController::class, 'index']);
        Route::post('/me/bookmarks', [MeBookmarkController::class, 'store']);
        Route::put('/me/bookmarks/{bookmark}', [MeBookmarkController::class, 'update']);
        Route::delete('/me/bookmarks/{bookmark}', [MeBookmarkController::class, 'destroy']);
    });

    Route::prefix('admin')->middleware(['verified', 'password-updated'])->group(function () {
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
});
