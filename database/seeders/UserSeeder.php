<?php

namespace Database\Seeders;

use App\Enums\StoragePath;
use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Database\Factories\TagFactory;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed users with bookmarks and tags for local testing.
     */
    public function run(): void
    {
        // Previous runs leave their files behind once the rows pointing at
        // them are gone, so the directory grows on every reseed.
        Storage::deleteDirectory(StoragePath::UserAvatar->value);

        $avatars = collect(File::files(database_path('seeders/avatars')))
            ->map(fn ($file) => $file->getFilename());

        $rows = [
            ['email' => 'admin@starter.com', 'bookmarks' => 0, 'tags' => 0, 'role' => UserRole::Admin, 'welcome' => false, 'avatar' => 'animal-07.jpg'],
            ['email' => 'empty@starter.com', 'bookmarks' => 0, 'tags' => 0, 'role' => null, 'welcome' => true, 'avatar' => 'animal-02.jpg'],
            ['email' => 'small@starter.com', 'bookmarks' => 50, 'tags' => 5, 'role' => null, 'welcome' => true, 'avatar' => 'cat-07.jpg'],
            ['email' => 'medium@starter.com', 'bookmarks' => 100, 'tags' => 10, 'role' => null, 'welcome' => true, 'avatar' => 'cat-05.jpg'],
            ['email' => 'large@starter.com', 'bookmarks' => 200, 'tags' => 20, 'role' => null, 'welcome' => true, 'avatar' => 'cat-01.jpg'],
        ];

        for ($i = 0; $i < 200; $i++) {
            $rows[] = ['email' => null, 'bookmarks' => rand(10, 20), 'tags' => rand(5, 10), 'role' => null, 'welcome' => false, 'avatar' => fake()->boolean(75)];
        }

        foreach ($rows as ['email' => $email, 'bookmarks' => $bookmarks, 'tags' => $tags, 'role' => $role, 'welcome' => $welcome, 'avatar' => $avatar]) {
            // Everything would otherwise land on today, leaving the stats
            // calculator with nothing in its yesterday and day before ranges.
            $createdAt = fake()->dateTimeBetween('-7 days', 'now');

            $attributes = [
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'last_active_at' => fake()->dateTimeBetween($createdAt, 'now'),
            ];

            if ($email) {
                $attributes['email'] = $email;
            }

            $user = User::factory()->create($attributes);

            if ($role) {
                $user->assignRole($role);
            }

            // Named rows name their file, the rest just flag that they want
            // one. Faked as an upload so the seed runs through the same crop
            // and encode the app applies, rather than reimplementing it here.
            if ($avatar) {
                $file = is_string($avatar) ? $avatar : $avatars->random();

                $user->storeAvatar(new UploadedFile(
                    database_path('seeders/avatars/' . $file),
                    $file,
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
                ->state(fn () => self::timestamps($createdAt))
                ->create()
                ->pluck('id');

            Bookmark::factory()
                ->count($bookmarks)
                ->for($user)
                ->state(fn () => self::timestamps($createdAt))
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

    /**
     * A timestamp pair somewhere between the user's own creation and now, so
     * their rows never predate them.
     *
     * @return array<string, \DateTimeInterface>
     */
    protected static function timestamps(\DateTimeInterface $createdAt): array
    {
        $timestamp = fake()->dateTimeBetween($createdAt, 'now');

        return [
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}
