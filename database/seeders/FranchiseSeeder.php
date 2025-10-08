<?php

namespace Database\Seeders;

use App\Models\Franchise;
use Illuminate\Database\Seeder;

class FranchiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Franchise::updateOrCreate(
            ['id' => 1],
            [
                'name' => ['ar' => 'فرانشايز الرياض', 'en' => 'Riyadh Franchise'],
                'tax_number' => '123456789012345',
            ]
        );

        Franchise::updateOrCreate(
            ['id' => 2],
            [
                'name' => ['ar' => 'فرانشايز جدة', 'en' => 'Jeddah Franchise'],
                'tax_number' => '123456789012346',
            ]
        );
    }
}
