

---------- OLD ---------

The client reads config at boot, before auth, so it can build the right signup wizard instead of bouncing off a 403.

| Endpoint | Provides | Status |
| --- | --- | --- |
| `GET /profile` | `interval`, `tier`, `ends_at` | `[NEW]` fields |
| `GET /setup-intent` | `client_secret` for Stripe.js card collection | `[NEW]` |
| `GET|PUT|DELETE /payment-method` | Default card read, replace, remove | `[NEW]` |




        Route::get('/subscription', [App\Http\Controllers\App\SubscriptionController::class, 'show']);
        Route::put('/subscription', [App\Http\Controllers\App\SubscriptionController::class, 'update']);
        Route::delete('/subscription', [App\Http\Controllers\App\SubscriptionController::class, 'destroy']);
        Route::patch('/subscription/resume', [App\Http\Controllers\App\SubscriptionController::class, 'resume']);


        Route::get('/subscription/coupon/{code}', [App\Http\Controllers\App\SubscriptionCouponController::class, 'show']);




## Registration (all modes)

Identical regardless of mode. Nothing touches Stripe.

```
POST /register
  plan_id = null  ->  User::plan() resolves to Free via withDefault()
  no stripe_id
  no subscription
  no card
```

What differs is whether the gate lets the user into the app afterwards.

| Mode | `EnsureSubscribed` on bookmarks / categories / tags |
| --- | --- |
| `freemium` | Always passes |
| `trial` | Passes only if complimentary, on trial, or subscribed. Fresh user is blocked. |
| `required` | Passes only if complimentary or subscribed. Fresh user is blocked. |

## Mode: freemium

```
REGISTER -> plan=free, no customer, no card
  |
  +-- card_upfront = false   (default)
  |     login -> app opens, full free-tier access immediately
  |     user hits a limit (10 bookmarks) -> 403 from the store request
  |       -> upgrade prompt -> UPGRADE FLOW
  |
  +-- card_upfront = true
        client reads the flag at boot, signup becomes a 2-step wizard:
          register -> card step -> app
        GET /setup-intent -> Stripe.js confirm -> PUT /payment-method
        -> app opens, still on free tier
        server-side enforcement of this flag is [NEW]
```

### Upgrade flow

```
GET /payment-method
  null -> GET /setup-intent -> Stripe.js confirm -> PUT /payment-method
  card -> show "Pay with VISA ...4242", skip collection

PUT /subscription {plan:pro, interval, promotion_code?}
  200                 -> active, refetch profile
  200 + client_secret -> SCA: confirm PaymentIntent, then refetch profile
  422                 -> bad promotion code
  402                 -> declined, back to the card step
```

## Mode: trial

```
REGISTER -> plan=free, no subscription, gate 403s the app
            client already knows mode=trial, so it routes straight to
            onboarding rather than bouncing off a 403
  |
  +-- card_upfront = false
  |     "Start your {subscription_trial_days}-day trial" screen
  |     PUT /subscription {plan:pro, interval}
  |       -> trialing, no card required. This works today.
  |     app opens, banner "Trial ends {trial_ends_at}"
  |       |
  |       +-- card added before day 14 -> auto-charges -> active
  |       |
  |       +-- no card at day 14 -> invoice fails -> past_due -> gate 403
  |             -> forced card screen -> PUT /payment-method
  |             -> no endpoint to retry the open invoice [NEW]
  |
  +-- card_upfront = true
        register -> card step -> trial step -> app
        GET /setup-intent -> PUT /payment-method -> PUT /subscription
        -> trialing with a card, day 14 charges silently -> active
```

Nothing starts the trial at registration. The client has to call `PUT /subscription`. Making it automatic is a change to the register flow, not a client concern.

## Mode: required

```
REGISTER -> plan=free, no subscription, gate 403s everything

  client knows mode=required, so the signup wizard ends on plan selection
  and there is no app access until a subscription exists

    GET /setup-intent -> Stripe.js confirm -> PUT /payment-method
    PUT /subscription {plan:pro, interval}
      200                 -> active -> app opens
      200 + client_secret -> SCA -> confirm -> app opens
      402                 -> stay on the wall
```

`card_upfront` is redundant in this mode. A card is always required to get in.

## Post-subscription states

Shared across all modes.

### Active

```
change interval -> PUT /subscription {plan:pro, interval:yearly}
                   prorated swap, needs `interval` on profile to know
                   which direction to offer
change card     -> GET /setup-intent -> PUT /payment-method
cancel          -> DELETE /subscription -> grace period
```

### Grace period

`is_on_grace_period` true, `ends_at` set. Show "You keep Pro until {ends_at}".

```
resume -> PATCH /subscription/resume -> active
ends   -> stripe_status canceled
    freemium -> gate passes, but plan_id is still "pro"
                => Pro feature limits persist forever   [BUG]
    trial    -> gate 403 -> re-subscribe wizard
    required -> gate 403 -> re-subscribe wizard
```

### Past due

Renewal charge failed.

```
freemium -> gate passes, plan_id still pro              [BUG, same cause]
trial    -> gate 403
required -> gate 403
recovery -> PUT /payment-method, then no invoice-retry endpoint [NEW]
```

### Complimentary

Admin-assigned only, via `/admin/users/{user}/subscription`.

- Gate passes in all three modes
- Cannot swap, `planPublic()` rejects plans with no `stripe_price_id`
- Cannot resume, `ResumeRequest` excludes complimentary
- Hide all billing UI

## Gaps

| Gap | Notes |
| --- | --- |
| No way to attach a card | No `/setup-intent`, no `/payment-method`. Blocks every paid path except a cardless trial. |
| `IncompletePayment` unhandled | `create(null)` throws, nothing renders it, so freemium and required subscribes 500. |
| Trial never auto-starts | Fresh user in trial mode is locked out until they call `PUT /subscription`. |
| No invoice retry | After fixing a card on a past-due subscription there is no way to retry the charge. |
| No downgrade to free | `planPublic()` requires a `stripe_price_id`, so cancel is the only exit from Pro. |
| `plan_id` never resets | `cancelPlan()` leaves it on Pro, so `canUsePlanFeature()` keeps reading Pro limits after the subscription ends. |
| `card_upfront` unenforced | Config is read by the client but nothing on the server acts on it. |
| `client_secret` branch unreachable | `validate()` throws on `requires_action` before the controller can return it. |

The two `[BUG]` rows above are the same root cause as the `plan_id` gap.
