<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'mahasiswa_id',
        'pembimbing_id',
        'aspek_penilaian',
        'nilai',
        'bobot',
        'keterangan',
        'nilai_akhir',
        'grade',
        'tanggal_penilaian',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'bobot' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'tanggal_penilaian' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($penilaian) {
            // Calculate nilai_akhir (e.g., nilai * bobot)
            $penilaian->nilai_akhir = $penilaian->nilai * $penilaian->bobot;

            // Optionally calculate grade based on nilai_akhir
            $penilaian->grade = $penilaian->calculateGrade($penilaian->nilai_akhir);
        });
    }

    public function pengajuanMagang()
    {
        return $this->belongsTo(PengajuanMagang::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class);
    }

    public static function getAllStudents()
    {
        return self::with('mahasiswa')
            ->distinct('mahasiswa_id')
            ->get()
            ->pluck('mahasiswa')
            ->filter()
            ->values();
    }

    public function scopeWithStudents($query)
    {
        return $query->with('mahasiswa')->distinct('mahasiswa_id');
    }

    protected function calculateGrade($nilai_akhir): string
    {
        // Example grading logic (adjust as needed)
        if ($nilai_akhir >= 85) {
            return 'A';
        } elseif ($nilai_akhir >= 70) {
            return 'B';
        } elseif ($nilai_akhir >= 55) {
            return 'C';
        } elseif ($nilai_akhir >= 40) {
            return 'D';
        } else {
            return 'E';
        }
    }
}
