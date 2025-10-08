<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::updateOrCreate(
            ['id' => 1],
            [
                // 'code' => 'BR001',
                'name' => ['ar' => 'الفرع الرئيسي', 'en' => 'Main Branch'],
                'tax_number' => '123456789012345',
            ]
        );

        Branch::updateOrCreate(
            ['id' => 2],
            [
                // 'code' => 'BR002',
                'name' => ['ar' => 'فرع جدة', 'en' => 'Jeddah Branch'],
                'tax_number' => '123456789012346',
            ]
        );
    }
}
