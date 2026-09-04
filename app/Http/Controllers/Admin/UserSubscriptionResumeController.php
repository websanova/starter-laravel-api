<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\ResumeSubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserSubscriptionResume\StoreRequest;
use App\Http\Resources\Admin\SubscriptionResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserSubscriptionResumeController extends Controller
{
    /**
     * Resume a user's cancelled subscription.
     */
    public function store(StoreRequest $request, User $user, ResumeSubscriptionProvider $resumeSubscription): JsonResponse
    {
        $subscription = $resumeSubscription->handle($user);

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.admin.user.subscription_resumed'),
        ]);
    }
}
