<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyLogbook extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'week_number',
        'start_date',
        'end_date',
        'activities',
        'achievements',
        'problems',
        'attachment',
        'mentor_feedback',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
