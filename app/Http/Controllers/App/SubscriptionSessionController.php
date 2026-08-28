<?php

namespace App\Http\Controllers\App;

use App\Contracts\CreateSessionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionSession\StoreRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionSessionController extends Controller
{
    /**
     * Open the checkout session the client subscribes through. The address, the
     * card and the promotion code are all collected inside that session, so
     * none of them are sent here and nothing is created until the user
     * confirms. The session is the whole response, there is no subscription to
     * hand back yet.
     */
    public function store(StoreRequest $request, CreateSessionProvider $sessions): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $result = $sessions->handle(
            $request->user(),
            $plan,
            $request->validated('interval'),
        );

        if (!$result->success) {
            return $this->error($result, 'subscription');
        }

        return response()->json(['data' => $result->data]);
    }
}
