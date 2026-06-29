<?php

namespace App\Http\Requests\App\Bookmark;

use App\Enums\PlanFeature;
use App\Rules\BookmarkRules;
use App\Rules\TagRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()->canUsePlanFeature(PlanFeature::Bookmarks)) {
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
            'category_id' => BookmarkRules::categoryId($this->user()->id),
            'url' => BookmarkRules::url(),
            'title' => BookmarkRules::title(),
            'description' => BookmarkRules::description(),
            'is_favorited' => BookmarkRules::isFavorited(),
            'tags' => ['sometimes', 'array'],
            'tags.*' => TagRules::name(),
        ];
    }
}

