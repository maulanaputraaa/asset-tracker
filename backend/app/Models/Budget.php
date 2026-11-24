<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Concerns\BelongsToTenant;

class Budget extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'site_id', 'name', 'allocated_amount', 'start_date', 'end_date'];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
}
