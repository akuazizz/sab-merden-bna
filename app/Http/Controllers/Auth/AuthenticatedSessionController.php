<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // ── Role-based redirect ───────────────────────────────────────────────
        // redirect()->intended() tetap dipakai sebagai mekanisme utama:
        // jika user mengakses URL langsung sebelum login (mis. /admin/pelanggan),
        // mereka akan dikembalikan ke URL itu setelah login.
        // Default fallback ditentukan berdasarkan role.

        $user = $request->user();

        $defaultRedirect = match (true) {
            $user->hasRole('admin')     => route('admin.dashboard'),
            $user->hasRole('pelanggan') => route('portal.dashboard'),
            default                     => route('dashboard'),  // fallback Breeze
        };

        return redirect()->intended($defaultRedirect);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
