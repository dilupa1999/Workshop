<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enum එකට 'waitlisted' අගය එකතු කිරීම
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM('active', 'cancelled', 'waitlisted') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM('active', 'cancelled') NOT NULL DEFAULT 'active'");
    }
};
