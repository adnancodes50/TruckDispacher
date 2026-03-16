<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Config seed — ALWAYS id = 1
        DB::table('config')->updateOrInsert(
            ['id' => 1],
            [
                'ticket_payment_url' => 'https://yourapp.com/pay-ticket',
                'platform_fee_percent' => 5.00,
                'maintenance_mode' => 0,
                'maintenance_message' => 'We are currently performing maintenance. Please check back soon.',
                'support_email' => 'support@truckerconnect.com',
                'support_phone' => '1-800-000-0000',
                'min_app_version' => '1.0.0',
                'updated_at' => now(),
            ]
        );

        // Admin user seed
        // Password is: Admin@123456
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@truckerconnect.com'],
            [
                'role' => 'admin',
                'full_name' => 'Platform Admin',
                'company_name' => 'TruckerConnect HQ',
                'password' => Hash::make('Admin@123456'),
                'phone' => '000-000-0000',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->call(UserSeeder::class);
    }
}
