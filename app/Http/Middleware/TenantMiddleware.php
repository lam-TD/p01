<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the domain from the request
        $host = $request->getHost();

        // Check if tenant exists
        $tenant = Tenant::where('domain', $host)
            ->where('status', true)
            ->first();

        if (! $tenant) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tenant not found'], 404);
            }
            abort(404, 'Tenant not found');
        }

        // Set current tenant for the application
        app()->instance('tenant', $tenant);

        // Switch to tenant database
        $this->tenantService->switchToTenant($tenant);

        // If authenticated user is not super-admin, check tenant access
        if (Auth::check() && ! Auth::user()->hasRole('super-admin') && Auth::user()->tenant_id !== $tenant->id) {
            // User is trying to access a tenant they don't belong to
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have access to this tenant'], 403);
            }

            return redirect()->route('login')->with('error', 'You do not have access to this domain.');
        }

        $response = $next($request);

        // Switch back to central database
        $this->tenantService->switchToCentral();

        return $response;
    }
}
