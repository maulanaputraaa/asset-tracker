<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\BelongsToTenant;

class ApprovalWorkflow extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'request_type', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function steps(): HasMany { return $this->hasMany(ApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_number'); }
}
