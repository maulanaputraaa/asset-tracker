<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\BelongsToTenant;

class AssetCategory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'asset_categories';

    protected $fillable = ['tenant_id', 'name', 'custom_fields'];

    protected $casts = ['custom_fields' => 'array'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function assets(): HasMany { return $this->hasMany(Asset::class, 'category_id'); }
}
