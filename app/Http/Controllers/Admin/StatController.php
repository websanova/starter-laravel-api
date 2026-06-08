<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Stat\IndexRequest;
use App\Http\Resources\Admin\StatResource;
use App\Models\Stat;
use Illuminate\Http\JsonResponse;

class StatController extends Controller
{
    /**
     * List stats with optional group filtering.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $stats = Stat::query()
            ->forGroup($request->validated('group'))
            ->get();

        return response()->json([
            'data' => StatResource::collection($stats),
        ]);
    }
}
