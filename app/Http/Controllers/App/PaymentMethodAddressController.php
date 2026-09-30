<?php

namespace App\Http\Controllers\App;

use App\Contracts\UpdatePaymentMethodAddressProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\PaymentMethodAddress\UpdateRequest;
use Illuminate\Http\JsonResponse;

class PaymentMethodAddressController extends Controller
{
    /**
     * Write the address the card is collected against. The client calls this on
     * the way out of the address step, before it asks for a setup intent, so a
     * card can never be stored against a customer with no address.
     */
    public function update(UpdateRequest $request, UpdatePaymentMethodAddressProvider $updatePaymentMethodAddress): JsonResponse
    {
        $result = $updatePaymentMethodAddress->handle($request->user(), $request->validated());

        if (!$result->success) {
            return $this->error($result, 'payment_method');
        }

        return response()->json([
            'message' => __('responses.payment_method.address_updated'),
        ]);
    }
}
