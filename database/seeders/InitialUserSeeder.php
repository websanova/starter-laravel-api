<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as SpatieRole;

class InitialUserSeeder extends Seeder
{
    /**
     * Seed the initial admin and regular user.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@starter.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email_verified_at' => now(),
                'password' => Hash::make('testtest'),
                'is_password_reset_required' => false,
            ],
        );

        $admin->assignRole(SpatieRole::findByName(UserRole::Admin->value, 'api'));

        User::firstOrCreate(
            ['email' => 'user@starter.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email_verified_at' => now(),
                'password' => Hash::make('testtest'),
                'is_password_reset_required' => false,
            ],
        );
    }
}
