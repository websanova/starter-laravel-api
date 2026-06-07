<?php

namespace App\Http\Requests\Admin\UserSubscription;

use App\Enums\PlanInterval;
use App\Rules\SubscriptionRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'plan' => SubscriptionRules::plan(),
            'interval' => SubscriptionRules::intervalOptional(),
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
