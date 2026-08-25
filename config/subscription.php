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
    | Applies to "trial" mode only. When true, nothing is stamped at
    | registration so the user is gated from the first request, and their
    | trial instead begins at their first subscribe. When false the trial
    | is stamped at registration and the clock runs whether they subscribe
    | or not. No card is collected at registration either way.
    |
    */

    'require_card_upfront' => (bool) env('SUBSCRIPTION_REQUIRE_CARD', false),

    /*
    |--------------------------------------------------------------------------
    | Automatic Tax
    |--------------------------------------------------------------------------
    |
    | Hands tax calculation to the billing provider on the first charge and on
    | every renewal after it. Off by default because it also has to be switched
    | on in the provider's own dashboard, and a subscription will be rejected if
    | it is enabled on only one side.
    |
    | A billing address is a precondition of subscribing either way, so this
    | flag decides nothing more than what goes on the subscription itself.
    | Users without one are turned away with "subscription.address_required"
    | before the provider is ever called.
    |
    */

    'automatic_tax' => (bool) env('SUBSCRIPTION_AUTOMATIC_TAX', false),

];
