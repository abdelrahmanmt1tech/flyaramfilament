<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SafesBank;

class SafesBankSeeder extends Seeder
{
    public function run(): void
    {
        SafesBank::create([
            'name' => 'Main Safe',
            'type' => 'safe',
            'balance' => 100000,
            'notes' => 'الخزنة الرئيسية للشركة',
        ]);

        SafesBank::create([
            'name' => 'CIB Bank',
            'type' => 'bank',
            'account_number' => '1234567890',
            'balance' => 250000,
            'notes' => 'حساب بنك CIB الأساسي',
        ]);

        SafesBank::create([
            'name' => 'QNB Bank',
            'type' => 'bank',
            'account_number' => '9876543210',
            'balance' => 50000,
            'notes' => 'حساب فرع مدينة نصر',
        ]);
    }
}
