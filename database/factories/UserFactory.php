<?php

namespace Database\Factories;

use App\Enums\BookmarkSort;
use App\Enums\BookmarkView;
use App\Enums\SortDirection;
use App\Enums\UserSort;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'locale' => fake()->boolean() ? fake()->randomElement(config('user.supported_locales')) : null,
            'timezone' => fake()->boolean() ? fake()->timezone() : null,
            'preferences' => [
                'admin' => self::randomPreferences([
                    'users_sort_by' => UserSort::cases(),
                    'users_sort_dir' => SortDirection::cases(),
                ]),
                'app' => self::randomPreferences([
                    'bookmarks_sort_by' => BookmarkSort::cases(),
                    'bookmarks_sort_dir' => SortDirection::cases(),
                    'bookmarks_view' => BookmarkView::cases(),
                ]),
            ],
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('testtest'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Pick a random value for some of the given preferences. Each key is
     * included or skipped on its own so the seeded rows come out partial,
     * which is the shape real rows have once config gains a new preference.
     *
     * @param  array<string, list<\BackedEnum>>  $choices
     * @return array<string, string>
     */
    protected static function randomPreferences(array $choices): array
    {
        $preferences = [];

        foreach ($choices as $key => $cases) {
            if (fake()->boolean()) {
                $preferences[$key] = fake()->randomElement($cases)->value;
            }
        }

        return $preferences;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
