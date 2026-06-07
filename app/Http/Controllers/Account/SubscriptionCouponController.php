<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\PromotionCodeResource;
use App\Services\PromotionCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SubscriptionCouponController extends Controller
{
    /**
     * Look up a promotion code.
     */
    public function show(string $code, PromotionCodeService $promotionCodeService): JsonResponse
    {
        $result = $promotionCodeService->resolve($code);

        if (!$result->success) {
            throw ValidationException::withMessages([
                'promotion_code' => [__("validation.{$result->error}")],
            ]);
        }

        return response()->json([
            'data' => new PromotionCodeResource($result->data),
        ]);
    }
}
