<?php

namespace App\Services\Stripe;

use App\Contracts\UpdateBillingAddressProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Stripe\Exception\ApiErrorException;

class UpdateBillingAddressService implements UpdateBillingAddressProvider
{
    /**
     * Commit a billing address, pushing it to Stripe before storing it. The
     * provider write goes first because a local address Stripe does not know
     * about is worse than no address at all, it would let a subscribe through
     * that the provider then rejects for having no tax location. No customer
     * id means the user never subscribed, so there is nothing to push to and
     * the write is local only.
     *
     * The address keys are Stripe's own, so they pass straight through and are
     * only renamed on the way into the users table.
     */
    public function handle(User $user, array $address): ServiceResult
    {
        $address = array_filter($address);

        if ($address == $user->billingAddress()) {
            return ServiceResult::success();
        }

        /**
         * The first invoice is finalized the moment the subscription is
         * created and never recalculates its tax, so an attempt still in
         * flight would keep charging the old jurisdiction. Killing it makes
         * the next CreateSubscriptionIntentService build a fresh one rather than hand
         * back a secret for the stale invoice. Only the two fields a tax
         * location resolves from count, since anything else leaves the amount
         * untouched and tearing up a live payment session over a corrected
         * street name costs the user their progress for nothing.
         */
        $movedTaxLocation = $address['country'] !== $user->billing_country
            || $address['postal_code'] !== $user->billing_postal_code;

        try {
            if ($user->hasStripeId()) {
                $user->updateStripeCustomer([
                    'address' => $address,
                    'tax' => ['validate_location' => 'immediately'],
                ]);
            }

            $subscription = $user->subscription();

            if ($movedTaxLocation && $subscription && $subscription->incomplete()) {
                $subscription->cancelNow();
            }
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        $user->update([
            'billing_city' => $address['city'] ?? null,
            'billing_country' => $address['country'] ?? null,
            'billing_line1' => $address['line1'] ?? null,
            'billing_line2' => $address['line2'] ?? null,
            'billing_postal_code' => $address['postal_code'] ?? null,
        ]);

        return ServiceResult::success();
    }
}
