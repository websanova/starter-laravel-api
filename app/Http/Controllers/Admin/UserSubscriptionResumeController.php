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
        $result = $resumeSubscription->handle($user);

        if (!$result->success) {
            return $this->error($result, 'subscription');
        }

        return response()->json([
            'data' => new SubscriptionResource($result->data['subscription']),
            'message' => __('responses.admin.user.subscription_resumed'),
        ]);
    }
}
