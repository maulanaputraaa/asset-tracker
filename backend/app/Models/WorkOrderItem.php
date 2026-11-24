<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends Model
{
    protected $fillable = ['work_order_id', 'item_name', 'qty', 'unit_price', 'total_price'];

    protected $casts = ['unit_price' => 'decimal:2', 'total_price' => 'decimal:2'];

    public function workOrder(): BelongsTo { return $this->belongsTo(WorkOrder::class); }
}
