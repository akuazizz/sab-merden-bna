<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureHasRole — middleware sederhana berbasis Spatie Permission.
 *
 * Cara pakai di route:
 *   ->middleware('role:admin')
 *   ->middleware('role:pelanggan')
 *   ->middleware('role:admin|pelanggan')
 *
 * Jika user tidak login → redirect login.
 * Jika login tapi tidak punya role → abort 403.
 */
class EnsureHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_FORBIDDEN, 'Akses ditolak. Anda tidak memiliki hak untuk halaman ini.');
    }
}
