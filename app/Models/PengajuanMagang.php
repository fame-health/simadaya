<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $mahasiswa_id
 * @property int|null $pembimbing_id
 * @property string $surat_permohonan
 * @property string $ktm
 * @property \Illuminate\Support\Carbon $tanggal_mulai
 * @property \Illuminate\Support\Carbon $tanggal_selesai
 * @property int $durasi_magang
 * @property string $bidang_diminati
 * @property string $status
 * @property string|null $alasan_penolakan
 * @property \Illuminate\Support\Carbon|null $tanggal_verifikasi
 * @property int|null $verified_by
 * @property string|null $surat_balasan
 * @property string|null $id_card_path      <-- [TELAH DITAMBAHKAN]
 * @property string|null $final_laporan
 * @property string|null $sertifikat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at

 * @property-read \App\Models\Mahasiswa $mahasiswa
 * @property-read \App\Models\Pembimbing|null $pembimbing
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penilaian> $penilaian
 * @property-read int|null $penilaian_count
 * @property-read \App\Models\User|null $verifikator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereAlasanPenolakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereBidangDiminati($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereDurasiMagang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereFinalLaporan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereIdCardPath($value) <-- [TELAH DITAMBAHKAN]
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereKtm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereMahasiswaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang wherePembimbingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereSertifikat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereSuratBalasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereSuratPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereTanggalMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereTanggalSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereTanggalVerifikasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PengajuanMagang whereVerifiedBy($value)
 * @mixin \Eloquent
 */
class PengajuanMagang extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_magang';

    protected $fillable = [
        'mahasiswa_id',
        'pembimbing_id',
        'surat_permohonan',
        'ktm',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi_magang',
        'bidang_diminati',
        'status',
        'alasan_penolakan',
        'tanggal_verifikasi',
        'verified_by',
        'surat_balasan',
        'id_card_path', // <-- [TELAH DITAMBAHKAN]
        'final_laporan',
        'sertifikat',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_verifikasi' => 'datetime',
        'durasi_magang' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_DITERIMA = 'diterima';
    const STATUS_DITOLAK = 'ditolak';
    const STATUS_SELESAI = 'selesai';

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class)->withDefault(['user' => ['name' => 'Anonim']]);
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }


    public function penilaian()
    {
        return $this->hasMany(Penilaian::class);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDiterima()
    {
        return $this->status === self::STATUS_DITERIMA;
    }

    public function isDitolak()
    {
        return $this->status === self::STATUS_DITOLAK;
    }
}
