<?php

namespace App\Http\Controllers\App;

use App\Contracts\CancelSubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionCancel\StoreRequest;
use App\Http\Resources\App\SubscriptionResource;
use Illuminate\Http\JsonResponse;

class SubscriptionCancelController extends Controller
{
    /**
     * Cancel the subscription at period end.
     */
    public function store(StoreRequest $request, CancelSubscriptionProvider $subscriptions): JsonResponse
    {
        $result = $subscriptions->handle($request->user());

        if (!$result->success) {
            return $this->error($result, 'subscription');
        }

        return response()->json([
            'data' => new SubscriptionResource($result->data),
            'message' => __('responses.subscription.cancelled'),
        ]);
    }
}
