<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'work_order_id', 'category', 'amount',
        'currency', 'transaction_date', 'description', 'invoice_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function workOrder(): BelongsTo { return $this->belongsTo(WorkOrder::class); }
}
