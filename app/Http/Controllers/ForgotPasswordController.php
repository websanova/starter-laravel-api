<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPassword\StoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Send a password reset link to the given email.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => __('responses.passwords.sent_if_exists')]);
    }
}
