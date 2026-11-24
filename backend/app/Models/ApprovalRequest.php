<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Concerns\BelongsToTenant;

class ApprovalRequest extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'approvable_type', 'approvable_id', 'requester_id', 'current_step', 'status'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function approvable(): MorphTo { return $this->morphTo(); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requester_id'); }
    public function actions(): HasMany { return $this->hasMany(ApprovalAction::class); }
}
