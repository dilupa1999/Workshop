<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkshopRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'staff']);
        $this->staffUser = User::factory()->create();
        $this->staffUser->assignRole('staff');
    }

    public function test_registrations_exceeding_capacity_are_placed_on_waitlist(): void
    {
        // 1. Create a workshop with strict capacity of 2 seats
        $workshop = Workshop::factory()->create([
            'capacity' => 2,
        ]);

        // 2. First registration -> Active
        $this->actingAs($this->staffUser)->post(route('registrations.store', $workshop), [
            'attendee_name' => 'Alice Silva',
            'attendee_email' => 'alice@test.com',
        ]);

        // 3. Second registration -> Active
        $this->actingAs($this->staffUser)->post(route('registrations.store', $workshop), [
            'attendee_name' => 'Bob Perera',
            'attendee_email' => 'bob@test.com',
        ]);

        // 4. Third registration (exceeds capacity) -> Must become Waitlisted
        $this->actingAs($this->staffUser)->post(route('registrations.store', $workshop), [
            'attendee_name' => 'Charlie Fernando',
            'attendee_email' => 'charlie@test.com',
        ]);

        $this->assertDatabaseHas('registrations', [
            'workshop_id' => $workshop->id,
            'attendee_email' => 'alice@test.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('registrations', [
            'workshop_id' => $workshop->id,
            'attendee_email' => 'bob@test.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('registrations', [
            'workshop_id' => $workshop->id,
            'attendee_email' => 'charlie@test.com',
            'status' => 'waitlisted',
        ]);
    }

    public function test_cancelling_an_active_seat_automatically_promotes_next_waitlisted_attendee(): void
    {
        $workshop = Workshop::factory()->create(['capacity' => 1]);

        // Active registration
        $activeReg = Registration::create([
            'workshop_id' => $workshop->id,
            'attendee_name' => 'Active Attendee',
            'attendee_email' => 'active@test.com',
            'status' => 'active',
            'registered_by' => $this->staffUser->id,
        ]);

        // Waitlisted registration
        $waitlistedReg = Registration::create([
            'workshop_id' => $workshop->id,
            'attendee_name' => 'Queued Attendee',
            'attendee_email' => 'queued@test.com',
            'status' => 'waitlisted',
            'registered_by' => $this->staffUser->id,
        ]);

        // Cancel the active registration
        $this->actingAs($this->staffUser)->patch(route('registrations.cancel', $activeReg));

        // Assert original is cancelled
        $this->assertDatabaseHas('registrations', [
            'id' => $activeReg->id,
            'status' => 'cancelled',
        ]);

        // Assert waitlisted attendee was elevated to active
        $this->assertDatabaseHas('registrations', [
            'id' => $waitlistedReg->id,
            'status' => 'active',
        ]);
    }
}