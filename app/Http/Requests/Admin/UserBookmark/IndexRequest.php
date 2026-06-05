<?php

namespace App\Http\Requests\Admin\UserBookmark;

use App\Enums\BookmarkSort;
use App\Enums\SortDirection;
use App\Rules\BookmarkRules;
use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => SharedRules::id(allowZero: true),
            'favorited' => BookmarkRules::isFavorited(),
            'sort_by' => BookmarkRules::sortBy(),
            'sort_dir' => SharedRules::sortDir(),
            'per_page' => SharedRules::perPage(),
        ];
    }

    /**
     * Get the validated data with enum fields cast to their types.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();

        if (array_key_exists('favorited', $data)) {
            $data['favorited'] = filter_var($data['favorited'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($data['sort_by'])) {
            $data['sort_by'] = BookmarkSort::from($data['sort_by']);
        }

        if (isset($data['sort_dir'])) {
            $data['sort_dir'] = SortDirection::from($data['sort_dir']);
        }

        if (!is_null($key)) {
            return data_get($data, $key, $default);
        }

        return $data;
    }
}
