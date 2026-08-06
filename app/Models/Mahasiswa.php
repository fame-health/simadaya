<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Mahasiswa
 *
 * @property int $id
 * @property int $user_id
 * @property string $nim
 * @property string $universitas
 * @property string $fakultas
 * @property string $jurusan
 * @property int $semester
 * @property float $ipk
 * @property string $alamat
 * @property \Illuminate\Support\Carbon $tanggal_lahir
 * @property string $jenis_kelamin
 * @property string|null $profile_photo_path  <-- [TELAH DITAMBAHKAN]
 * @property string|null $nomor_hp            <-- [TELAH DITAMBAHKAN]
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PengajuanMagang> $pengajuan
 * @property-read int|null $pengajuan_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penilaian> $penilaian
 * @property-read int|null $penilaian_count
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereFakultas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereIpk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereJurusan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereNim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereNomorHp($value)          <-- [TELAH DITAMBAHKAN]
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereProfilePhotoPath($value) <-- [TELAH DITAMBAHKAN]
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereUniversitas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mahasiswa whereUserId($value)
 * @mixin \Eloquent
 */
class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id',
        'nim',
        'universitas',
        'fakultas',
        'jurusan',
        'semester',
        'ipk',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'profile_photo_path', // <-- [TELAH DITAMBAHKAN]
        'nomor_hp',           // <-- [TELAH DITAMBAHKAN]
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'ipk' => 'decimal:2',
    ];

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke pengajuan magang
     */
    public function pengajuan()
    {
        return $this->hasMany(PengajuanMagang::class);
    }

    /**
     * Alias relasi ke pengajuan magang untuk kompatibilitas
     */
    public function pengajuanMagang()
    {
        return $this->hasMany(PengajuanMagang::class);
    }

    /**
     * Relasi ke penilaian
     */
    public function penilaian()
    {
        return $this->hasMany(Penilaian::class);
    }
}
