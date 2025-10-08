<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::updateOrCreate(
            ['id' => 1],
            [
                'name' => ['ar' => 'عميل تجريبي 1', 'en' => 'Test Client 1'],
                'company_name' => ['ar' => 'شركة العميل التجريبي 1', 'en' => 'Test Client Company 1'],
                // 'phone' => '0501234567',
                'tax_number' => '1234567890',
                // 'address' => ['ar' => 'الرياض، المملكة العربية السعودية', 'en' => 'Riyadh, Saudi Arabia'],
                // 'email' => 'client1@test.com',
                // 'lead_source_id' => 1,
            ]
        );

        Client::updateOrCreate(
            ['id' => 2],
            [
                'name' => ['ar' => 'عميل تجريبي 2', 'en' => 'Test Client 2'],
                'company_name' => ['ar' => 'شركة العميل التجريبي 2', 'en' => 'Test Client Company 2'],
                // 'phone' => '0507654321',
                'tax_number' => '0987654321',
                // 'address' => ['ar' => 'جدة، المملكة العربية السعودية', 'en' => 'Jeddah, Saudi Arabia'],
                // 'email' => 'client2@test.com',
                // 'lead_source_id' => 2,
            ]
        );
    }
}
