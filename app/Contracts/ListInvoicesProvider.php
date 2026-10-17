<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface ListInvoicesProvider
{
    /**
     * Fetch the user's most recent invoices live from the provider, newest
     * first, and hand back one page of them as a length aware paginator. The
     * provider only pages by cursor, so the paging is done on what it returns.
     */
    public function handle(User $user, int $perPage): ServiceResult;
}
