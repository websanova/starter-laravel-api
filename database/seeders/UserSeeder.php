<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed users with bookmarks and tags for local testing.
     */
    public function run(): void
    {
        $rows = [
            ['email' => 'admin@starter.com', 'bookmarks' => 0, 'tags' => 0, 'role' => UserRole::Admin, 'welcome' => false],
            ['email' => 'small@starter.com', 'bookmarks' => 50, 'tags' => 5, 'role' => null, 'welcome' => true],
            ['email' => 'medium@starter.com', 'bookmarks' => 100, 'tags' => 10, 'role' => null, 'welcome' => true],
            ['email' => 'large@starter.com', 'bookmarks' => 200, 'tags' => 20, 'role' => null, 'welcome' => true],
        ];

        for ($i = 0; $i < 200; $i++) {
            $rows[] = ['email' => null, 'bookmarks' => rand(10, 20), 'tags' => rand(5, 10), 'role' => null, 'welcome' => false];
        }

        foreach ($rows as ['email' => $email, 'bookmarks' => $bookmarks, 'tags' => $tags, 'role' => $role, 'welcome' => $welcome]) {
            // Tag names come from a fixed list, so the unique pool has to be
            // reset per user or later users run out of names to draw from.
            fake()->unique(true);

            $user = User::factory()->create(
                $email ? ['email' => $email] : []
            );

            if ($role) {
                $user->assignRole($role);
            }

            $tagIds = Tag::factory()->count($tags)->for($user)->create()->pluck('id');

            Bookmark::factory()
                ->count($bookmarks)
                ->for($user)
                ->create()
                ->each(fn ($bookmark) => $bookmark->tags()->attach(
                    $tagIds->random(rand(1, 3))
                ));

            // Written straight to the table rather than through notify(), which
            // would also fire the mail channel.
            if ($welcome) {
                $user->notifications()->create([
                    'id' => Str::uuid(),
                    'type' => WelcomeNotification::class,
                    'data' => [
                        'title' => __('notifications.welcome.subject'),
                        'body' => __('notifications.welcome.line1'),
                    ],
                ]);
            }
        }
    }
}
