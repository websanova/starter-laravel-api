<?php

use Illuminate\Support\Facades\Route;

Route::get('/up', fn () => response()->json(['status' => 'ok']));

Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook']);

Route::get('/plans', [App\Http\Controllers\App\PlanController::class, 'index']);

Route::get('/settings', [App\Http\Controllers\App\SettingController::class, 'show']);

Route::get('/timezones', [App\Http\Controllers\App\TimezoneController::class, 'index']);

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\App\RegisterController::class, 'store']);
    Route::post('/login', [App\Http\Controllers\App\LoginController::class, 'store']);
    Route::post('/forgot-password', [App\Http\Controllers\App\ForgotPasswordController::class, 'store']);
    Route::post('/reset-password', [App\Http\Controllers\App\ResetPasswordController::class, 'store']);
    Route::post('/change-email', [App\Http\Controllers\App\ChangeEmailController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'track-active'])->group(function () {
    Route::post('/logout', [App\Http\Controllers\App\LoginController::class, 'destroy']);
    Route::post('/refresh', [App\Http\Controllers\App\LoginController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'track-active'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\App\ProfileController::class, 'show']);

    Route::post('/verify', [App\Http\Controllers\App\VerificationController::class, 'verify']);
    Route::post('/verify/resend', [App\Http\Controllers\App\VerificationController::class, 'resend']);

    Route::middleware('verified')->group(function () {
        Route::patch('/password', [App\Http\Controllers\App\PasswordController::class, 'update']);
    });

    Route::middleware(['verified', 'password-updated'])->group(function () {
        Route::get('/sync', [App\Http\Controllers\App\SyncController::class, 'show']);

        Route::get('/notifications', [App\Http\Controllers\App\NotificationController::class, 'index']);
        Route::post('/notifications/read', [App\Http\Controllers\App\NotificationReadController::class, 'store']);
        Route::patch('/notifications/{notification}', [App\Http\Controllers\App\NotificationController::class, 'update']);

        Route::patch('/profile', [App\Http\Controllers\App\ProfileController::class, 'update']);
        Route::delete('/profile', [App\Http\Controllers\App\ProfileController::class, 'destroy']);
        Route::post('/email', [App\Http\Controllers\App\EmailController::class, 'store']);
        Route::post('/avatar', [App\Http\Controllers\App\AvatarController::class, 'store']);
        Route::delete('/avatar', [App\Http\Controllers\App\AvatarController::class, 'destroy']);

        Route::get('/subscription', [App\Http\Controllers\App\SubscriptionController::class, 'show']);
        Route::put('/subscription', [App\Http\Controllers\App\SubscriptionController::class, 'update']);
        Route::delete('/subscription', [App\Http\Controllers\App\SubscriptionController::class, 'destroy']);
        Route::patch('/subscription/resume', [App\Http\Controllers\App\SubscriptionController::class, 'resume']);
        Route::get('/subscription/coupon/{code}', [App\Http\Controllers\App\SubscriptionCouponController::class, 'show']);

        Route::middleware('subscribed')->group(function () {
            Route::get('/categories', [App\Http\Controllers\App\CategoryController::class, 'index']);
            Route::post('/categories', [App\Http\Controllers\App\CategoryController::class, 'store']);
            Route::put('/categories/{category}', [App\Http\Controllers\App\CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [App\Http\Controllers\App\CategoryController::class, 'destroy']);

            Route::get('/tags', [App\Http\Controllers\App\TagController::class, 'index']);
            Route::post('/tags', [App\Http\Controllers\App\TagController::class, 'store']);
            Route::put('/tags/{tag}', [App\Http\Controllers\App\TagController::class, 'update']);
            Route::delete('/tags/{tag}', [App\Http\Controllers\App\TagController::class, 'destroy']);

            Route::get('/bookmarks', [App\Http\Controllers\App\BookmarkController::class, 'index']);
            Route::post('/bookmarks', [App\Http\Controllers\App\BookmarkController::class, 'store']);
            Route::put('/bookmarks/{bookmark}', [App\Http\Controllers\App\BookmarkController::class, 'update']);
            Route::delete('/bookmarks/{bookmark}', [App\Http\Controllers\App\BookmarkController::class, 'destroy']);
        });
    });
});

Route::prefix('admin')->group(function () {
    Route::get('/settings', [App\Http\Controllers\App\SettingController::class, 'show']);

    Route::get('/timezones', [App\Http\Controllers\App\TimezoneController::class, 'index']);

    Route::middleware('throttle:auth')->group(function () {
        Route::post('/login', [App\Http\Controllers\Admin\LoginController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'track-active'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\Admin\LoginController::class, 'destroy']);
        Route::post('/refresh', [App\Http\Controllers\Admin\LoginController::class, 'update']);
    });
});

Route::prefix('admin')->middleware(['auth:sanctum', 'track-active', 'verified', 'password-updated', 'admin'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'show']);

    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show']);
    Route::patch('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update']);
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy']);
    Route::delete('/users/{user}/avatar', [App\Http\Controllers\Admin\UserAvatarController::class, 'destroy']);
    Route::delete('/users/{user}/force', [App\Http\Controllers\Admin\UserForceDeleteController::class, 'destroy'])->withTrashed();
    Route::patch('/users/{user}/restore', [App\Http\Controllers\Admin\UserRestoreController::class, 'update'])->withTrashed();
    Route::patch('/users/{user}/role', [App\Http\Controllers\Admin\UserRoleController::class, 'update']);
    Route::post('/users/{user}/password-reset', [App\Http\Controllers\Admin\UserPasswordResetController::class, 'store']);

    Route::get('/users/{user}/subscription', [App\Http\Controllers\Admin\UserSubscriptionController::class, 'show']);
    Route::delete('/users/{user}/subscription', [App\Http\Controllers\Admin\UserSubscriptionController::class, 'destroy']);
    Route::patch('/users/{user}/subscription/resume', [App\Http\Controllers\Admin\UserSubscriptionController::class, 'resume']);
    Route::post('/users/{user}/subscription/coupon', [App\Http\Controllers\Admin\UserSubscriptionCouponController::class, 'store']);
    Route::delete('/users/{user}/subscription/coupon', [App\Http\Controllers\Admin\UserSubscriptionCouponController::class, 'destroy']);

    Route::get('/users/{user}/bookmarks', [App\Http\Controllers\Admin\UserBookmarkController::class, 'index']);
    Route::delete('/users/{user}/bookmarks/{bookmark}', [App\Http\Controllers\Admin\UserBookmarkController::class, 'destroy'])->scopeBindings();

    Route::get('/users/{user}/categories', [App\Http\Controllers\Admin\UserCategoryController::class, 'index']);
    Route::delete('/users/{user}/categories/{category}', [App\Http\Controllers\Admin\UserCategoryController::class, 'destroy'])->scopeBindings();

    Route::get('/users/{user}/tags', [App\Http\Controllers\Admin\UserTagController::class, 'index']);
    Route::delete('/users/{user}/tags/{tag}', [App\Http\Controllers\Admin\UserTagController::class, 'destroy'])->scopeBindings();

    Route::get('/plans', [App\Http\Controllers\Admin\PlanController::class, 'index']);
    Route::post('/plans', [App\Http\Controllers\Admin\PlanController::class, 'store']);
    Route::get('/plans/{plan}', [App\Http\Controllers\Admin\PlanController::class, 'show']);
    Route::patch('/plans/{plan}', [App\Http\Controllers\Admin\PlanController::class, 'update']);
    Route::delete('/plans/{plan}', [App\Http\Controllers\Admin\PlanController::class, 'destroy']);
    Route::patch('/plans/{plan}/prices/{price}', [App\Http\Controllers\Admin\PlanPriceController::class, 'update'])->scopeBindings();

    Route::get('/stats', [App\Http\Controllers\Admin\StatController::class, 'index']);
});
