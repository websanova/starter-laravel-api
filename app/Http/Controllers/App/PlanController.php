<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    /**
     * List active, public plans.
     */
    public function index(): JsonResponse
    {
        $plans = Plan::cached()
            ->where('is_active', true)
            ->where('is_public', true)
            ->values();

        return response()->json([
            'data' => PlanResource::collection($plans),
        ]);
    }
}

