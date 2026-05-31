<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Roles\RoleManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Seed all permission slugs ─────────────────────────
        $allSlugs = collect(RoleManager::permissions())->flatten()->unique();

        $permissionMap = $allSlugs->mapWithKeys(function (string $slug) {
            $permission = Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucwords(str_replace('_', ' ', $slug))]
            );
            return [$slug => $permission->id];
        });

        // ── 2. Seed roles and attach permissions ──────────────────
        foreach (RoleManager::permissions() as $roleSlug => $slugs) {
            $role = Role::firstOrCreate(
                ['slug' => $roleSlug],
                ['name' => ucwords(str_replace('_', ' ', $roleSlug))]
            );

            $ids = collect($slugs)->map(fn ($s) => $permissionMap[$s])->all();
            $role->permissions()->sync($ids);
        }

        // ── 3. Seed the super admin user ──────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $superAdminRole = Role::where('slug', RoleManager::SUPER_ADMIN)->firstOrFail();
        $superAdmin->roles()->syncWithoutDetaching($superAdminRole);

        // Ensure profile exists. Balances are ledger-calculated, so no wallet is created.
        $superAdmin->profile()->firstOrCreate([]);

        $this->command->info('Super admin seeded: superadmin@gmail.com / password');
    }
}
