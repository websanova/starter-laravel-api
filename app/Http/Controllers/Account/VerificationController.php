<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Verification\ResendRequest;
use App\Http\Requests\Account\Verification\VerifyRequest;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
        $result = $this->verificationService->verify($request->user(), $request->code);

        if (!$result->success) {
            throw ValidationException::withMessages([
                'code' => [__("responses.{$result->error}")],
            ]);
        }

        return response()->json(['message' => __('responses.verification.verified')]);
    }

    /**
     * Resend a verification code.
     */
    public function resend(ResendRequest $request): JsonResponse
    {
        if (!$this->verificationService->canResend($request->user())) {
            throw new TooManyRequestsHttpException(null, __('responses.verification.throttled'));
        }

        $this->verificationService->send($request->user());

        return response()->json(['message' => __('responses.verification.sent')]);
    }
}
