<?php

namespace App\Services\Stripe;

use App\Contracts\SyncPaymentMethodProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Invoice as StripeInvoice;

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

        return ServiceResult::success($this->settleOpenInvoice($user));
    }

    /**
     * Charge the invoice a failed renewal left open, now that there is a new
     * card to charge it to. Stripe does not retry on a card change of its own,
     * it waits for its next scheduled attempt, which is days out on a past due
     * subscription and never comes at all once that schedule is exhausted. The
     * user is here and the card is in place, so this is the moment to settle it.
     *
     * Nothing here can fail the sync. The card is already on file whatever the
     * invoice does, and an invoice that does not settle is left to Stripe's own
     * retries exactly as it was before.
     */
    protected function settleOpenInvoice(User $user): ?array
    {
        $subscription = $user->subscription();

        if (!$subscription) {
            return null;
        }

        try {
            $stripeSubscription = Cashier::stripe()->subscriptions->retrieve($subscription->stripe_id, [
                'expand' => ['latest_invoice'],
            ]);
        } catch (ApiErrorException) {
            return null;
        }

        $invoice = $stripeSubscription->latest_invoice;

        if (!$invoice || $invoice->status !== StripeInvoice::STATUS_OPEN) {
            return null;
        }

        try {
            Cashier::stripe()->invoices->pay($invoice->id);
        } catch (CardException $e) {
            return $this->refused($invoice->id, $e);
        } catch (ApiErrorException $e) {
            return ['status' => 'declined', 'debug' => [$e->getMessage()]];
        }

        return ['status' => 'paid'];
    }

    /**
     * Tell a card the bank refused apart from one it wants the user to
     * authenticate. The second is worth carrying back, since the user is on the
     * page and can clear the challenge there, while an off session retry days
     * later has nobody to answer it and fails the same way every time.
     */
    protected function refused(string $invoiceId, CardException $e): array
    {
        $declined = ['status' => 'declined', 'debug' => [$e->getMessage()]];

        if ($e->getStripeCode() !== 'authentication_required') {
            return $declined;
        }

        try {
            $invoice = Cashier::stripe()->invoices->retrieve($invoiceId, [
                'expand' => ['confirmation_secret'],
            ]);
        } catch (ApiErrorException) {
            return $declined;
        }

        $clientSecret = $invoice->confirmation_secret->client_secret ?? null;

        return $clientSecret
            ? ['status' => 'requires_action', 'client_secret' => $clientSecret]
            : $declined;
    }
}
