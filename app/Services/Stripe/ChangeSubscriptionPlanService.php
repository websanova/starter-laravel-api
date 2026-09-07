<?php

namespace App\Services\Stripe;

use App\Contracts\ChangeSubscriptionPlanProvider;
use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Subscription;
use Stripe\Exception\ApiErrorException;

class ChangeSubscriptionPlanService implements ChangeSubscriptionPlanProvider
{
    /**
     * Swap to a different plan or interval. The existing subscription carries
     * on with a different price against it, so nothing is cancelled and nothing
     * is created, and the difference is prorated and invoiced on the spot.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): ServiceResult
    {
        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        if (!$subscription) {
            return ServiceResult::error('nothing_to_update');
        }

        if ($subscription->canceled()) {
            return ServiceResult::error('pending_cancellation');
        }

        $status = match ($subscription->stripe_status) {
            'active' => null,
            'trialing' => 'on_trial',
            'past_due' => 'past_due',
            'unpaid' => 'unpaid',
            default => 'not_active',
        };

        if ($status) {
            return ServiceResult::error($status);
        }

        $priceId = $plan->priceId($interval);

        if (!$priceId) {
            return ServiceResult::error('plan_unavailable');
        }

        /**
         * The price already on the subscription is not a refusal. The request
         * is satisfied, so the current state goes back and Stripe is left
         * alone. It goes ahead of the payment method check so a subscription
         * that is not about to be billed is never refused over a card.
         */
        if ($subscription->stripe_price === $priceId) {
            return ServiceResult::success(['subscription' => $subscription, 'payment' => null]);
        }

        try {
            /**
             * The subscription default comes first and the customer is the
             * fallback, which is the order the charge itself reads. Not the
             * pm_type column, which is display and lags the webhook.
             */
            if (!$subscription->asStripeSubscription()->default_payment_method && !$user->defaultPaymentMethod()) {
                return ServiceResult::error('payment_method_missing');
            }

            $subscription->swapAndInvoice($priceId);
        } catch (IncompletePayment $e) {
            $user->fillPlan()->save();

            return ServiceResult::success([
                'subscription' => $subscription,
                'payment' => $this->payment($e, $subscription),
            ]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        $user->fillPlan()->save();

        return ServiceResult::success(['subscription' => $subscription, 'payment' => null]);
    }

    /**
     * Describe the proration invoice the swap could not settle. Stripe applies
     * the price and raises the invoice as two separate things, so the plan has
     * changed by the time either of these comes back. A card the bank wants
     * authenticated hands over the secret to challenge against, and a declined
     * one hands over the invoice for the user to settle.
     */
    protected function payment(IncompletePayment $e, Subscription $subscription): array
    {
        if ($e->payment->requiresPaymentMethod()) {
            return [
                'type' => 'invoice',
                'status' => 'failed',
                'invoice_id' => $subscription->latestInvoice()?->id,
            ];
        }

        return [
            'type' => 'invoice',
            'status' => 'requires_action',
            'client_secret' => $e->payment->clientSecret(),
        ];
    }
}
