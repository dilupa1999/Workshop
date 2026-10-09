<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Spatie Roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'staff']);
    }

    public function test_admin_cannot_access_workshops_catalogue(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('workshops.index'));

        // Admin must be strictly rejected with HTTP 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_manager_can_access_workshops_and_edit_page(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $workshop = Workshop::factory()->create();

        $response = $this->actingAs($manager)->get(route('workshops.index'));
        $response->assertStatus(200);

        $editResponse = $this->actingAs($manager)->get(route('workshops.edit', $workshop));
        $editResponse->assertStatus(200);
    }

    public function test_staff_can_view_workshops_but_cannot_access_edit_page(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $workshop = Workshop::factory()->create();

        // Staff can view catalogue
        $response = $this->actingAs($staff)->get(route('workshops.index'));
        $response->assertStatus(200);

        // Staff cannot access edit form
        $editResponse = $this->actingAs($staff)->get(route('workshops.edit', $workshop));
        $editResponse->assertStatus(403);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $response = $this->actingAs($manager)->get(route('users.index'));
        $response->assertStatus(403);
    }
}