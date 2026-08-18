<?php

namespace App\Services\Stripe;

use App\Contracts\SyncPaymentMethodProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class SyncPaymentMethodService implements SyncPaymentMethodProvider
{
    /**
     * Pick up a card the client confirmed but never reported back, whether the
     * call after the confirm was lost or the webhook never landed. Nothing the
     * client holds is needed, the confirm already attached the card to the
     * customer, so the setup intent it came from is asked for instead.
     */
    public function handle(User $user): ServiceResult
    {
        if (!$user->hasStripeId()) {
            return ServiceResult::error('nothing_to_sync');
        }

        try {
            /**
             * Stripe lists newest first, so the first succeeded one is the card
             * the user just entered. The limit covers the intents abandoned
             * before confirming that can sit ahead of it.
             */
            $setupIntents = Cashier::stripe()->setupIntents->all([
                'customer' => $user->stripe_id,
                'limit' => 10,
            ]);

            $setupIntent = collect($setupIntents->data)->firstWhere('status', 'succeeded');

            if (!$setupIntent || !$setupIntent->payment_method) {
                return ServiceResult::error('nothing_to_sync');
            }

            /**
             * Stripe bills a subscription off its own default and only falls
             * back to the customer when it has none, so this is the write that
             * actually moves the next renewal onto the new card. It goes first
             * because a failure part way through is better left billing the new
             * card against a stale display than the reverse.
             */
            $user->loadMissing('subscriptions');

            $subscription = $user->subscription();

            if ($subscription) {
                $subscription->updateStripeSubscription(['default_payment_method' => $setupIntent->payment_method]);
            }

            $user->updateDefaultPaymentMethod($setupIntent->payment_method);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        return ServiceResult::success();
    }
}
