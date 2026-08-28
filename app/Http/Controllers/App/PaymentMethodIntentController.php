<?php

namespace App\Http\Controllers\App;

use App\Contracts\CreatePaymentMethodIntentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\PaymentMethodIntent\StoreRequest;
use Illuminate\Http\JsonResponse;

class PaymentMethodIntentController extends Controller
{
    /**
     * Open a session for collecting a card and return the secret the client
     * mounts its own payment form against, along with the kind of intent it
     * belongs to so the response matches what the subscription intent returns.
     */
    public function store(StoreRequest $request, CreatePaymentMethodIntentProvider $paymentMethods): JsonResponse
    {
        $result = $paymentMethods->handle($request->user());

        if (!$result->success) {
            return $this->error($result, 'payment_method');
        }

        return response()->json(['data' => $result->data]);
    }
}
