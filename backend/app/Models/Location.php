<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\BelongsToTenant;

class Location extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'site_id', 'name', 'description'];

    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function assets(): HasMany { return $this->hasMany(Asset::class); }
}
