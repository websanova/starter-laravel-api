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
     * An addition as much as a replacement, since a user who has never
     * subscribed reaches this page, and it is also the way back for a cancelled
     * user who removed theirs.
     *
     * The address has to be on the customer before a secret is handed out, which
     * is what makes it impossible to store a card against a customer with no
     * address. No customer means no address either, so both answer the same way
     * and the client goes back to the address step. Nothing is created here, the
     * address call ahead of this one is what makes the customer.
     */
    public function handle(User $user): ServiceResult
    {
        if (!$user->hasStripeId()) {
            return ServiceResult::error('address_required');
        }

        try {
            if (!$user->asStripeCustomer()->address) {
                return ServiceResult::error('address_required');
            }

            /**
             * The card is collected now and billed later on renewals, with no
             * one at the keyboard to authenticate, so Stripe is told up front
             * what it is for and asks the bank for the mandate while the user
             * is still here.
             */
            $setupIntent = $user->createSetupIntent([
                'usage' => 'off_session',
                'payment_method_types' => ['card'],
            ]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        return ServiceResult::success(['client_secret' => $setupIntent->client_secret, 'type' => 'setup']);
    }
}
