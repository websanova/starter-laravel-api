<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed users with bookmarks and tags for local testing.
     */
    public function run(): void
    {
        $rows = [
            ['small@starter.com', 50, 5],
            ['medium@starter.com', 100, 10],
            ['large@starter.com', 200, 20],
        ];

        for ($i = 0; $i < 200; $i++) {
            $rows[] = [null, rand(10, 20), rand(5, 10)];
        }

        foreach ($rows as [$email, $bookmarks, $tags]) {
            // Tag names come from a fixed list, so the unique pool has to be
            // reset per user or later users run out of names to draw from.
            fake()->unique(true);

            $user = User::factory()->create(
                $email ? ['email' => $email] : []
            );

            $tagIds = Tag::factory()->count($tags)->for($user)->create()->pluck('id');

            Bookmark::factory()
                ->count($bookmarks)
                ->for($user)
                ->create()
                ->each(fn ($bookmark) => $bookmark->tags()->attach(
                    $tagIds->random(rand(1, 3))
                ));
        }
    }
}
