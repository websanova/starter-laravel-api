<?php

namespace App\Http\Requests\Account\Subscription;

use App\Enums\PlanInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->subscribed();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', 'exists:plans,slug'],
            'interval' => ['required', 'string', Rule::enum(PlanInterval::class)],
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
