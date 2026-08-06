<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('catatan_pembimbing', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Pastikan menggunakan InnoDB

            $table->id();

            // Perbaikan 1: Tentukan nama tabel referensi secara eksplisit
            $table->foreignId('pengajuan_magang_id')
                  ->constrained('pengajuan_magang') // Pastikan nama tabel benar
                  ->onDelete('cascade');

            // Perbaikan 2: Pastikan nama tabel referensi benar
            $table->foreignId('pembimbing_id')
                  ->constrained('pembimbing')
                  ->onDelete('cascade');

            $table->foreignId('mahasiswa_id')
                  ->constrained('mahasiswa')
                  ->onDelete('cascade');

            $table->timestamp('tanggal_catatan');
            $table->string('judul_catatan');
            $table->text('isi_catatan');
            $table->enum('tipe_catatan', ['feedback', 'evaluasi', 'bimbingan']);
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Tambahkan index untuk performa
            $table->index('pengajuan_magang_id');
            $table->index('pembimbing_id');
            $table->index('mahasiswa_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('catatan_pembimbing');
    }
};
