<?php

namespace App\Services\Stripe;

use App\Contracts\CancelSubscriptionProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Illuminate\Support\Carbon;
use Stripe\Exception\ApiErrorException;

class CancelSubscriptionService implements CancelSubscriptionProvider
{
    /**
     * Cancel the subscription at period end. Nothing is charged and nothing is
     * refunded, the paid term simply runs out.
     */
    public function handle(User $user): ServiceResult
    {
        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        if (!$subscription) {
            return ServiceResult::error('nothing_to_cancel');
        }

        /**
         * Already cancelled is not a refusal. The request is satisfied, so the
         * current state goes back and Stripe is left alone. A double submit, a
         * second tab and a direct call all land here.
         */
        if ($subscription->canceled()) {
            return ServiceResult::success(['subscription' => $subscription]);
        }

        try {
            $stripeSubscription = $subscription->updateStripeSubscription([
                'cancel_at_period_end' => true,
            ]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        /**
         * Both fields come off the update response. Cashier's own cancel()
         * reads the end date back out of the subscription items instead, which
         * is a second Stripe call per item and a lazy load of the relation, all
         * for a date the response already carries as cancel_at. A trial needs
         * no special casing either, since Stripe puts cancel_at on the trial
         * end when that is where the term stops.
         */
        $subscription->fill([
            'stripe_status' => $stripeSubscription->status,
            'ends_at' => Carbon::createFromTimestamp($stripeSubscription->cancel_at),
        ])->save();

        return ServiceResult::success(['subscription' => $subscription]);
    }
}
