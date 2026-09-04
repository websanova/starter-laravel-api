<?php

namespace App\Http\Controllers\App;

use App\Contracts\DeletePaymentMethodProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\PaymentMethod\DestroyRequest;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    /**
     * Remove the card on file. There is no add path here, since the first card
     * is collected during subscribe.
     *
     * Nothing settles after the response. No setup intent, no confirm and no
     * bank challenge, so unlike the update there is nothing for the client to
     * poll afterwards.
     */
    public function destroy(DestroyRequest $request, DeletePaymentMethodProvider $paymentMethods): JsonResponse
    {
        $result = $paymentMethods->handle($request->user());

        if (!$result->success) {
            return $this->error($result, 'payment_method');
        }

        return response()->json(null, 204);
    }
}
