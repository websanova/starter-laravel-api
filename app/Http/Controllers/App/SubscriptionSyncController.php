<?php

namespace App\Http\Controllers\App;

use App\Contracts\SyncSubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionSync\StoreRequest;
use Illuminate\Http\JsonResponse;

class SubscriptionSyncController extends Controller
{
    /**
     * Write the local rows for a checkout session the client just completed.
     * Everything is already correct at Stripe by the time this runs, only the
     * local side is behind, so an error here is worth showing and worth
     * retrying. The webhook lands regardless.
     */
    public function store(StoreRequest $request, SyncSubscriptionProvider $syncSubscription): JsonResponse
    {
        $user = $request->user();

        $result = $syncSubscription->handle($user, $request->validated('session'));

        if (!$result->success) {
            return $this->error($result, 'subscription');
        }

        return response()->json([
            'message' => __('responses.subscription.synced'),
        ]);
    }
}
