<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->resolveTenant($request);

        if (! $tenantId) {
            return response()->json([
                'message' => 'Tenant context required. Provide X-Tenant-ID header or login.'
            ], 400);
        }

        // Set ke request agar bisa dipakai controller/service
        $request->merge(['tenant_id' => $tenantId]);

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?int
    {
        // 1. Header X-Tenant-ID (mobile/API)
        if ($request->hasHeader('X-Tenant-ID')) {
            return (int) $request->header('X-Tenant-ID');
        }

        // 2. User login (Sanctum)
        if ($request->user() && $request->user()->tenant_id) {
            return $request->user()->tenant_id;
        }

        // 3. Subdomain (opsional)
        if (config('app.tenant_subdomain')) {
            $slug = $request->header('X-Tenant-Slug') ?? $this->extractSubdomain($request);
            if ($slug) {
                return \App\Models\Tenant::where('slug', $slug)->value('id');
            }
        }

        return null;
    }

    protected function extractSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $baseDomain = config('app.base_domain');
        if ($baseDomain && str_ends_with($host, '.' . $baseDomain)) {
            return str_replace('.' . $baseDomain, '', $host);
        }
        return null;
    }
}