<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nim')->unique();
            $table->string('universitas');
            $table->string('fakultas');
            $table->string('jurusan');
            $table->integer('semester');
            $table->decimal('ipk', 3, 2);
            $table->text('alamat');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);

            // --- KOLOM BARU DITAMBAHKAN DI SINI ---
            $table->string('profile_photo_path')->nullable(); // Path foto profil mahasiswa (Opsional)
            $table->string('nomor_hp')->nullable();           // Nomor handphone mahasiswa (Opsional)
            // ------------------------------------------

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
};
