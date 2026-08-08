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
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_logs', 'status')) {
                $table->string('status')->default('present')->after('token');
            }

            if (!Schema::hasColumn('attendance_logs', 'document_path')) {
                $table->string('document_path')->nullable()->after('status');
            }

            if (!Schema::hasColumn('attendance_logs', 'reason')) {
                $table->text('reason')->nullable()->after('document_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'document_path', 'reason']);
        });
    }
};
