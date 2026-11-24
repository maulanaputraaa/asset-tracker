<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Concerns\BelongsToTenant;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'site_id', 'name', 'email', 'password',
        'role', 'employee_id', 'phone', 'avatar',
        'is_active', 'notification_preferences', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function site(): BelongsTo { return $this->belongsTo(Site::class); }

    public function assignments(): HasMany { return $this->hasMany(AssetAssignment::class); }
    public function workOrders(): HasMany { return $this->hasMany(WorkOrder::class, 'technician_id'); }
    public function transferRequests(): HasMany { return $this->hasMany(Transfer::class, 'requested_by'); }
    public function approvalActions(): HasMany { return $this->hasMany(ApprovalAction::class, 'approver_id'); }
}
