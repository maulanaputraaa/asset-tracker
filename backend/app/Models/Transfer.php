<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\BelongsToTenant;

class Transfer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'asset_id', 'from_site_id', 'to_site_id',
        'to_location_id', 'requested_by', 'status', 'reason',
        'planned_date', 'dispatched_at', 'received_at',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function fromSite(): BelongsTo { return $this->belongsTo(Site::class, 'from_site_id'); }
    public function toSite(): BelongsTo { return $this->belongsTo(Site::class, 'to_site_id'); }
    public function toLocation(): BelongsTo { return $this->belongsTo(Location::class, 'to_location_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function items(): HasMany { return $this->hasMany(TransferItem::class); }
}
