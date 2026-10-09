<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g. USER_CREATED, WORKSHOP_CREATED, WORKSHOP_UPDATED
            $table->nullableMorphs('auditable'); // auditable_type & auditable_id
            $table->string('description');
            $table->json('changes')->nullable(); // Records previous and new attribute values
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
