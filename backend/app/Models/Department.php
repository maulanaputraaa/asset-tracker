<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\BelongsToTenant;

class Department extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function assets(): HasMany { return $this->hasMany(Asset::class); }
}
