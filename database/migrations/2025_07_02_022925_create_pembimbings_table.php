<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nip')->unique();
            $table->string('jabatan');
            $table->string('bidang_keahlian');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembimbing');
    }
};
