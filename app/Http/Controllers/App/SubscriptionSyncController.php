<?php

namespace App\Http\Controllers\App;

use App\Contracts\SyncSubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionSync\StoreRequest;
use App\Http\Resources\App\SubscriptionResource;
use Illuminate\Http\JsonResponse;

class SubscriptionSyncController extends Controller
{
    /**
     * Pick up a status the webhook has not landed yet. The client calls this
     * once a challenge on the first invoice clears, since the subscription was
     * written before the challenge ran and still reads incomplete.
     */
    public function store(StoreRequest $request, SyncSubscriptionProvider $subscriptions): JsonResponse
    {
        $user = $request->user();

        $subscriptions->handle($user);

        $subscription = $user->subscription();

        if (!$subscription) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }
}
