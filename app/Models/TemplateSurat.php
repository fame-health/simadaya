<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    use HasFactory;

    protected $table = 'template_surat';

    protected $fillable = [
        'nama_template',
        'nomer_surat', // pastikan kolom ini sudah ada di database
        'jenis_surat',
        'content_template',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const JENIS_PENERIMAAN = 'penerimaan';
    const JENIS_SERTIFIKAT = 'sertifikat';

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Boot method untuk generate nomor surat otomatis
     */
    protected static function booted()
    {
        static::created(function ($template) {
            $bulan = date('m');
            $tahun = date('Y');

            // Format: PEN/{bulan}/{tahun}/{id}
            $nomerSurat = 'NO/' . $bulan . '/' . $tahun . '/' . $template->id;

            // Update record dengan nomor surat
            $template->update(['nomer_surat' => $nomerSurat]);
        });
    }


}
