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
        return response()->json([
            'data' => PlanResource::collection(Plan::listable()),
        ]);
    }
}

