<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('pembimbing')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('session_name', 50);
            $table->date('session_date');
            $table->string('current_token', 64)->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->timestamp('attendance_start_at')->nullable();
            $table->timestamp('attendance_end_at')->nullable();
            $table->unsignedSmallInteger('rotation_interval_seconds')->default(10);
            $table->timestamp('last_rotated_at')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();

            $table->index(['mentor_id', 'status']);
            $table->index(['location_id', 'session_date']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
