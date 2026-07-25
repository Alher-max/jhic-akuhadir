<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class ResolveTenantSubdomain
{
    public function handle(Request $request, Closure $next)
    {
        $subdomain = $request->route('subdomain');
        
        if ($subdomain) {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            
            if (!$tenant) {
                abort(404, 'Sekolah / Institusi Tidak Ditemukan');
            }

            // Share the tenant instance globally for the request lifecycle
            app()->instance('tenant', $tenant);
            view()->share('current_tenant', $tenant);
            
            // Forget the subdomain route parameter so it doesn't mess up generated URLs if not needed
            $request->route()->forgetParameter('subdomain');
        }

        return $next($request);
    }
}
