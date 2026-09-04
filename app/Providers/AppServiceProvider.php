<?php

namespace App\Providers;

use App\Contracts\CancelSubscriptionProvider;
use App\Contracts\ChangeSubscriptionPlanProvider;
use App\Contracts\CreatePaymentMethodIntentProvider;
use App\Contracts\CreateSessionProvider;
use App\Contracts\DeletePaymentMethodProvider;
use App\Contracts\ResolvePromotionCodeProvider;
use App\Contracts\ResumeSubscriptionProvider;
use App\Contracts\SyncPaymentMethodProvider;
use App\Contracts\SyncPlanPricesProvider;
use App\Contracts\SyncSubscriptionProvider;
use App\Contracts\UpdateBillingAddressProvider;
use App\Services\Stripe\CancelSubscriptionService;
use App\Services\Stripe\ChangeSubscriptionPlanService;
use App\Services\Stripe\CreatePaymentMethodIntentService;
use App\Services\Stripe\CreateSessionService;
use App\Services\Stripe\DeletePaymentMethodService;
use App\Services\Stripe\ResolvePromotionCodeService;
use App\Services\Stripe\ResumeSubscriptionService;
use App\Services\Stripe\SyncPaymentMethodService;
use App\Services\Stripe\SyncPlanPricesService;
use App\Services\Stripe\SyncSubscriptionService;
use App\Services\Stripe\UpdateBillingAddressService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Disable Cashier's auto-registered web routes to avoid CSRF issues. Webhook is registered manually in api.php.
        Cashier::ignoreRoutes();

        // Billing runs through one provider at a time. Swapping to another means writing
        // the implementations under App\Services\{Provider} and rebinding them here.
        $this->app->bind(UpdateBillingAddressProvider::class, UpdateBillingAddressService::class);
        $this->app->bind(CreateSessionProvider::class, CreateSessionService::class);
        $this->app->bind(SyncSubscriptionProvider::class, SyncSubscriptionService::class);
        $this->app->bind(ChangeSubscriptionPlanProvider::class, ChangeSubscriptionPlanService::class);
        $this->app->bind(CancelSubscriptionProvider::class, CancelSubscriptionService::class);
        $this->app->bind(ResumeSubscriptionProvider::class, ResumeSubscriptionService::class);
        $this->app->bind(ResolvePromotionCodeProvider::class, ResolvePromotionCodeService::class);
        $this->app->bind(SyncPlanPricesProvider::class, SyncPlanPricesService::class);
        $this->app->bind(CreatePaymentMethodIntentProvider::class, CreatePaymentMethodIntentService::class);
        $this->app->bind(SyncPaymentMethodProvider::class, SyncPaymentMethodService::class);
        $this->app->bind(DeletePaymentMethodProvider::class, DeletePaymentMethodService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dev/test only: throws on lazy-loaded relations to catch missing eager loads (N+1) before they reach production.
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            $email = strtolower($request->string('email'));

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
