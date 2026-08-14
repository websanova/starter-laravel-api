<?php

namespace App\Services\Stripe;

use App\Contracts\PaymentMethodProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class PaymentMethodService implements PaymentMethodProvider
{
    /**
     * Open a session for collecting a card and hand back the secret a payment
     * element mounts against. The setup intent hangs off the customer, so one
     * has to exist before Stripe will take it. Nothing is charged here, so the
     * client always confirms this as a setup.
     */
    public function intent(User $user): ServiceResult
    {
        try {
            $user->createOrGetStripeCustomer();

            /**
             * The card is collected now and billed later on renewals, with no
             * one at the keyboard to authenticate, so Stripe is told up front
             * what it is for and asks the bank for the mandate while the user
             * is still here.
             */
            $setupIntent = $user->createSetupIntent(['usage' => 'off_session']);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        return ServiceResult::success(['client_secret' => $setupIntent->client_secret, 'type' => 'setup']);
    }

    /**
     * Pick up a card the client confirmed but never reported back, whether the
     * call after the confirm was lost or the webhook never landed. Nothing the
     * client holds is needed, the confirm already attached the card to the
     * customer, so the setup intent it came from is asked for instead.
     */
    public function sync(User $user): ServiceResult
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
