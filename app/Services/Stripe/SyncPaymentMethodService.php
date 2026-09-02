<?php

namespace App\Services\Stripe;

use App\Contracts\SyncPaymentMethodProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class SyncPaymentMethodService implements SyncPaymentMethodProvider
{
    public function __construct(private ReplacePaymentMethodService $paymentMethods)
    {
    }

    /**
     * Put a card the client just confirmed in place, without waiting on the
     * webhook. Confirming the setup intent only attaches the card, so until
     * this runs it sits on the customer and nothing bills it.
     *
     * The client hands over the intent it confirmed rather than this hunting
     * for it, because the webhook already covers every path where the client
     * cannot report back, and guessing which of the customer's intents was
     * meant is only needed once that is no longer true.
     */
    public function handle(User $user, string $setupIntentId): ServiceResult
    {
        if (!$user->hasStripeId()) {
            return ServiceResult::error('nothing_to_sync');
        }

        try {
            $setupIntent = Cashier::stripe()->setupIntents->retrieve($setupIntentId);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        /**
         * The id came off the request, so it is only trustworthy once it is
         * shown to belong to this customer. An intent that does not is treated
         * as nothing to sync rather than named, since the caller has no
         * business knowing whether it exists.
         */
        if ($setupIntent->customer !== $user->stripe_id) {
            return ServiceResult::error('nothing_to_sync');
        }

        if ($setupIntent->status !== 'succeeded' || !$setupIntent->payment_method) {
            return ServiceResult::error('nothing_to_sync');
        }

        try {
            $this->paymentMethods->handle($user, $setupIntent->payment_method);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        return ServiceResult::success();
    }
}
