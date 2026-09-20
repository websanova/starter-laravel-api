<?php

namespace App\Rules;

use App\Enums\TagSort;
use App\Models\Tag;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagRules
{
    /**
     * Validation rules for the name field.
     */
    public static function name(int $userId, ?int $ignoreId = null, bool $required = true): array
    {
        return [
            'bail',
            $required ? 'required' : 'sometimes',
            'string',
            'max:50',
            new TagNameFormat,
            function (string $attribute, mixed $value, Closure $fail) use ($userId, $ignoreId) {
                $exists = Tag::where('user_id', $userId)
                    ->where('slug', Str::slug(trim($value)))
                    ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                    ->exists();

                if ($exists) {
                    $fail(__('validation.tag.name_duplicate'));
                }
            },
        ];
    }

    /**
     * Validation rules for the sort_by field.
     */
    public static function sortBy(): array
    {
        return ['sometimes', 'string', Rule::enum(TagSort::class)];
    }
}
