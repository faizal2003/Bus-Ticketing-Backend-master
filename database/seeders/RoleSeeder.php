<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view_users', 'create_users', 'edit_users', 'delete_users',

            // Bus Management
            'view_buses', 'create_buses', 'edit_buses', 'delete_buses',

            // Schedule Management
            'view_schedules', 'create_schedules', 'edit_schedules', 'delete_schedules',

            // Booking Management
            'view_bookings', 'create_bookings', 'edit_bookings', 'delete_bookings',

            // Payment Management
            'view_payments', 'verify_payments',

            // Ticket Management
            'view_tickets', 'validate_tickets', 'scan_tickets',

            // Reports
            'view_reports', 'export_reports',

            // System Configuration
            'manage_settings', 'view_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'view_buses', 'create_buses', 'edit_buses',
            'view_schedules', 'create_schedules', 'edit_schedules',
            'view_bookings', 'view_payments',
            'view_reports', 'view_dashboard',
        ]);

        $conductor = Role::create(['name' => 'kondektur', 'guard_name' => 'web']);
        $conductor->givePermissionTo([
            'view_tickets', 'validate_tickets', 'scan_tickets',
        ]);

        $passenger = Role::create(['name' => 'penumpang', 'guard_name' => 'web']);
        $passenger->givePermissionTo([
            'create_bookings', 'view_bookings',
        ]);
    }
}
