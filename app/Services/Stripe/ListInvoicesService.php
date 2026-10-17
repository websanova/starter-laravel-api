<?php

namespace App\Services\Stripe;

use App\Contracts\ListInvoicesProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Stripe\Exception\ApiErrorException;

class ListInvoicesService implements ListInvoicesProvider
{
    /**
     * Stripe only pages by cursor, with no page numbers and no total. One call
     * pulls the latest invoices and the paging happens here, which keeps the
     * response in the usual page and total shape. Thirty is two and a half years
     * of monthly invoices, anything older is out of reach from this endpoint.
     *
     * Nothing is stored locally, so every request is a live call to Stripe. A
     * user with no customer yet has no invoices and gets an empty page back
     * without a call being made.
     */
    private const LIMIT = 30;

    public function handle(User $user, int $perPage): ServiceResult
    {
        try {
            $invoices = $user->invoices(true, ['limit' => self::LIMIT]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        $page = Paginator::resolveCurrentPage();

        return ServiceResult::success(new LengthAwarePaginator(
            $invoices->forPage($page, $perPage)->values(),
            $invoices->count(),
            $perPage,
            $page,
        ));
    }
}
