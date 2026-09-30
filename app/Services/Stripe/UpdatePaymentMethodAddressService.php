<?php

namespace App\Services\Stripe;

use App\Contracts\UpdatePaymentMethodAddressProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class UpdatePaymentMethodAddressService implements UpdatePaymentMethodAddressProvider
{
    /**
     * Commit the address the card is collected against. It goes to the customer
     * and nowhere else, since that is where every renewal invoice computes tax
     * from and where the user reads it back off an invoice.
     *
     * This runs before a setup intent is ever issued, which is what closes the
     * hole the other order leaves. A bank challenge can send the browser away
     * and never bring it back, and an address that only exists in the browser at
     * that point is gone, leaving a card on file against an address nobody
     * updated. Writing it first also means the provider's rejection lands while
     * the user is still looking at the form that has the fields.
     *
     * The customer is created here when there isn't one, since this is the first
     * call the payment method page makes and a user who has never subscribed
     * reaches it.
     *
     * The address keys are the provider's own, so they pass straight through.
     */
    public function handle(User $user, array $address): ServiceResult
    {
        try {
            $user->createOrGetStripeCustomer();
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        /**
         * The name is a sibling of the address on the customer rather than a
         * field inside it, so it comes out before the rest is passed through.
         */
        $name = $address['name'] ?? '';

        /**
         * Every key goes on every save, empty where the user cleared it, since
         * the provider only touches what it is sent and an absent key leaves the
         * old value in place. An empty string is what clears one.
         */
        $address = [
            'city' => $address['city'] ?? '',
            'country' => $address['country'] ?? '',
            'line1' => $address['line1'] ?? '',
            'line2' => $address['line2'] ?? '',
            'postal_code' => $address['postal_code'] ?? '',
            'state' => $address['state'] ?? '',
        ];

        try {
            $user->updateStripeCustomer([
                'name' => $name,
                'address' => $address,
                'tax' => ['validate_location' => 'immediately'],
            ]);
        } catch (InvalidRequestException $e) {
            /**
             * The provider could not place the address against a tax
             * jurisdiction. The customer is left unchanged and retrying the same
             * address fails the same way, so this is the user's to correct rather
             * than something to try again as-is.
             */
            return ServiceResult::error('address_invalid', ['debug' => [$e->getMessage()]]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        return ServiceResult::success();
    }
}
