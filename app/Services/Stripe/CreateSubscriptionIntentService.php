<?php

namespace App\Services\Stripe;

use App\Contracts\CreateSubscriptionIntentProvider;
use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class CreateSubscriptionIntentService implements CreateSubscriptionIntentProvider
{
    /**
     * Open a payment session and hand back the secret a payment element mounts
     * against. The subscription is created here and up front, sitting
     * incomplete until the client confirms it, so this has to stay safe to
     * call again on a page refresh or on a second attempt after the user
     * walked away from the first one.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): ServiceResult
    {
        /**
         * The customer has to carry a billing address before the subscription
         * is created, tax on or off, and nothing in this flow collects one, so
         * it has to already be on the user. A local failure that names the
         * reason beats a provider error the client cannot act on.
         */
        if (!$user->hasBillingAddress()) {
            return ServiceResult::error('address_required');
        }

        $priceId = $plan->priceId($interval);
        $subscription = $user->subscription();

        try {
            /**
             * Tax is computed off the customer's address and the first invoice
             * locks its amount the moment the subscription is created, so this
             * is the last point the address can reach Stripe. Pushed on every
             * attempt rather than checked first, since reading the customer
             * back costs the same call as writing it, and a customer created
             * outside this flow carries whatever address it came with.
             */
            $user->updateOrCreateStripeCustomer([
                'address' => $user->billingAddress(),
                'tax' => ['validate_location' => 'immediately'],
            ]);

            /**
             * The local status is only as fresh as the last webhook, and this
             * is hit again the moment the payment page reloads. Pull the real
             * status before deciding anything, or a subscription paid seconds
             * ago still reads incomplete and gets torn down below. It also
             * lets Stripe's own expiry land, which drops through to a fresh
             * attempt untouched.
             */
            if ($subscription && $subscription->incomplete()) {
                $subscription->syncStripeStatus();
            }

            if ($subscription && $subscription->valid()) {
                return ServiceResult::error('already_subscribed');
            }

            /**
             * TODO: Past due and unpaid are billing failures on a subscription
             * that already exists. Stripe keeps that subscription and its open
             * invoice, so the fix is to attach a new payment method and retry
             * the invoice, not to open a second subscription alongside the
             * failing one. Until that endpoint exists the request is rejected
             * here.
             */
            if ($subscription && ($subscription->pastDue() || $subscription->stripe_status === StripeSubscription::STATUS_UNPAID)) {
                return ServiceResult::error('payment_required');
            }

            /**
             * An incomplete subscription is an attempt still in flight. The
             * same price is the refresh case, so it is handed back as is and
             * Stripe keeps the invoice and its element session alive. A
             * different price means they changed their mind on the page, and
             * swap refuses to touch an incomplete subscription, so the old
             * attempt is cancelled outright and a new one built.
             */
            $reuse = $subscription && $subscription->incomplete() && $subscription->stripe_price === $priceId;

            if ($subscription && $subscription->incomplete() && !$reuse) {
                $subscription->cancelNow();
            }

            if (!$reuse) {
                $builder = $user->newSubscription('default', $priceId)->ignoreIncompletePayments();

                if ($trialEndsAt = $user->resolveTrialEnd()) {
                    $builder->trialUntil($trialEndsAt);
                }

                /**
                 * Stripe leaves the subscription's default payment method
                 * unset unless it is told to keep the one that paid, and
                 * SyncPaymentMethodService reads the card off exactly that field, so
                 * renewals would bill against nothing without this.
                 */
                $options = [
                    'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                ];

                if (config('subscription.automatic_tax')) {
                    $options['automatic_tax'] = ['enabled' => true];
                }

                $subscription = $builder->create(null, [], $options);
            }

            /**
             * A subscription with something to charge for exposes the secret on
             * its invoice, while one starting on a trial has nothing to charge
             * yet, so Stripe hangs a setup intent off it instead. The client
             * confirms those two with different calls, so which one came back
             * goes along with it.
             */
            $stripeSubscription = $subscription->asStripeSubscription([
                'latest_invoice.confirmation_secret',
                'pending_setup_intent',
            ]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        if ($secret = $stripeSubscription->latest_invoice->confirmation_secret->client_secret ?? null) {
            return ServiceResult::success(['client_secret' => $secret, 'type' => 'payment']);
        }

        if ($secret = $stripeSubscription->pending_setup_intent->client_secret ?? null) {
            return ServiceResult::success(['client_secret' => $secret, 'type' => 'setup']);
        }

        return ServiceResult::error('provider_unavailable');
    }
}
