<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Database\Factories\TagFactory;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed users with bookmarks and tags for local testing.
     */
    public function run(): void
    {
        $rows = [
            ['email' => 'admin@starter.com', 'bookmarks' => 0, 'tags' => 0, 'role' => UserRole::Admin, 'welcome' => false, 'avatar' => 'cat1.jpg'],
            ['email' => 'small@starter.com', 'bookmarks' => 50, 'tags' => 5, 'role' => null, 'welcome' => true, 'avatar' => 'cat2.jpg'],
            ['email' => 'medium@starter.com', 'bookmarks' => 100, 'tags' => 10, 'role' => null, 'welcome' => true, 'avatar' => 'cat3.jpg'],
            ['email' => 'large@starter.com', 'bookmarks' => 200, 'tags' => 20, 'role' => null, 'welcome' => true, 'avatar' => 'cat4.jpg'],
        ];

        for ($i = 0; $i < 200; $i++) {
            $rows[] = ['email' => null, 'bookmarks' => rand(10, 20), 'tags' => rand(5, 10), 'role' => null, 'welcome' => false, 'avatar' => null];
        }

        foreach ($rows as ['email' => $email, 'bookmarks' => $bookmarks, 'tags' => $tags, 'role' => $role, 'welcome' => $welcome, 'avatar' => $avatar]) {
            $user = User::factory()->create(
                $email ? ['email' => $email] : []
            );

            if ($role) {
                $user->assignRole($role);
            }

            // Faked as an upload so the seed runs through the same crop and
            // encode the app applies, rather than reimplementing it here.
            if ($avatar) {
                $user->storeAvatar(new UploadedFile(
                    database_path('seeders/avatars/' . $avatar),
                    $avatar,
                    test: true,
                ));
            }

            // Names are drawn from a fixed list, so they have to be handed in
            // already distinct. Faker's unique pool is shared and global, and
            // resetting it per user would wipe the factory's email history too.
            $names = collect(TagFactory::NAMES)
                ->shuffle()
                ->take($tags)
                ->map(fn ($name) => ['name' => $name])
                ->all();

            $tagIds = Tag::factory()
                ->count($tags)
                ->for($user)
                ->sequence(...$names)
                ->create()
                ->pluck('id');

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
