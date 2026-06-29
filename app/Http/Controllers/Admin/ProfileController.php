<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show the authenticated admin user.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new ProfileResource($request->user()),
        ]);
    }
}
