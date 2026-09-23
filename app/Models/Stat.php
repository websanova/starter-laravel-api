<?php

namespace App\Models;

use App\Enums\StatGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'calculated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    /**
     * Filter by group.
     */
    public function scopeForGroup(Builder $query, ?string $group): void
    {
        if (is_null($group)) {
            return;
        }

        $query->where('group', $group);
    }

    /**
     * Groups that are still calculated but not worth showing yet. Subscription
     * stats have no seeded data behind them, so they only ever report zeros.
     */
    public function scopeVisible(Builder $query): void
    {
        $query->whereNot('group', StatGroup::Subscriptions->value);
    }
}
