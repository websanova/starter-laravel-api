<?php

namespace App\Http\Controllers\App;

use App\Contracts\SubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionSync\StoreRequest;
use Illuminate\Http\JsonResponse;

class SubscriptionSyncController extends Controller
{
    /**
     * Pull the subscription state from Stripe after the client confirms a
     * payment. The webhook does the same work on its own schedule, so this
     * exists only to close the window where the client would otherwise refresh
     * against stale state.
     */
    public function store(StoreRequest $request, SubscriptionProvider $subscriptions): JsonResponse
    {
        $subscriptions->sync($request->user());

        return response()->json(null, 204);
    }
}
