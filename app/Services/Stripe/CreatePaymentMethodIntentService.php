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
     * Serves both the subscribe wizard and a straight card replacement. The
     * customer is a precondition either way, made when the address was saved
     * rather than here, so no customer means the address was never settled.
     */
    public function handle(User $user): ServiceResult
    {
        if (!$user->hasStripeId()) {
            return ServiceResult::error('address_required');
        }

        try {
            /**
             * Stripe stamps the customer with whether it can place them in a
             * tax jurisdiction, and it does that when the address is pushed. So
             * the state of the last address save is readable here for the cost
             * of a retrieve, without re-sending an address this step never
             * collected. Collecting a card against a customer Stripe cannot
             * place only defers the failure to the subscribe call, where the
             * remedy is the same and the user has further to walk back.
             */
            $customer = $user->asStripeCustomer(['tax']);

            $automaticTax = $customer->tax->automatic_tax ?? null;

            if (!in_array($automaticTax, ['supported', 'not_collecting'])) {
                return ServiceResult::error('tax_location_invalid');
            }

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
