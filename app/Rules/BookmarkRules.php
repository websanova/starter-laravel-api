<?php

namespace App\Rules;

use App\Enums\BookmarkSort;
use App\Models\Bookmark;
use Closure;
use Illuminate\Validation\Rule;

class BookmarkRules
{
    /**
     * Validation rules for the description field.
     */
    public static function description(): array
    {
        return [
            'nullable',
            'string',
            'max:1000',
        ];
    }

    /**
     * Validation rules for the is_favorited field.
     */
    public static function isFavorited(): array
    {
        return ['sometimes', 'boolean'];
    }

    /**
     * Validation rules for the sort_by field.
     */
    public static function sortBy(): array
    {
        return ['sometimes', 'string', Rule::enum(BookmarkSort::class)];
    }

    /**
     * Validation rules for each tag_ids item.
     */
    public static function tagId(int $userId): array
    {
        return [
            'integer',
            'distinct',
            Rule::exists('tags', 'id')->where('user_id', $userId),
        ];
    }

    /**
     * Validation rules for the tag_ids field.
     */
    public static function tagIds(): array
    {
        return ['sometimes', 'array', 'max:' . config('bookmark.max_tags')];
    }

    /**
     * Validation rules for the title field.
     */
    public static function title(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:255',
        ];
    }

    /**
     * Validation rules for the url field.
     */
    public static function url(int $userId, ?int $ignoreId = null, bool $required = true): array
    {
        return [
            'bail',
            $required ? 'required' : 'sometimes',
            'string',
            'url',
            'max:2048',
            function (string $attribute, mixed $value, Closure $fail) use ($userId, $ignoreId) {
                $exists = Bookmark::where('user_id', $userId)
                    ->where('url_hash', Bookmark::hashUrl($value))
                    ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                    ->exists();

                if ($exists) {
                    $fail(__('validation.bookmark.url_duplicate'));
                }
            },
        ];
    }
}
