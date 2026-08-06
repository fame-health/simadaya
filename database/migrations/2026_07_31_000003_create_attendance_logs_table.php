<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->string('token', 64);
            $table->timestamp('scan_time')->index();
            $table->text('browser')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('device_hash', 64)->nullable()->index();
            $table->string('status', 30)->default('present')->index();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'student_id']);
            $table->index(['session_id', 'status']);
            $table->index(['student_id', 'scan_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
