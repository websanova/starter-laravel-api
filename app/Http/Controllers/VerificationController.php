<?php

namespace App\Http\Controllers;

use App\Http\Requests\Verification\ResendRequest;
use App\Http\Requests\Verification\VerifyRequest;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    public function __construct(
        protected VerificationService $verificationService
    ) {}

    /**
     * Verify the user's code.
     */
    public function verify(VerifyRequest $request): JsonResponse
    {
        $this->verificationService->verify($request->user(), $request->code);

        return response()->json(['message' => __('verification.verified')]);
    }

    /**
     * Resend a verification code.
     */
    public function resend(ResendRequest $request): JsonResponse
    {
        if (!$this->verificationService->canResend($request->user())) {
            throw ValidationException::withMessages([
                'code' => [__('verification.throttled')],
            ]);
        }

        $this->verificationService->send($request->user());

        return response()->json(['message' => __('verification.sent')]);
    }
}
