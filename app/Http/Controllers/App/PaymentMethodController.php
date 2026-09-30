<?php

namespace App\Http\Controllers\App;

use App\Contracts\DeletePaymentMethodProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\PaymentMethod\DestroyRequest;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    /**
     * Remove the card on file. Adding one lives on the payment method page,
     * which takes a first card as readily as a replacement.
     *
     * Nothing settles after the response. No setup intent, no confirm and no
     * bank challenge, so unlike the update there is nothing for the client to
     * poll afterwards.
     */
    public function destroy(DestroyRequest $request, DeletePaymentMethodProvider $deletePaymentMethod): JsonResponse
    {
        $result = $deletePaymentMethod->handle($request->user());

        if (!$result->success) {
            return $this->error($result, 'payment_method');
        }

        return response()->json(null, 204);
    }
}
