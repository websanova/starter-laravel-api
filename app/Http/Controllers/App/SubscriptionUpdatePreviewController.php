<?php

namespace App\Http\Controllers\App;

use App\Contracts\PreviewSubscriptionUpdateProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionUpdatePreview\ShowRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionUpdatePreviewController extends Controller
{
    /**
     * Quote what a plan or interval change costs before the user commits.
     */
    public function show(ShowRequest $request, PreviewSubscriptionUpdateProvider $previewSubscriptionUpdate): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $result = $previewSubscriptionUpdate->handle(
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
