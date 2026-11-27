<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['asset_id', 'name', 'interval_days', 'last_run_date', 'next_due_date'];

    protected $casts = [
        'last_run_date' => 'date',
        'next_due_date' => 'date',
    ];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
