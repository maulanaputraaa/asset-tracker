<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    protected $fillable = ['workflow_id', 'step_number', 'approver_type', 'approver_value', 'min_amount'];

    protected $casts = ['min_amount' => 'decimal:2'];

    public function workflow(): BelongsTo { return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id'); }
}
