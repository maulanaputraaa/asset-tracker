<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\BelongsToTenant;

class StockOpname extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'site_id', 'name', 'status', 'started_at', 'completed_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function items(): HasMany { return $this->hasMany(StockOpnameItem::class); }
}
