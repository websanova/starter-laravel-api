<?php

namespace App\Models;

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
}
