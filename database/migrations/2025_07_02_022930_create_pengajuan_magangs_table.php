<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_magang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('pembimbing_id')->nullable()->constrained('pembimbing')->onDelete('set null');
            $table->string('surat_permohonan');
            $table->string('ktm');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('durasi_magang');
            $table->string('bidang_diminati');
            $table->enum('status', ['pending', 'diterima', 'ditolak', 'selesai'])->default('pending');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // --- KOLOM ID CARD DITAMBAHKAN DI SINI ---
            $table->string('surat_balasan')->nullable();
            $table->string('id_card_path')->nullable(); // <-- KOLOM BARU
            // ------------------------------------------

            $table->string('final_laporan')->nullable();
            $table->string('sertifikat')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_magang');
    }
};
