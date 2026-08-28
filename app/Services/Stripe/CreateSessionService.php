<?php

namespace App\Services\Stripe;

use App\Contracts\CreateSessionProvider;
use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class CreateSessionService implements CreateSessionProvider
{
    /**
     * Open a Checkout Session and hand back the secret the client mounts its
     * address and payment elements against. Nothing else is created here. The
     * address, the card, the promotion code, the tax and the subscription all
     * hang off the session and only exist once the user confirms, so a user who
     * abandons the page leaves a session that ages out on its own.
     *
     * Stripe creates the subscription inside that confirm, in the browser, so
     * this is the only place the server sees the user and the only place the
     * check below can run at all.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): ServiceResult
    {
        $user->loadMissing('subscriptions');

        try {
            /**
             * The customer is created here when there isn't one, since a
             * session has nothing to hang off otherwise. It goes first because
             * the sweep below is addressed to a customer, and one made a moment
             * ago has nothing to sweep. Passing it is also what satisfies the
             * session's email requirement, so no contact details element is
             * needed.
             */
            $customer = $user->createOrGetStripeCustomer();

            $this->expireOpenSessions($customer->id);

            $subscription = $user->subscription();

            if ($subscription) {
                /**
                 * Past due and unpaid are named apart from a live subscription
                 * so the client can send the user to the card page rather than
                 * tell them they are already subscribed. Both still refuse,
                 * since the subscription exists at Stripe either way and a
                 * second one is wrong regardless. Checked first because whether
                 * a past due subscription counts as valid is a Cashier setting,
                 * and this does not depend on how it is set.
                 */
                if ($subscription->pastDue() || $subscription->stripe_status === StripeSubscription::STATUS_UNPAID) {
                    return ServiceResult::error('payment_required');
                }

                if ($subscription->valid()) {
                    return ServiceResult::error('already_subscribed');
                }
            }

            $payload = [
                'ui_mode' => 'elements',
                'mode' => 'subscription',
                'customer' => $customer->id,
                'line_items' => [
                    ['price' => $plan->priceId($interval), 'quantity' => 1],
                ],
                'return_url' => config('app.frontend_url') . '/subscribe',
                'billing_address_collection' => 'required',
                'allow_promotion_codes' => true,
                /**
                 * The address the user enters in the session only reaches the
                 * customer through this, and it has to reach it, otherwise the
                 * renewals after the first invoice have no tax location to
                 * calculate from. Stripe also refuses the create without it
                 * once automatic tax is on and a customer is passed.
                 */
                'customer_update' => ['address' => 'auto'],
            ];

            if (config('subscription.automatic_tax')) {
                $payload['automatic_tax'] = ['enabled' => true];
            }

            /**
             * trial_end rather than trial_period_days, since a user carrying a
             * partial trial keeps whatever is left of it and a whole number of
             * days cannot say that. Built here rather than through Cashier's
             * checkout builder, which floors the same value at 48 hours out and
             * would hand back time the user has already spent.
             */
            if ($trialEndsAt = $user->resolveTrialEnd()) {
                $payload['subscription_data']['trial_end'] = $trialEndsAt->getTimestamp();
            }

            $session = Cashier::stripe()->checkout->sessions->create($payload);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        /**
         * The id rides along with the secret because the sync call the client
         * makes after confirming is addressed to a session, and this is the
         * only place the client ever sees which one it is holding.
         */
        return ServiceResult::success([
            'id' => $session->id,
            'client_secret' => $session->client_secret,
        ]);
    }

    /**
     * Close every session the customer still has open, so the one about to be
     * handed out is the only one that can be confirmed. Without this a tab left
     * open on another device stays good for up to a day and buys a second
     * subscription when the user comes back to it.
     *
     * Running before the subscription check is what makes that check useful,
     * since a session swept here can no longer complete behind it.
     */
    protected function expireOpenSessions(string $customerId): void
    {
        $stripe = Cashier::stripe();

        $sessions = $stripe->checkout->sessions->all([
            'customer' => $customerId,
            'status' => Session::STATUS_OPEN,
        ]);

        foreach ($sessions->data as $session) {
            try {
                $stripe->checkout->sessions->expire($session->id);
            } catch (ApiErrorException) {
                // Only an open session can be expired, and one that completed
                // between the list and here is no longer open.
            }
        }
    }
}
