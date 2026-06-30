<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverBusTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_drivers(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Access driver list
        $response = $this->actingAs($superAdmin)->get(route('superadmin.drivers.index'));
        $response->assertStatus(200);

        // Add a driver
        $response = $this->actingAs($superAdmin)->post(route('superadmin.drivers.store'), [
            'name' => 'Driver Baru',
            'phone' => '08123456789',
            'license_number' => 'SIM-B-123',
            'status' => 'active',
        ]);
        $response->assertRedirect(route('superadmin.drivers.index'));
        $this->assertDatabaseHas('drivers', ['name' => 'Driver Baru']);
    }

    public function test_non_super_admin_cannot_access_driver_management(): void
    {
        $admin = User::create([
            'name' => 'Admin biasa',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('superadmin.drivers.index'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_super_admin_can_create_bus_with_driver_and_conductor(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $driver = Driver::create([
            'name' => 'Joko',
            'phone' => '08123',
            'status' => 'active',
        ]);

        $conductor = User::create([
            'name' => 'Kondektur 1',
            'email' => 'kondektur@test.com',
            'password' => bcrypt('password'),
            'role' => 'kondektur',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.buses.store'), [
            'bus_name' => 'Bus Test 123',
            'bus_number' => 999,
            'plate_number' => 'B 1234 ABC',
            'total_seats' => 40,
            'bus_type' => 'executive',
            'status' => 'active',
            'driver_id' => $driver->id,
            'conductor_id' => $conductor->id,
        ]);

        $response->assertRedirect(route('admin.buses.index'));
        $this->assertDatabaseHas('buses', [
            'bus_name' => 'Bus Test 123',
            'driver_id' => $driver->id,
            'conductor_id' => $conductor->id,
        ]);
    }

    public function test_users_can_update_profile_picture(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::create([
            'name' => 'Penumpang Biasa',
            'email' => 'penumpang@test.com',
            'password' => bcrypt('password'),
            'role' => 'penumpang',
            'is_active' => true,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Penumpang Updated',
            'email' => 'penumpang@test.com',
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertNotNull($user->avatar);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar);
    }
}
