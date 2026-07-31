<?php

namespace App\Http\Requests\App\Subscription;

use App\Enums\PlanInterval;
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
            'plan' => SubscriptionRules::planPublic(),
            'interval' => SubscriptionRules::interval(),
            'promotion_code' => SubscriptionRules::promotionCode(),
        ];
    }

    /**
     * Get the validated data with enum fields cast to their types.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();

        if (isset($data['interval'])) {
            $data['interval'] = PlanInterval::from($data['interval']);
        }

        if (!is_null($key)) {
            return data_get($data, $key, $default);
        }

        return $data;
    }
}
