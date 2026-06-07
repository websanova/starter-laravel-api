<?php

namespace App\Http\Requests\Admin\UserSubscriptionCoupon;

use App\Rules\SubscriptionRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'promotion_code' => SubscriptionRules::promotionCodeRequired(),
        ];
    }
}
