<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::updateOrCreate(
            ['symbol' => 'SAR'],
            [
                'name' => ['ar' => 'الريال السعودي', 'en' => 'Saudi Riyal'],
                'symbol' => 'SAR',
            ]
        );

        Currency::updateOrCreate(
            ['symbol' => 'USD'],
            [
                'name' => ['ar' => 'الدولار الأمريكي', 'en' => 'US Dollar'],
                'symbol' => 'USD',
            ]
        );

        Currency::updateOrCreate(
            ['symbol' => 'EUR'],
            [
                'name' => ['ar' => 'اليورو', 'en' => 'Euro'],
                'symbol' => 'EUR',
            ]
        );

        Currency::updateOrCreate(
            ['symbol' => 'AED'],
            [
                'name' => ['ar' => 'الدرهم الإماراتي', 'en' => 'UAE Dirham'],
                'symbol' => 'AED',
            ]
        );
    }
}
