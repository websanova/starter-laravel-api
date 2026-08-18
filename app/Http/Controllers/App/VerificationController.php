<?php

namespace App\Http\Controllers\App;

use App\Enums\VerificationChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Verification\ResendRequest;
use App\Http\Requests\App\Verification\VerifyRequest;
use App\Services\ConfirmVerificationService;
use App\Services\SendVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class VerificationController extends Controller
{
    public function __construct(
        protected ConfirmVerificationService $confirmVerificationService,
        protected SendVerificationService $sendVerificationService
    ) {}

    /**
     * Verify the user's code.
     */
    public function verify(VerifyRequest $request): JsonResponse
    {
        $result = $this->confirmVerificationService->handle(
            $request->user(),
            $request->code,
            VerificationChannel::from($request->channel)
        );

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
        $channel = VerificationChannel::from($request->channel);

        $result = $this->sendVerificationService->handle($request->user(), $channel);

        if (!$result->success) {
            if ($result->error === 'verification.throttled') {
                throw new TooManyRequestsHttpException(null, __("responses.{$result->error}"));
            }

            throw ValidationException::withMessages([
                'channel' => [__("responses.{$result->error}")],
            ]);
        }

        return response()->json(['message' => __('responses.verification.sent')]);
    }
}

