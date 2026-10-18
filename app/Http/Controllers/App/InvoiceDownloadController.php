<?php

namespace App\Http\Controllers\App;

use App\Contracts\FindInvoiceProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\InvoiceDownload\ShowRequest;
use App\Http\Requests\App\InvoiceDownload\StoreRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class InvoiceDownloadController extends Controller
{
    /**
     * Hand back a short lived signed link for one of the user's invoices. The
     * download itself sits outside auth so a plain link can open it, which
     * leaves the signature as the only proof of who asked, hence the user id
     * riding inside it.
     */
    public function store(StoreRequest $request, string $invoice, FindInvoiceProvider $findInvoice): JsonResponse
    {
        $result = $findInvoice->handle($request->user(), $invoice);

        if (!$result->success) {
            return $this->error($result, 'invoice', $result->error === 'not_found' ? 404 : 409);
        }

        $url = URL::temporarySignedRoute('invoices.download', now()->addMinutes(5), [
            'invoice' => $invoice,
            'user' => $request->user()->id,
        ]);

        return response()->json(['data' => ['url' => $url]]);
    }

    /**
     * Stream the invoice as a PDF for a valid signed link. The user comes from
     * the signed query string and the invoice is still looked up through them,
     * so the ownership check holds.
     */
    public function show(ShowRequest $request, string $invoice, FindInvoiceProvider $findInvoice): Response
    {
        $user = User::findOrFail($request->query('user'));

        $result = $findInvoice->handle($user, $invoice);

        if (!$result->success) {
            return $this->error($result, 'invoice', $result->error === 'not_found' ? 404 : 409);
        }

        return $result->data->download();
    }
}
