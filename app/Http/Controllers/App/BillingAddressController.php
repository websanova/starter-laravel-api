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
     * Replace the user's billing address. Required before subscribing when
     * automatic tax is on, since nothing in the payment flow collects one and
     * the provider needs somewhere to calculate from.
     */
    public function update(UpdateRequest $request, UpdateBillingAddressProvider $subscriptions): JsonResponse
    {
        $user = $request->user();

        $result = $subscriptions->handle($user, $request->validated());

        if (!$result->success) {
            $response = ['message' => __("responses.subscription.{$result->error}")];

            if (config('app.debug') && isset($result->data['debug'])) {
                $response['debug'] = $result->data['debug'];
            }

            return response()->json($response, 409);
        }

        return response()->json([
            'data' => new BillingAddressResource($user),
            'message' => __('responses.billing.address_updated'),
        ]);
    }
}
