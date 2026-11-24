<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    // Konvensi Laravel: boot{NamaTrait} dipanggil otomatis, tidak menimpa booted() milik model
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = self::resolveTenantId();
            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        static::creating(function (Model $model) {
            $tenantId = self::resolveTenantId();
            if ($tenantId && empty($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    protected static function resolveTenantId(): ?int
    {
        // 1. Dari user yang login (web/API Sanctum)
        if (Auth::check() && Auth::user()->tenant_id) {
            return Auth::user()->tenant_id;
        }

        // 2. Dari header X-Tenant-ID (mobile/API)
        if (request()->hasHeader('X-Tenant-ID')) {
            return (int) request()->header('X-Tenant-ID');
        }

        // 3. Dari subdomain (jika pakai subdomain routing)
        if (config('app.tenant_subdomain') && request()->hasHeader('X-Tenant-Slug')) {
            return \App\Models\Tenant::where('slug', request()->header('X-Tenant-Slug'))->value('id');
        }

        return null;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    // Helper untuk query tanpa scope (Super Admin / system job)
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}