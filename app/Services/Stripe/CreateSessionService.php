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
     * The Checkout Elements SDK takes no locale option, so unlike a plain
     * Elements integration the locale can only be set on the session.
     *
     * Stripe's locale enum carries no region for English, so the tags stored
     * in config('user.supported_locales') have to be mapped onto it. Adding a
     * locale there means adding it here too, otherwise it falls through to
     * auto and the session follows the browser rather than the user.
     */
    private const LOCALES = [
        'en-US' => 'en',
        'en-CA' => 'en',
        'fr-CA' => 'fr-CA',
    ];

    /**
     * Open a Checkout Session and hand back the secret the client mounts its
     * address and payment elements against. Nothing else is created here. The
     * address, the card, the tax and the subscription all hang off the session
     * and only exist once the user confirms, so a user who abandons the page
     * leaves a session that ages out on its own.
     *
     * Stripe creates the subscription inside that confirm, in the browser, so
     * this is the only place the server sees the user and the only place the
     * check below can run at all.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): ServiceResult
    {
        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        /**
         * The check goes before anything at Stripe because it reads the local
         * subscription, so it needs neither the customer nor the sweep, and a
         * refused user costs no call at all.
         */
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

            $payload = [
                'ui_mode' => 'elements',
                'mode' => 'subscription',
                'locale' => self::LOCALES[$user->locale] ?? 'auto',
                'customer' => $customer->id,
                'line_items' => [
                    ['price' => $plan->priceId($interval), 'quantity' => 1],
                ],
                'return_url' => config('app.frontend_url') . '/subscribe',
                /**
                 * The payment element only looks the customer's saved cards up
                 * when this is on. Without it the user is handed blank card
                 * fields with a card already on file, and it also turns on the
                 * element's own save consent checkbox.
                 */
                'saved_payment_method_options' => ['payment_method_save' => 'enabled'],
                /**
                 * The card confirmed here becomes the subscription's default,
                 * so every renewal after the first invoice charges it.
                 */
                'subscription_data' => [
                    'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                ],
            ];

            /**
             * The address the user enters in the session only reaches the
             * customer through customer_update, and it has to reach it,
             * otherwise the renewals after the first invoice have no tax
             * location to calculate from. Stripe also refuses the create
             * without it once automatic tax is on and a customer is passed.
             *
             * Both are skipped for a customer with a card on file, since the
             * payment method flow already wrote an address there. Asking again
             * puts a step in front of a user with nothing to correct, and
             * customer_update would overwrite a tax address from a form they
             * did not come to fill in.
             */
            if (!$this->hasSavedPaymentMethod($customer->id)) {
                $payload['billing_address_collection'] = 'required';
                $payload['customer_update'] = ['address' => 'auto', 'name' => 'auto'];
            }

            if (config('subscription.automatic_tax')) {
                $payload['automatic_tax'] = ['enabled' => true];
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
     * A user who already has a subscription never reaches here, since the
     * check above refuses them before anything at Stripe is touched.
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

    /**
     * Whether the customer has a card the session will hand back in
     * savedPaymentMethods, which is what the client opens its confirm step on.
     *
     * Read from Stripe and filtered to the same allow_redisplay the session
     * filters on, so the two cannot disagree. A local flag drifts, and it only
     * knows about the default payment method where the session surfaces any
     * saved one, which would land a user on a confirm step of a session that
     * was still asking for an address.
     */
    protected function hasSavedPaymentMethod(string $customerId): bool
    {
        $paymentMethods = Cashier::stripe()->customers->allPaymentMethods($customerId, [
            'allow_redisplay' => 'always',
            'limit' => 1,
        ]);

        return count($paymentMethods->data) > 0;
    }
}
