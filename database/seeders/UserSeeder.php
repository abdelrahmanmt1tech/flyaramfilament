<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                // 'code' => 'ADM001',
                'name' => 'المدير العام',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'iata_code' => 'IATA001',
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@admin.com'],
            [
                // 'code' => 'MGR001',
                'name' => 'مدير المبيعات',
                'email' => 'manager@admin.com',
                'password' => Hash::make('123456'),
                'iata_code' => 'IATA002',
            ]
        );
    }
}
