<?php

namespace App\Http\Requests\App\Bookmark;

use App\Enums\PlanFeature;
use App\Rules\BookmarkRules;
use App\Rules\TagRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->canUsePlanFeature(PlanFeature::Bookmarks);
    }

    /**
     * Refuse with the plan code rather than the bare message abort() produces,
     * so the client can tell a plan limit from any other 403.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'error' => 'plan_limit_reached',
            'message' => __('responses.plan.limit_reached'),
        ], 403));
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

