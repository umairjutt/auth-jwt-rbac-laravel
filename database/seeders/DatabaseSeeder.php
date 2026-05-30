<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.update',
            'settings.update',
            'profile.update',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions(['users.view', 'users.update']);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions(['profile.update']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'password', 'email_verified_at' => now()]
        );
        $adminUser->syncRoles(['admin']);

        $regular = User::firstOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'User', 'password' => 'password', 'email_verified_at' => now()]
        );
        $regular->syncRoles(['user']);
    }
}
