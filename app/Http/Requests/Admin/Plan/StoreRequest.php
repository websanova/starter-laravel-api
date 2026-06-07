<?php

namespace App\Http\Requests\Admin\Plan;

use App\Rules\PlanRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Plan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => PlanRules::name(),
            'slug' => PlanRules::slug(),
            'stripe_monthly_price_id' => PlanRules::stripePriceId(),
            'stripe_yearly_price_id' => PlanRules::stripePriceId(),
            'monthly_price' => PlanRules::price(),
            'yearly_price' => PlanRules::price(),
            'features' => PlanRules::features(),
            'features.*' => PlanRules::featureValue(),
            'is_active' => PlanRules::isActive(),
            'is_public' => PlanRules::isPublic(),
            'sort_order' => PlanRules::sortOrder(),
        ];
    }
}
