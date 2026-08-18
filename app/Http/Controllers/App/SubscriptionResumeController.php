<?php

namespace App\Http\Controllers\App;

use App\Contracts\ResumeSubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionResume\StoreRequest;
use App\Http\Resources\App\SubscriptionResource;
use Illuminate\Http\JsonResponse;

class SubscriptionResumeController extends Controller
{
    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function store(StoreRequest $request, ResumeSubscriptionProvider $subscriptions): JsonResponse
    {
        $subscription = $subscriptions->handle($request->user());

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.subscription.resumed'),
        ]);
    }
}
