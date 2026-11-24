<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    protected $fillable = ['transfer_id', 'asset_id', 'condition_status'];

    public function transfer(): BelongsTo { return $this->belongsTo(Transfer::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
