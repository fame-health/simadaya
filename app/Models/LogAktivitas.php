<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $activity
 * @property string $description
 * @property string $ip_address
 * @property string $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogAktivitas whereUserId($value)
 * @mixin \Eloquent
 */
class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'activity',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
