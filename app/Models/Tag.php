<?php

namespace App\Models;

use App\Enums\SortDirection;
use App\Enums\TagSort;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
    ];

    /**
     * Bootstrap the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Tag $tag) {
            $tag->name = strtolower(trim($tag->name));
            $tag->slug = Str::slug($tag->name);
        });

        static::updating(function (Tag $tag) {
            if ($tag->isDirty('name')) {
                $tag->name = strtolower(trim($tag->name));
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get the user that owns the tag.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sort by the given column and direction.
     */
    public function scopeSortBy(Builder $query, ?TagSort $column = null, ?SortDirection $direction = null): void
    {
        $query->orderBy(
            ($column ?? TagSort::Name)->value,
            ($direction ?? SortDirection::Asc)->value
        );
    }
}
