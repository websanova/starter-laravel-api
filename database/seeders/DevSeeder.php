<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as SpatieRole;

class DevSeeder extends Seeder
{
    /**
     * Seed development users.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@starter.com',
            'password' => Hash::make('testtest'),
        ]);

        $admin->assignRole(SpatieRole::findByName(UserRole::Admin->value, 'api'));

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user@starter.com',
            'password' => Hash::make('testtest'),
        ]);
    }
}
