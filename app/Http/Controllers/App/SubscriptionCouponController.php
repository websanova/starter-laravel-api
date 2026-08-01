<?php

namespace App\Http\Controllers\App;

use App\Contracts\PromotionCodeProvider;
use App\Http\Controllers\Controller;
use App\Http\Resources\App\PromotionCodeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SubscriptionCouponController extends Controller
{
    /**
     * Look up a promotion code.
     */
    public function show(string $code, PromotionCodeProvider $promotionCodeService): JsonResponse
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

