<?php

use App\Enums\SubscriptionMode;

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Mode
    |--------------------------------------------------------------------------
    |
    | Controls how the application handles subscriptions at signup.
    |
    | "freemium"  - Users start on the free plan, can upgrade anytime.
    | "trial"     - Users get a trial period, then must subscribe.
    | "required"  - Payment is required before accessing gated features.
    |
    */

    'mode' => SubscriptionMode::from(env('SUBSCRIPTION_MODE', 'freemium')),

    /*
    |--------------------------------------------------------------------------
    | Trial Days
    |--------------------------------------------------------------------------
    |
    | The number of trial days granted when mode is set to "trial".
    | Ignored in other modes.
    |
    */

    'trial_days' => (int) env('SUBSCRIPTION_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Require Card Upfront
    |--------------------------------------------------------------------------
    |
    | When true, a payment method is collected at registration even if the
    | user starts on a free plan or trial. When false, payment is only
    | collected when the user subscribes to a paid plan.
    |
    */

    'require_card_upfront' => (bool) env('SUBSCRIPTION_REQUIRE_CARD', false),

    /*
    |--------------------------------------------------------------------------
    | Stripe Products
    |--------------------------------------------------------------------------
    |
    | The Stripe product IDs for each paid plan and billing interval. These
    | are resolved here so the values are baked into the cached config and
    | remain available after "config:cache" runs.
    |
    */

    'stripe_products' => [
        'pro' => [
            'monthly' => env('STRIPE_PRODUCT_PRO_MONTHLY'),
            'yearly' => env('STRIPE_PRODUCT_PRO_YEARLY'),
        ],
    ],

];
