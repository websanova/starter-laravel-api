<?php

namespace App\Services\Stripe;

use App\Contracts\CreateSubscriptionProvider;
use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Subscription;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Subscription as StripeSubscription;

class CreateSubscriptionService implements CreateSubscriptionProvider
{
    /**
     * Create the subscription, last of the three steps. The address was settled
     * and the card stored and defaulted before this runs, so nothing about
     * either is collected or sent here. Stripe creates the first invoice and
     * settles it inside the create call, off session, against whatever default
     * the customer carries.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval, ?string $promotionCodeId = null): ServiceResult
    {
        /**
         * Both preconditions land the user back on an earlier step rather than
         * on an error they can do nothing with, so they are named locally
         * instead of left for Stripe to refuse further in.
         */
        if (!$user->hasStripeId() || !$user->hasBillingAddress()) {
            return ServiceResult::error('address_required');
        }

        if (!$user->pm_type) {
            return ServiceResult::error('payment_method_required');
        }

        $priceId = $plan->priceId($interval);

        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        try {
            if ($subscription) {
                $result = $this->reconcile($user, $subscription, $priceId);

                /**
                 * A result means the existing subscription answered the
                 * request, either by already satisfying it or by refusing it.
                 * Nothing back means it was torn down and the create below is
                 * the answer instead.
                 */
                if ($result) {
                    return $result;
                }
            }

            /**
             * Cashier defaults to default_incomplete, which opens the invoice
             * and waits for the client to confirm it. This flow has no element
             * left mounted by now, the card is stored and the user is on the
             * confirm step, so allowPaymentFailures is what tells Stripe to
             * attempt the charge itself and report how it went.
             */
            $builder = $user->newSubscription('default', $priceId)
                ->ignoreIncompletePayments()
                ->allowPaymentFailures();

            if ($trialEndsAt = $user->resolveTrialEnd()) {
                $builder->trialUntil($trialEndsAt);
            }

            if ($promotionCodeId) {
                $builder->withPromotionCode($promotionCodeId);
            }

            $options = [];

            if (config('subscription.automatic_tax')) {
                $options['automatic_tax'] = ['enabled' => true];
            }

            /**
             * No default payment method goes on the subscription. Stripe falls
             * back to the customer's when it has none, which leaves one pointer
             * to keep current instead of two.
             */
            $subscription = $builder->create(null, [], $options);

            $user->load('subscriptions');

            $user->fillPlan()->save();

            return $this->result($subscription);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }
    }

    /**
     * Work out what the subscription already on the user means for the request.
     * A result answers the request outright, null means it was cancelled and
     * the caller should build a fresh one.
     */
    protected function reconcile(User $user, Subscription $subscription, ?string $priceId): ?ServiceResult
    {
        if ($subscription->valid()) {
            /**
             * Same plan and interval is the request already satisfied, most
             * likely a double submit or a stale page. Anything else is a swap
             * and belongs to the change endpoint, which prorates.
             */
            return $subscription->hasPrice($priceId)
                ? $this->result($subscription)
                : ServiceResult::error('already_subscribed');
        }

        /**
         * Past due and unpaid went live and later failed a renewal. Stripe
         * keeps the subscription and its open invoice, so the fix is a new card
         * and a retry through whatever dunning path owns it, never a second
         * subscription alongside the failing one.
         */
        if ($subscription->pastDue() || $subscription->stripe_status === StripeSubscription::STATUS_UNPAID) {
            return ServiceResult::error('payment_required');
        }

        if (!$subscription->incomplete()) {
            return null;
        }

        /**
         * An incomplete subscription is a first charge that was refused. Its
         * invoice can still be paid as it stands, but only while it is for what
         * the user is asking for now and was priced where they are now. A
         * finalized invoice never recalculates its tax, and Stripe will not
         * repoint an incomplete subscription at a different price.
         */
        $invoice = $subscription->latestInvoice();

        if (!$invoice || !$subscription->hasPrice($priceId) || $this->taxLocationMoved($user, $invoice->customer_address)) {
            $subscription->cancelNow();

            return null;
        }

        try {
            $invoice->pay();
        } catch (CardException $e) {
            /**
             * The invoice charges whatever the customer default is now, so this
             * is the same card failing again unless the user changed it. The
             * subscription is untouched and stays incomplete either way.
             */
            return ServiceResult::error('payment_failed', ['debug' => [$e->getMessage()]]);
        }

        $subscription->syncStripeStatus();

        return $this->result($subscription);
    }

    /**
     * Whether the address an invoice was finalized against still resolves to
     * where the customer is now. Only country and postal code count, since they
     * are the pair a tax location comes from and a corrected street name leaves
     * the amount untouched.
     */
    protected function taxLocationMoved(User $user, mixed $invoiceAddress): bool
    {
        if (!$invoiceAddress) {
            return false;
        }

        return $invoiceAddress->country !== $user->billing_country
            || $invoiceAddress->postal_code !== $user->billing_postal_code;
    }

    /**
     * Hand back the subscription and, when the charge was challenged rather
     * than settled, the first invoice's secret so the client can run the
     * challenge without mounting anything.
     */
    protected function result(Subscription $subscription): ServiceResult
    {
        $clientSecret = null;

        if ($subscription->incomplete()) {
            $stripeSubscription = $subscription->asStripeSubscription(['latest_invoice.confirmation_secret']);

            $clientSecret = $stripeSubscription->latest_invoice->confirmation_secret->client_secret ?? null;
        }

        return ServiceResult::success([
            'subscription' => $subscription,
            'client_secret' => $clientSecret,
        ]);
    }
}
