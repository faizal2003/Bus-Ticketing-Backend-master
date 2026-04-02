<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@bus.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567890',
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // Create Admin
        $admin = User::create([
            'name' => 'Admin Bus',
            'email' => 'admin@bus.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567891',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create Conductor
        $conductor = User::create([
            'name' => 'Kondektur Budi',
            'email' => 'kondektur@bus.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567892',
            'role' => 'kondektur',
            'email_verified_at' => now(),
        ]);
        $conductor->assignRole('kondektur');

        // Create Sample Passenger
        $passenger = User::create([
            'name' => 'Penumpang Andi',
            'email' => 'penumpang@bus.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567893',
            'role' => 'penumpang',
            'email_verified_at' => now(),
        ]);
        $passenger->assignRole('penumpang');
    }
}
