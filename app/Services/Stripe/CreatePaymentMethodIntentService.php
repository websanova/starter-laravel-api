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
     * element mounts against. This replaces the card already on file, so the
     * customer is a precondition rather than something made here. Nothing is
     * charged, so the client always confirms this as a setup.
     */
    public function handle(User $user): ServiceResult
    {
        /**
         * The setup intent hangs off the customer, and Cashier throws outright
         * when there is none, so it is caught here instead and named for the
         * client.
         */
        if (!$user->hasStripeId()) {
            return ServiceResult::error('customer_required');
        }

        try {
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
