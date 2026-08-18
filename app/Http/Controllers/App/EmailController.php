<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Email\StoreRequest;
use App\Services\SendEmailChangeService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class EmailController extends Controller
{
    public function __construct(
        protected SendEmailChangeService $sendEmailChangeService
    ) {}

    /**
     * Request an email change for the authenticated user.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->sendEmailChangeService->handle($request->user(), $request->validated('email'));

        if (!$result->success) {
            throw new TooManyRequestsHttpException(null, __("responses.{$result->error}"));
        }

        return response()->json(['message' => __('responses.email_change.sent')]);
    }
}

