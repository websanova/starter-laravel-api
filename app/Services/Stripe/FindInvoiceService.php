<?php

namespace App\Services\Stripe;

use App\Contracts\FindInvoiceProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Exceptions\InvalidInvoice;
use Stripe\Exception\ApiErrorException;

class FindInvoiceService implements FindInvoiceProvider
{
    /**
     * Cashier refuses to wrap an invoice whose customer is not the user, which
     * is the ownership check. That and an id Stripe has never heard of answer
     * the same way, so an id belonging to someone else cannot be told apart
     * from one that does not exist.
     */
    public function handle(User $user, string $invoiceId): ServiceResult
    {
        if (!$user->hasStripeId()) {
            return ServiceResult::error('not_found');
        }

        try {
            $invoice = $user->findInvoice($invoiceId);
        } catch (InvalidInvoice) {
            return ServiceResult::error('not_found');
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        if (is_null($invoice)) {
            return ServiceResult::error('not_found');
        }

        return ServiceResult::success($invoice);
    }
}
