<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = ['stock_opname_id', 'asset_id', 'status', 'scanned_location_id'];

    public function stockOpname(): BelongsTo { return $this->belongsTo(StockOpname::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function scannedLocation(): BelongsTo { return $this->belongsTo(Location::class, 'scanned_location_id'); }
}
