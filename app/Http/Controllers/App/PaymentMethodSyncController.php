<?php

namespace App\Http\Controllers\App;

use App\Contracts\SyncPaymentMethodProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\PaymentMethodSync\StoreRequest;
use Illuminate\Http\JsonResponse;

class PaymentMethodSyncController extends Controller
{
    /**
     * Pick up a card the client confirmed but never reported back. The client
     * calls this straight after confirming so the change lands in the request
     * rather than waiting on the webhook.
     *
     * The user is reloaded before the card is read back, since a webhook that
     * landed first wrote the columns against its own copy and left the one held
     * here a card behind.
     */
    public function store(StoreRequest $request, SyncPaymentMethodProvider $paymentMethods): JsonResponse
    {
        $user = $request->user();

        $result = $paymentMethods->handle($user, $request->validated('setup_intent'));

        if (!$result->success) {
            return $this->error($result, 'payment_method');
        }

        $response = [
            'data' => $user->refresh()->payment_method,
            'message' => __('responses.payment_method.updated'),
        ];

        if ($result->data) {
            $response['invoice'] = $this->invoice($result->data);
        }

        return response()->json($response);
    }

    /**
     * Shape the attempt made on an invoice the failed renewal left open. Only
     * present when there was one to charge, so a user who is not in dunning
     * sees the same response as before.
     *
     * A challenge carries the secret the client confirms against, since the
     * user is on the page and can clear it there. A refusal carries a message
     * to show, with the provider's own text held back for debug like every
     * other failure the client is handed.
     */
    protected function invoice(array $invoice): array
    {
        $payload = ['status' => $invoice['status']];

        if ($invoice['status'] === 'declined') {
            $payload['message'] = __('responses.payment_method.invoice_declined');
        }

        if (isset($invoice['client_secret'])) {
            $payload['client_secret'] = $invoice['client_secret'];
        }

        if (config('app.debug') && isset($invoice['debug'])) {
            $payload['debug'] = $invoice['debug'];
        }

        return $payload;
    }
}
