<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\BelongsToTenant;

class WorkOrder extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'asset_id', 'technician_id', 'type', 'status',
        'title', 'description', 'checklist', 'estimated_cost', 'actual_cost',
        'estimated_hours', 'due_date', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'checklist' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
    public function items(): HasMany { return $this->hasMany(WorkOrderItem::class); }
}
