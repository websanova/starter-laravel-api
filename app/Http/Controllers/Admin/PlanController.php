<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plan\DestroyRequest;
use App\Http\Requests\Admin\Plan\IndexRequest;
use App\Http\Requests\Admin\Plan\ShowRequest;
use App\Http\Requests\Admin\Plan\StoreRequest;
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
     * Create a new plan.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $plan = Plan::create($request->validated());

        return response()->json([
            'data' => new PlanResource($plan),
            'message' => __('responses.admin.plan.created'),
        ], 201);
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

    /**
     * Delete a plan.
     */
    public function destroy(DestroyRequest $request, Plan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json(null, 204);
    }
}
