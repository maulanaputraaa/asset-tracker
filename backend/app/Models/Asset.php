<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use App\Concerns\BelongsToTenant;

class Asset extends Model
{
    use HasFactory, BelongsToTenant, LogsActivity, SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'category_id', 'vendor_id', 'department_id',
        'site_id', 'location_id', 'asset_code', 'name', 'brand', 'model',
        'serial_number', 'status', 'purchase_date', 'purchase_cost',
        'warranty_expiry_date', 'salvage_value', 'specifications',
        'photo', 'documents',
    ];

    protected $casts = [
        'specifications' => 'array',
        'documents' => 'array',
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $asset) => $asset->uuid ??= (string) Str::uuid());
    }

    // FR-10.2 audit diff JSON
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontLogEmptyChanges();
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function category(): BelongsTo { return $this->belongsTo(AssetCategory::class, 'category_id'); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }

    // FR-3.6 hierarki induk-komponen
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'asset_components', 'child_asset_id', 'parent_asset_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'asset_components', 'parent_asset_id', 'child_asset_id');
    }

    public function assignments(): HasMany { return $this->hasMany(AssetAssignment::class); }
    public function transfers(): HasMany { return $this->hasMany(Transfer::class); }
    public function workOrders(): HasMany { return $this->hasMany(WorkOrder::class); }
    public function schedules(): HasMany { return $this->hasMany(MaintenanceSchedule::class); }
    public function costEntries(): HasMany { return $this->hasMany(CostEntry::class); }
}