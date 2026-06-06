<?php

namespace App\Http\Requests\Account\Tag;

use App\Enums\PlanFeature;
use App\Rules\TagRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()->canUsePlanFeature(PlanFeature::Tags)) {
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
            'name' => TagRules::name(),
        ];
    }
}
