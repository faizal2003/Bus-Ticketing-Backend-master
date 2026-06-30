<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        Driver::create([
            'name' => 'Joko Susilo',
            'phone' => '081234567001',
            'license_number' => 'SIM-A-12345',
            'status' => 'active',
        ]);

        Driver::create([
            'name' => 'Slamet Riyadi',
            'phone' => '081234567002',
            'license_number' => 'SIM-B1-54321',
            'status' => 'active',
        ]);

        Driver::create([
            'name' => 'Bambang Pamungkas',
            'phone' => '081234567003',
            'license_number' => 'SIM-B2-99999',
            'status' => 'active',
        ]);
    }
}
