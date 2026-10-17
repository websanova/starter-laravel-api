<?php

namespace App\Http\Controllers\App;

use App\Contracts\FindInvoiceProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\InvoiceDownload\ShowRequest;
use Symfony\Component\HttpFoundation\Response;

class InvoiceDownloadController extends Controller
{
    /**
     * Stream one of the user's invoices as a PDF. The client has to fetch this
     * with its token and save the response, a plain link will not carry it.
     */
    public function show(ShowRequest $request, string $invoice, FindInvoiceProvider $findInvoice): Response
    {
        $result = $findInvoice->handle($request->user(), $invoice);

        if (!$result->success) {
            return $this->error($result, 'invoice', $result->error === 'not_found' ? 404 : 409);
        }

        return $result->data->download();
    }
}
