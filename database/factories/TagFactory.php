<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * Tag names to draw from. Faker's word pool is too small and too random
     * to read like real tags when seeding at volume.
     */
    private const NAMES = [
        'Laravel', 'PHP', 'JavaScript', 'Vue', 'React',
        'Docker', 'DevOps', 'Testing', 'Database', 'Security',
        'API', 'Design', 'CSS', 'Tailwind', 'Performance',
        'Caching', 'Queues', 'Deployment', 'Monitoring', 'Logging',
        'Authentication', 'Billing', 'Stripe', 'Webhooks', 'Migrations',
        'Eloquent', 'Redis', 'MySQL', 'Postgres', 'Nginx',
        'Linux', 'Git', 'GitHub', 'Terraform', 'AWS',
        'S3', 'Email', 'Cron', 'Backups', 'Analytics',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->randomElement(self::NAMES),
        ];
    }
}
