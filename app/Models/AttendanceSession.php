<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'mentor_id',
        'location_id',
        'session_name',
        'session_date',
        'current_token',
        'expires_at',
        'started_at',
        'ended_at',
        'attendance_start_at',
        'attendance_end_at',
        'rotation_interval_seconds',
        'last_rotated_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'attendance_start_at' => 'datetime',
        'attendance_end_at' => 'datetime',
        'last_rotated_at' => 'datetime',
        'session_date' => 'date',
    ];

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class, 'mentor_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'session_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
