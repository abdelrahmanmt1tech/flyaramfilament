<?php

namespace Database\Seeders;

use App\Models\TaxType;
use Illuminate\Database\Seeder;

class TaxTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TaxType::updateOrCreate(
            ['id' => 1], 
            [
                'name'  => 'ضريبة داخلية',
                'value' => 15,
            ]
        );
    }
}
