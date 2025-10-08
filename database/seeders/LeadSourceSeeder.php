<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeadSource::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'وسائل التواصل الاجتماعي',
            ]
        );

        LeadSource::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'الإعلانات',
            ]
        );
    }
}
