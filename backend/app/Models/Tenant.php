<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'logo', 'status', 'settings'];

    protected $casts = ['settings' => 'array'];

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function sites(): HasMany { return $this->hasMany(Site::class); }
    public function categories(): HasMany { return $this->hasMany(AssetCategory::class, 'tenant_id'); }
    public function vendors(): HasMany { return $this->hasMany(Vendor::class); }
    public function departments(): HasMany { return $this->hasMany(Department::class); }
    public function assets(): HasMany { return $this->hasMany(Asset::class); }
    public function workflows(): HasMany { return $this->hasMany(ApprovalWorkflow::class, 'tenant_id'); }
    public function budgets(): HasMany { return $this->hasMany(Budget::class); }
}
