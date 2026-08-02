<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plan\IndexRequest;
use App\Http\Requests\Admin\Plan\ShowRequest;
use App\Http\Requests\Admin\Plan\UpdateRequest;
use App\Http\Resources\Admin\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    /**
     * List plans with optional filtering.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $plans = Plan::query()
            ->with('prices')
            ->forActive($request->validated('active'))
            ->sortBy($request->validated('sort_by'), $request->validated('sort_dir'))
            ->paginate($request->validated('per_page', 15));

        return response()->json(PlanResource::paginated($plans));
    }

    /**
     * Show a single plan.
     */
    public function show(ShowRequest $request, Plan $plan): JsonResponse
    {
        return response()->json([
            'data' => new PlanResource($plan),
        ]);
    }

    /**
     * Update a plan.
     */
    public function update(UpdateRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validated());

        return response()->json([
            'data' => new PlanResource($plan),
            'message' => __('responses.admin.plan.updated'),
        ]);
    }
}
