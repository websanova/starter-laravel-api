<?php

namespace App\Http\Controllers\App;

use App\Contracts\UpdateBillingAddressProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\BillingAddress\UpdateRequest;
use App\Http\Resources\App\BillingAddressResource;
use Illuminate\Http\JsonResponse;

class BillingAddressController extends Controller
{
    /**
     * Replace the user's billing address. Required before subscribing, tax on
     * or off, since nothing in the payment flow collects one and the provider
     * needs somewhere to calculate from.
     */
    public function update(UpdateRequest $request, UpdateBillingAddressProvider $updateBillingAddress): JsonResponse
    {
        $user = $request->user();

        $result = $updateBillingAddress->handle($user, $request->validated());

        if (!$result->success) {
            return $this->error($result, 'billing');
        }

        return response()->json([
            'data' => new BillingAddressResource($user),
            'message' => __('responses.billing.address_updated'),
        ]);
    }
}
