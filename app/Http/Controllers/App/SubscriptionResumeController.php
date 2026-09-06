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
    public function store(StoreRequest $request, ResumeSubscriptionProvider $resumeSubscription): JsonResponse
    {
        $result = $resumeSubscription->handle($request->user());

        if (!$result->success) {
            return $this->error($result, 'subscription');
        }

        return response()->json([
            'data' => new SubscriptionResource($result->data),
            'message' => __('responses.subscription.resumed'),
        ]);
    }
}
