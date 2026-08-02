<?php

namespace App\Http\Requests\Admin\PlanPrice;

use App\Rules\PriceRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('plan'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'lookup_key' => PriceRules::lookupKey(),
        ];
    }
}
