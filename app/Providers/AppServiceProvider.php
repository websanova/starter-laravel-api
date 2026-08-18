<?php

namespace App\Providers;

use App\Contracts\CancelSubscriptionProvider;
use App\Contracts\ChangeSubscriptionPlanProvider;
use App\Contracts\CreatePaymentMethodIntentProvider;
use App\Contracts\CreateSubscriptionIntentProvider;
use App\Contracts\ResolvePromotionCodeProvider;
use App\Contracts\ResumeSubscriptionProvider;
use App\Contracts\SyncPaymentMethodProvider;
use App\Contracts\SyncPlanPricesProvider;
use App\Contracts\SyncSubscriptionProvider;
use App\Contracts\UpdateBillingAddressProvider;
use App\Services\Stripe\CancelSubscription;
use App\Services\Stripe\ChangeSubscriptionPlan;
use App\Services\Stripe\CreatePaymentMethodIntent;
use App\Services\Stripe\CreateSubscriptionIntent;
use App\Services\Stripe\ResolvePromotionCode;
use App\Services\Stripe\ResumeSubscription;
use App\Services\Stripe\SyncPaymentMethod;
use App\Services\Stripe\SyncPlanPrices;
use App\Services\Stripe\SyncSubscription;
use App\Services\Stripe\UpdateBillingAddress;
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
        $this->app->bind(UpdateBillingAddressProvider::class, UpdateBillingAddress::class);
        $this->app->bind(CreateSubscriptionIntentProvider::class, CreateSubscriptionIntent::class);
        $this->app->bind(SyncSubscriptionProvider::class, SyncSubscription::class);
        $this->app->bind(ChangeSubscriptionPlanProvider::class, ChangeSubscriptionPlan::class);
        $this->app->bind(CancelSubscriptionProvider::class, CancelSubscription::class);
        $this->app->bind(ResumeSubscriptionProvider::class, ResumeSubscription::class);
        $this->app->bind(ResolvePromotionCodeProvider::class, ResolvePromotionCode::class);
        $this->app->bind(SyncPlanPricesProvider::class, SyncPlanPrices::class);
        $this->app->bind(CreatePaymentMethodIntentProvider::class, CreatePaymentMethodIntent::class);
        $this->app->bind(SyncPaymentMethodProvider::class, SyncPaymentMethod::class);
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
