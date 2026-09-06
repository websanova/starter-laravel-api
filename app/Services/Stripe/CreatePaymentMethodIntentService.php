<?php

namespace App\Services\Stripe;

use App\Contracts\CreatePaymentMethodIntentProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Stripe\Exception\ApiErrorException;

class CreatePaymentMethodIntentService implements CreatePaymentMethodIntentProvider
{
    /**
     * Open a session for collecting a card and hand back the secret a payment
     * element mounts against. Nothing is charged, so the client always confirms
     * this as a setup.
     *
     * The customer is created here when there isn't one. A user who never
     * subscribed reaches this page the same as everyone else, and a setup
     * intent has nothing to hang off without one.
     */
    public function handle(User $user): ServiceResult
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
}
