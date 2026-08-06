<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('template_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->string('nomer_surat')->nullable()->unique(); // Tambah kolom nomor surat
            $table->enum('jenis_surat', ['penerimaan', 'sertifikat']);
            $table->text('content_template');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_surat');
    }
};
