<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface FindInvoiceProvider
{
    /**
     * Find one of the user's own invoices at the provider. An invoice that
     * belongs to someone else is reported as missing rather than forbidden.
     */
    public function handle(User $user, string $invoiceId): ServiceResult;
}
