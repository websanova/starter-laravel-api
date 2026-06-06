<?php

namespace App\Models;

use App\Enums\BookmarkSort;
use App\Enums\SortDirection;
use Database\Factories\BookmarkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Bookmark extends Model
{
    /** @use HasFactory<BookmarkFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'url',
        'title',
        'description',
        'is_favorited',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_favorited' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category this bookmark belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the tags attached to this bookmark.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Sync tags by name, creating any that don't exist.
     */
    public function syncTags(?array $names): void
    {
        if (is_null($names)) {
            return;
        }

        $ids = collect($names)->map(function ($name) {
            return Tag::firstOrCreate(
                ['user_id' => $this->user_id, 'slug' => Str::slug(strtolower(trim($name)))],
                ['name' => strtolower(trim($name))]
            );
        })->pluck('id');

        $this->tags()->sync($ids);
    }

    /**
     * Filter by favorited status.
     */
    public function scopeForFavorited(Builder $query, ?bool $favorited): void
    {
        if (is_null($favorited)) {
            return;
        }

        $query->where('is_favorited', $favorited);
    }

    /**
     * Filter by category, where 0 means uncategorized.
     */
    public function scopeForCategory($query, ?int $categoryId): void
    {
        if (is_null($categoryId)) {
            return;
        }

        if ($categoryId === 0) {
            $query->whereNull('category_id');
        } else {
            $query->where('category_id', $categoryId);
        }
    }

    /**
     * Sort by the given column and direction.
     */
    public function scopeSortBy(Builder $query, ?BookmarkSort $column = null, ?SortDirection $direction = null): void
    {
        $query->orderBy(
            ($column ?? BookmarkSort::CreatedAt)->value,
            ($direction ?? SortDirection::Desc)->value
        );
    }
}
