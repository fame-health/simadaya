<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pengajuan_magang_id
 * @property int $pembimbing_id
 * @property int $mahasiswa_id
 * @property \Illuminate\Support\Carbon $tanggal_catatan
 * @property string $judul_catatan
 * @property string $isi_catatan
 * @property string $tipe_catatan
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mahasiswa $mahasiswa
 * @property-read \App\Models\Pembimbing $pembimbing
 * @property-read \App\Models\PengajuanMagang $pengajuanMagang
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereIsiCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereJudulCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereMahasiswaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing wherePembimbingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing wherePengajuanMagangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereTanggalCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereTipeCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatatanPembimbing whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CatatanPembimbing extends Model
{
    use HasFactory;

    protected $table = 'catatan_pembimbing';

    protected $fillable = [
        'pengajuan_magang_id',
        'pembimbing_id',
        'mahasiswa_id',
        'tanggal_catatan',
        
        'judul_catatan',
        'isi_catatan',
        'tipe_catatan',
        'is_read',
    ];

    protected $casts = [
        'tanggal_catatan' => 'datetime',
        'is_read' => 'boolean',
    ];

    const TIPE_FEEDBACK = 'feedback';
    const TIPE_EVALUASI = 'evaluasi';
    const TIPE_BIMBINGAN = 'bimbingan';

    public function pengajuanMagang()
    {
        return $this->belongsTo(PengajuanMagang::class);
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
