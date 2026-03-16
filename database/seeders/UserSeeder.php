<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1 Admin (already seeded in DatabaseSeeder, but let's ensure it exists here too or skip)
        User::updateOrCreate(
            ['email' => 'admin@truckerconnect.com'],
            [
                'role' => 'admin',
                'full_name' => 'Platform Admin',
                'company_name' => 'TruckerConnect HQ',
                'password' => Hash::make('Admin@123456'),
                'phone' => '000-000-0000',
                'is_active' => 1,
            ]
        );

        // 10 Drivers
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'role' => 'driver',
                'full_name' => "Driver User $i",
                'email' => "driver$i@truckerconnect.com",
                'password' => Hash::make('password'),
                'phone' => '123-456-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'license_number' => 'LIC-' . Str::upper(Str::random(8)),
                'truck_info' => 'Semi Truck ' . $i,
                'is_active' => 1,
            ]);
        }

        // 10 Brokers
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'role' => 'broker',
                'full_name' => "Broker User $i",
                'email' => "broker$i@truckerconnect.com",
                'company_name' => "Logistics Co $i",
                'password' => Hash::make('password'),
                'phone' => '987-654-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'is_active' => 1,
            ]);
        }
    }
}
