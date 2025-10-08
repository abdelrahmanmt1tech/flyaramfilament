<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'الخطوط السعودية',
                'tax_number' => '1234567890',
            ]
        );

        Supplier::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'فلاي دبي',
                'tax_number' => '0987654321',
            ]
        );
    }
}
