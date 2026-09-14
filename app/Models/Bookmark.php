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
     * Get the tags attached to this bookmark.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
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
