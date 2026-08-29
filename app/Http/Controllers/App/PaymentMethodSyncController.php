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

        return response()->json([
            'data' => $user->refresh()->payment_method,
            'message' => __('responses.payment_method.updated'),
        ]);
    }
}
