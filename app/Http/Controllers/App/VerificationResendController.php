<?php

namespace App\Http\Controllers\App;

use App\Enums\VerificationChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\VerificationResend\StoreRequest;
use App\Services\SendVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class VerificationResendController extends Controller
{
    public function __construct(
        protected SendVerificationService $sendVerificationService
    ) {}

    /**
     * Resend a verification code.
     */
    public function store(StoreRequest $request): JsonResponse
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
