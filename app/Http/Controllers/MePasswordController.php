<?php

namespace App\Http\Controllers;

use App\Http\Requests\MePassword\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class MePasswordController extends Controller
{
    /**
     * Update the authenticated user's password.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
            'is_password_reset_required' => false,
        ]);

        return response()->json(['message' => __('responses.password.updated')]);
    }
}
