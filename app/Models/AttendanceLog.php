<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    const STATUS_PRESENT = 'present';
    const STATUS_PERMIT = 'permit';
    const STATUS_SICK = 'sick';
    const STATUS_ALPHA = 'alpha';
    const STATUS_PENDING_PERMIT = 'pending_permit';
    const STATUS_PENDING_SICK = 'pending_sick';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'session_id',
        'student_id',
        'token',
        'scan_time',
        'browser',
        'ip_address',
        'device_hash',
        'status',
        'document_path',
        'reason',
        'failure_reason',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'student_id');
    }
}
