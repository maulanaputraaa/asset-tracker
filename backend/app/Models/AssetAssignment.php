<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    protected $fillable = ['asset_id', 'user_id', 'checked_out_at', 'due_date', 'checked_in_at', 'status'];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'due_date' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
