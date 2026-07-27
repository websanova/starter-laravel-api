# Subscriptions

This doc covers all the various user flows (back to front end) for the various subscription modes and integrations. It is based on using Stripe so other implementations may vary around initial setup intents and payment methods but should otherwise by pretty much the same.

## Summary

**`config/subscriptions.php`**

Central config where the mode, trial days, card requried up front and product ids are set.

* `mode` - Includes `freemium`, `trial`, `required` where trial is intended to gate the app after some x amount of days regardless of a card being required up front or not.
* `require_card_upfront` - Boolean on whether a card is requried up front. Applies to `freemium` and `trial` only as `required` mode automatically means a card/susbcription would be required.
* `trial_days` - Sets the number of trial days when subscription mode is set to `trial`. Note that the trial days locks into whatever the value was set at for the user at that time. If a user was locked in at 14 trial days and it gets changed to 21 days, only new trial users get that new value.

**`GET /settings`**

Includes `subscription_mode`, `subscription_card_upfront`, `subscription_trial_days` values from the subscriptions config which will be required by the client to handle various flows and display logic.

**`GET /plans`**

Public list of active, public plans. Complimentary and internal plans never appear here. Any names or features should be translated API side for use in notificaitons and emails.

* `name`- Client should display this for the plan name.
* `tier` - Ordering rank. Compare against the tier on the profile plan to know whether a change is an upgrade or a downgrade.
* `features` - The limit map for the plan. `null` on a feature means unlimited.
* `prices` - Keyed by interval, each with `amount` and `currency`.

**`GET /profile`**

Everything the client needs to decide what to show without a second call. All of it is derived, none of it is stored on the user directly except `trial_ends_at`.

* `plan` - The current plan as `id`, `slug`, `name`, `tier` and `features`. Always present, it falls back to the free plan when nothing is subscribed so the client never has to null check it.
* `subscription` - The billing relationship as `interval` and `ends_at`, or null when there is no Stripe subscription at all. The null is the useful part, it's what the billing screen branches on to decide between an empty state and a manage view. Note this is not the same as `is_subscribed`, a `past_due` or cancelled user still has a subscription object.
* `subscription.interval` - `month` or `year`, whichever is currently being billed. Tells the billing screen which toggle is active and which direction a swap would go.
* `subscription.ends_at` - When access actually stops after a cancel. Null while the subscription is live.
* `has_payment_method` - Whether there's a default card on file. Saves a call to `/payment-method` just to decide between the subscribe flow and the card step, and it's what tells you to nag a trialing user before their trial converts.
* `is_subscribed` - Active or trialing. Same flag the gate middleware uses, so if this is false in `trial` or `required` mode the app is locked.
* `is_on_trial` - Inside the trial window, pairs with `trial_ends_at` for a countdown banner.
* `is_on_grace_period` - Cancelled but still paid up, pairs with `subscription.ends_at`. Offer resume here, not subscribe.
* `is_complimentary` - Plan was granted by an admin with no Stripe behind it. Hide all billing UI, the subscription endpoints will reject these users.
* `trial_ends_at` - When the trial ends. Stays flat rather than sitting under `subscription` because a trial can exist with no subscription behind it at all.

**`GET /setup-intent`**

Returns a `client_secret` for Stripe.js. The API never sees raw card data, the client collects the card against this secret and sends back only the resulting payment method id. Fetch a fresh one per attempt, they're single use.

**`GET /payment-method`**

The default card on file, or `data: null` when there isn't one. One call decides between showing "Pay with VISA ...4242" and a full card form.

* `id` - The `pm_...` id.
* `brand`, `last_four`, `exp_month`, `exp_year` - Display only.

**`PUT /payment-method`**

Attaches the payment method id returned by Stripe.js and makes it default, replacing whatever was there. There's no separate add vs update because a user has exactly one default card and that's the whole model.

* `payment_method` - Required, the `pm_...` id.

**`DELETE /payment-method`**

Detaches the default card. Rejected while a live subscription exists, otherwise the next renewal quietly fails and drops the user into `past_due`.

**`GET /subscription`**

The Stripe side state, kept separate from `/profile` because most screens never need it. Returns `data: null` rather than a 404 when there's no subscription record, so the billing screen renders its empty state off the same call.

* `stripe_status` - Straight from Stripe. `trialing`, `active`, `past_due`, `canceled`, `incomplete`. This is what drives the recovery flows.
* `stripe_price` - The price id currently attached.
* `ends_at` - Set once cancelled, when access stops.
* `trial_ends_at` - When the trial converts.
* `on_grace_period`, `quantity`, `created_at`, `updated_at`

**`PUT /subscription`**

One endpoint for both subscribe and swap. If a subscription already exists it swaps and prorates, otherwise it creates. The client doesn't need to know which, it sends the plan it wants and refetches the profile.

* `plan` - Plan slug. Only active, public plans with a Stripe price attached pass validation, so free and complimentary plans are rejected here by design.
* `interval` - `month` or `year`.
* `promotion_code` - Optional. Only applies on create, Stripe attaches discounts at subscription creation so it's ignored on a swap.

Responses to handle:

* `200` - Done, refetch the profile.
* `200` with `client_secret` - Stripe wants 3D Secure. Confirm it with Stripe.js, then refetch.
* `402` - Card declined, send the user back to the card step.
* `422` - Bad promotion code or plan.

**`DELETE /subscription`**

Cancels at period end rather than immediately, which puts the user into the grace period. Access continues until `ends_at`. 403 when there's nothing to cancel.

**`PATCH /subscription/resume`**

Undoes a cancel while still inside the grace period. Once the period has ended there's nothing to resume and it 403s, at that point it's a fresh subscribe. Complimentary users are rejected too since there's no Stripe subscription behind them.

**`GET /subscription/coupon/{code}`**

Lets the client validate and preview a promo code before the subscribe form is submitted, so the discount can be shown against the price rather than surprising the user after the charge.

* `code`, `percent_off`, `amount_off`, `currency`, `duration`, `duration_in_months`

Invalid or expired codes return a 422 keyed on `promotion_code`, the same shape as posting a bad code to `PUT /subscription`, so the client reuses one error handler for both.

## Trials

The rule the whole thing hangs off is that the trial clock starts when access starts. Everything else falls out of that.

With `require_card_upfront` off the user is let straight in at registration, so `users.trial_ends_at` is stamped there and the clock runs whether or not they ever open a billing screen. With it on they hit a paywall from the first second, so nothing is stamped at registration. There is no access to burn, and their trial only begins when they subscribe.

That distinction matters because otherwise a paywalled user who registers, bounces, and comes back three weeks later would find a trial they were never able to use already expired.

**Carrying the clock into Stripe**

When someone subscribes mid trial we hand Stripe the existing `trial_ends_at` rather than a fresh day count, so the clock carries over untouched. Subscribe on day 3 of 14 and Stripe trials for the remaining 11. Cashier then moves the date onto the subscription row and clears the user column, which is why the profile reads through `trialEndsAt()` instead of the raw column, it returns whichever one currently owns the date.

An expired `trial_ends_at` grants nothing and the charge happens immediately, otherwise waiting out the free period and then subscribing would quietly hand out a second one. A user with no date and no past subscriptions is a genuine first timer, and in `trial` mode with a card required up front that first subscribe is where their trial begins. Anyone with subscription rows already is returning after a cancel, so they get nothing. Without that check a user could farm trials forever by cancelling and resubscribing.

**The gate doesn't care about the mode**

`trial` and `required` run the same check, which is complimentary or trialing or subscribed. The mode only decides what happens at registration, never who gets through the gate. That is what makes switching modes on a live user base survivable.

**Switching trial to required**

Users mid trial keep it. Their `trial_ends_at` is already set and the gate still honours `is_on_trial`, so they finish the days they were promised and the remainder still carries into Stripe when they subscribe. Nothing has to be backfilled or migrated. New registrations get nothing stamped and are locked out until they pay, which is the point of the switch.

**Switching required to trial**

New registrations get a trial. Users who signed up while `required` was on generally do not, and that is worth being explicit about. They were never stamped at registration, and if they already subscribed under the old mode their subscription rows disqualify them anyway. So flipping the mode does not retroactively hand a free trial to your existing paying base.

The one seam is a user who registered under `required`, never subscribed, and is still sitting there when you flip to `trial` with a card required up front. They look identical to a brand new user, no date and no subscription rows, so their first subscribe starts a full trial. That is almost certainly what you want, but it means the promise is "anyone who has not paid yet" rather than strictly "new signups only".
