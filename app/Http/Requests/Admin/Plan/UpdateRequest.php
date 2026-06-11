<?php

namespace App\Http\Requests\Admin\Plan;

use App\Rules\PlanRules;
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
            'name' => PlanRules::name(required: false),
            'slug' => PlanRules::slug(required: false, ignore: $this->route('plan')->id),
            'features' => PlanRules::features(),
            'features.*' => PlanRules::featureValue(),
            'is_active' => PlanRules::isActive(),
            'is_public' => PlanRules::isPublic(),
            'sort_order' => PlanRules::sortOrder(),
        ];
    }
}
