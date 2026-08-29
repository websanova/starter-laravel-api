<?php

namespace App\Services\Stripe;

use App\Contracts\UpdateBillingAddressProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class UpdateBillingAddressService implements UpdateBillingAddressProvider
{
    /**
     * Commit a billing address, pushing it to Stripe before storing it. The
     * provider write goes first because a local address Stripe does not know
     * about is worse than no address at all, it would let a subscribe through
     * that the provider then rejects for having no tax location. The customer
     * is created when there isn't one, since the setup intent the subscribe
     * flow mounts against has nothing to hang off otherwise.
     *
     * Pushed on every save rather than compared first. A customer created a
     * moment ago carries no address, so an unchanged address does not imply an
     * address Stripe has already accepted, and reading the customer back to
     * tell the difference costs the same call as writing it.
     *
     * The address keys are Stripe's own, so they pass straight through and are
     * only renamed on the way into the users table.
     */
    public function handle(User $user, array $address): ServiceResult
    {
        /**
         * The name is a sibling of the address on the customer rather than a
         * field inside it, so it comes out before the rest is passed through.
         */
        $name = $address['name'] ?? null;

        unset($address['name']);

        $address = array_filter($address);

        try {
            $user->updateOrCreateStripeCustomer([
                'name' => $name,
                'address' => $address,
                'tax' => ['validate_location' => 'immediately'],
            ]);
        } catch (InvalidRequestException $e) {
            /**
             * Stripe could not place the address against a tax jurisdiction.
             * The customer is left unchanged and retrying the same address
             * fails the same way, so this is the user's to correct rather than
             * something to try again as-is.
             */
            return ServiceResult::error('address_invalid', ['debug' => [$e->getMessage()]]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        $user->update([
            'billing_city' => $address['city'] ?? null,
            'billing_country' => $address['country'] ?? null,
            'billing_line1' => $address['line1'] ?? null,
            'billing_line2' => $address['line2'] ?? null,
            'billing_name' => $name,
            'billing_postal_code' => $address['postal_code'] ?? null,
            'billing_state' => $address['state'] ?? null,
        ]);

        return ServiceResult::success();
    }
}
