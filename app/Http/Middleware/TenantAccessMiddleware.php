<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $tenant = app('tenant');

        // Check if user is a super-admin
        if ($user->hasRole('super-admin')) {
            // Super-admin can access any tenant
            return $next($request);
        }

        // Check if user belongs to this tenant
        if ($user->tenant_id !== $tenant->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have access to this tenant.',
                ], 403);
            }

            abort(403, 'You do not have access to this tenant.');
        }

        return $next($request);
    }
}
