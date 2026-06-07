<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\PromotionCodeResource;
use App\Rules\ValidPromotionCode;
use Illuminate\Http\JsonResponse;

class SubscriptionCouponController extends Controller
{
    /**
     * Look up a promotion code.
     */
    public function show(string $code): JsonResponse
    {
        $rule = new ValidPromotionCode;
        $errors = [];

        $rule->validate('code', $code, function ($message) use (&$errors) {
            $errors[] = $message;
        });

        if ($errors) {
            return response()->json([
                'message' => $errors[0],
                'errors' => ['code' => $errors],
            ], 422);
        }

        return response()->json([
            'data' => new PromotionCodeResource(ValidPromotionCode::resolved()),
        ]);
    }
}
