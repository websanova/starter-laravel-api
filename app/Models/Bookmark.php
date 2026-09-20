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
     * Bootstrap the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Bookmark $bookmark) {
            if ($bookmark->isDirty('url')) {
                $bookmark->url_hash = static::hashUrl($bookmark->url);
            }
        });
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
     * Filter by tag.
     */
    public function scopeForTag(Builder $query, ?int $tagId): void
    {
        if (is_null($tagId)) {
            return;
        }

        $query->whereHas('tags', fn ($q) => $q->where('tags.id', $tagId));
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

    /**
     * Normalize a URL and hash it for duplicate detection. Only differences
     * that never change the target page are normalized away: scheme and host
     * case, fragment, default port, trailing slash, and query parameter order.
     * Query parameters are sorted as raw strings so their encoding is untouched.
     */
    public static function hashUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return hash('sha256', $url);
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');
        $port = $parts['port'] ?? null;
        $path = rtrim($parts['path'] ?? '', '/');

        $defaultPorts = ['http' => 80, 'https' => 443];

        if (!is_null($port) && ($defaultPorts[$scheme] ?? null) === $port) {
            $port = null;
        }

        $params = array_filter(explode('&', $parts['query'] ?? ''), fn ($param) => $param !== '');
        sort($params);

        $normalized = $scheme . '://' . $host;

        if (!is_null($port)) {
            $normalized .= ':' . $port;
        }

        $normalized .= $path;

        if (count($params) > 0) {
            $normalized .= '?' . implode('&', $params);
        }

        return hash('sha256', $normalized);
    }
}
