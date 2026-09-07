<?php

namespace App\Services\Stripe;

use App\Contracts\PreviewSubscriptionUpdateProvider;
use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class PreviewSubscriptionUpdateService implements PreviewSubscriptionUpdateProvider
{
    /**
     * Quote what changing to the given plan and interval costs. Nothing is
     * created at Stripe, the invoice is only computed and handed back, so the
     * user sees the charge before committing to it.
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

        try {
            $stripeSubscription = $subscription->asStripeSubscription();

            if (!$stripeSubscription->default_payment_method && !$user->defaultPaymentMethod()) {
                return ServiceResult::error('payment_method_missing');
            }

            $payload = [
                'customer' => $user->stripe_id,
                'subscription' => $subscription->stripe_id,
                'subscription_details' => [
                    'items' => [[
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $priceId,
                    ]],
                    'proration_behavior' => 'always_invoice',
                ],
            ];

            /**
             * Two calls because they answer two different questions. The
             * default preview is the invoice raised the moment the change is
             * confirmed, prorated off today. The recurring preview is the same
             * subscription billed as a whole period, which is what the next
             * renewal costs once the proration is behind them.
             */
            $due = Cashier::stripe()->invoices->createPreview($payload);

            $recurring = Cashier::stripe()->invoices->createPreview(
                array_merge($payload, ['preview_mode' => 'recurring']),
            );
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        return ServiceResult::success([
            'amount_due' => $due->amount_due,
            'currency' => $due->currency,
            'recurring_total' => $recurring->total,
        ]);
    }
}
