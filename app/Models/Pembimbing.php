<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $nip
 * @property string $jabatan
 * @property string $bidang_keahlian
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CatatanPembimbing> $catatanPembimbing
 * @property-read int|null $catatan_pembimbing_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PengajuanMagang> $mahasiswaBimbingan
 * @property-read int|null $mahasiswa_bimbingan_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing whereBidangKeahlian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembimbing whereUserId($value)
 * @mixin \Eloquent
 */
class Pembimbing extends Model
{
    use HasFactory;

    protected $table = 'pembimbing';

    protected $fillable = [
        'user_id',
        'nip',
        'jabatan',
        'bidang_keahlian',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mahasiswaBimbingan()
    {
        return $this->hasMany(PengajuanMagang::class);
    }

    public function catatanPembimbing()
    {
        return $this->hasMany(CatatanPembimbing::class);
    }
}
