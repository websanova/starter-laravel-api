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
     * Write the local rows for a checkout session the client just completed.
     * Everything is already correct at Stripe by the time this runs, only the
     * local side is behind, so an error here is worth showing and worth
     * retrying. The webhook lands regardless.
     */
    public function store(StoreRequest $request, SyncSubscriptionProvider $subscriptions): JsonResponse
    {
        $user = $request->user();

        $result = $subscriptions->handle($user, $request->validated('session'));

        if (!$result->success) {
            $response = ['message' => __("responses.subscription.{$result->error}")];

            if (config('app.debug') && isset($result->data['debug'])) {
                $response['debug'] = $result->data['debug'];
            }

            return response()->json($response, 409);
        }

        return response()->json([
            'data' => new SubscriptionResource($user->subscription()),
        ]);
    }
}
