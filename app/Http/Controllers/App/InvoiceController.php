<?php

namespace App\Http\Controllers\App;

use App\Contracts\ListInvoicesProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Invoice\IndexRequest;
use App\Http\Resources\App\InvoiceResource;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * List the authenticated user's recent invoices, pulled live from Stripe.
     */
    public function index(IndexRequest $request, ListInvoicesProvider $listInvoices): JsonResponse
    {
        $result = $listInvoices->handle($request->user(), $request->validated('per_page', 15));

        if (!$result->success) {
            return $this->error($result, 'invoice');
        }

        return response()->json(InvoiceResource::paginated($result->data));
    }
}
