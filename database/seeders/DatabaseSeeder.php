<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workshop;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
       
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

    
        $admin = User::firstOrCreate(
            ['email' => 'admin@workshop.com'],
            ['name' => 'System Admin', 'password' => Hash::make('Admin@1234')]
        );
        $admin->assignRole($adminRole);

        $manager = User::firstOrCreate(
            ['email' => 'manager@workshop.com'],
            ['name' => 'Programme Manager', 'password' => Hash::make('Manager@1234')]
        );
        $manager->assignRole($managerRole);

        $staff = User::firstOrCreate(
            ['email' => 'staff@workshop.com'],
            ['name' => 'Front Desk Staff', 'password' => Hash::make('Staff@1234')]
        );
        $staff->assignRole($staffRole);

        // 3. Sample Workshops (Client Requirement)
        Workshop::firstOrCreate(
            ['code' => 'WS-POT-01'],
            [
                'title' => 'Beginner Pottery Making',
                'instructor' => 'Kasun Perera',
                'date_time' => now()->addDays(2)->setHour(10)->setMinute(0),
                'capacity' => 5,
                'status' => 'scheduled',
            ]
        );

        Workshop::firstOrCreate(
            ['code' => 'WS-COD-02'],
            [
                'title' => 'Introduction to Web Coding',
                'instructor' => 'Nimal Silva',
                'date_time' => now()->addDays(5)->setHour(14)->setMinute(30),
                'capacity' => 12,
                'status' => 'scheduled',
            ]
        );

        Workshop::firstOrCreate(
            ['code' => 'WS-FIT-03'],
            [
                'title' => 'Weekend Morning Yoga & Fitness',
                'instructor' => 'Anoma Fernando',
                'date_time' => now()->addDays(7)->setHour(8)->setMinute(0),
                'capacity' => 8,
                'status' => 'scheduled',
            ]
        );
    }
}
