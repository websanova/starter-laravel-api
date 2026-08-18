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
     */
    public function store(StoreRequest $request, SyncPaymentMethodProvider $paymentMethods): JsonResponse
    {
        $result = $paymentMethods->handle($request->user());

        if (!$result->success) {
            $response = ['message' => __("responses.payment_method.{$result->error}")];

            if (config('app.debug') && isset($result->data['debug'])) {
                $response['debug'] = $result->data['debug'];
            }

            return response()->json($response, 409);
        }

        return response()->json(['message' => __('responses.payment_method.updated')]);
    }
}
