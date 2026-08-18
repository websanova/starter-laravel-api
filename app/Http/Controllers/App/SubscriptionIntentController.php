<?php

namespace App\Http\Controllers\App;

use App\Contracts\CreateSubscriptionIntentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionIntent\StoreRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionIntentController extends Controller
{
    /**
     * Open a payment session and return the secret the client mounts its own
     * payment form against, along with which kind of intent it belongs to so
     * the client knows whether to confirm a payment or a setup. Safe to call
     * again for the same attempt, so a page refresh gets the same secret back
     * rather than a second subscription.
     */
    public function store(StoreRequest $request, CreateSubscriptionIntentProvider $subscriptions): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $result = $subscriptions->handle(
            $request->user(),
            $plan,
            $request->validated('interval'),
        );

        if (!$result->success) {
            $response = ['message' => __("responses.subscription.{$result->error}")];

            if (config('app.debug') && isset($result->data['debug'])) {
                $response['debug'] = $result->data['debug'];
            }

            return response()->json($response, 409);
        }

        return response()->json(['data' => $result->data]);
    }
}
