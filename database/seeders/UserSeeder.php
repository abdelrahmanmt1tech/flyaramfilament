<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Support\PermissionRegistry;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */




    public function run(): void
    {
        // Create or get the super admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'مدير عام',
            'guard_name' => 'web'
        ]);

        // Sync all permissions first
        PermissionRegistry::sync();

        // Get all permissions
        $allPermissions = Permission::all();

        // Give all permissions to super admin role
        $superAdminRole->syncPermissions($allPermissions);

        // Create admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'المدير العام',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'iata_code' => 'IATA001',
            ]
        );

        // Assign super admin role to admin user
        $adminUser->assignRole($superAdminRole);
    }
}
