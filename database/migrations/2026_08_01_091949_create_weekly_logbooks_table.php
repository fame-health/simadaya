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
        Schema::create('weekly_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->unsignedInteger('week_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('activities');
            $table->text('achievements')->nullable();
            $table->text('problems')->nullable();
            $table->string('attachment')->nullable();
            $table->text('mentor_feedback')->nullable();
            $table->string('status')->default('submitted'); // submitted, approved, revision_needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_logbooks');
    }
};
