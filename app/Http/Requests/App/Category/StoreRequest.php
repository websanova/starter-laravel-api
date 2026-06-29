<?php

namespace App\Http\Requests\App\Category;

use App\Enums\PlanFeature;
use App\Rules\CategoryRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()->canUsePlanFeature(PlanFeature::Categories)) {
            abort(403, __('responses.plan.limit_reached'));
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => CategoryRules::name(),
        ];
    }
}

