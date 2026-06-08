<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Seed roles, permissions, and the initial super user.
     */
    public function run(): void
    {
        $permissions = [
            'users.manage',
            'users.assign-role',
            'plans.manage',
            'stats.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        $super = SpatieRole::findOrCreate(UserRole::Super->value, 'api');
        $admin = SpatieRole::findOrCreate(UserRole::Admin->value, 'api');

        $admin->syncPermissions($permissions);

        $user = User::firstOrCreate(
            ['email' => 'super@starter.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'User',
                'email_verified_at' => now(),
                'password' => Hash::make('initinit'),
                'is_password_reset_required' => true,
            ],
        );

        $user->assignRole($super);
    }
}
