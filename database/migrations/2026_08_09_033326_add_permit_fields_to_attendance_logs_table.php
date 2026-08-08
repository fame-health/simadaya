<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $blueprint) {
            // Status: present (hadir), sick (sakit), permit (izin)
            $blueprint->string('status')->default('present')->after('token');
            $blueprint->string('document_path')->nullable()->after('status');
            $blueprint->text('reason')->nullable()->after('document_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['status', 'document_path', 'reason']);
        });
    }
};
