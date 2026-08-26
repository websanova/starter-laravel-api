<?php

namespace App\Http\Requests\App\PromotionCodeVerify;

use App\Rules\SubscriptionRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'promotion_code' => SubscriptionRules::promotionCode(required: true),
        ];
    }
}
