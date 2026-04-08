<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SAB Merden' }} — Sistem Pengelolaan Air Bersih</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="h-full bg-slate-50 font-[Inter]">

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

<div class="flex h-full min-h-screen">

    {{-- ══════════════════════════════════════ SIDEBAR ══════ --}}
    <aside id="sidebar"
           class="fixed top-0 left-0 h-full w-64 bg-[#0f2744] flex flex-col z-30
                  transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <div class="w-9 h-9 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 2C8 7 4 10.5 4 14a8 8 0 0016 0c0-3.5-4-7-8-12z"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-tight">SAB Merden</p>
                <p class="text-slate-400 text-xs">Admin Panel</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-2">Menu</p>

            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                {{-- Dashboard Icon --}}
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.pelanggan.index') }}"
               class="nav-item {{ request()->routeIs('admin.pelanggan.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Pelanggan
            </a>

            <a href="{{ route('admin.meteran.index') }}"
               class="nav-item {{ request()->routeIs('admin.meteran.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Meteran
            </a>

            <a href="{{ route('admin.tagihan.index') }}"
               class="nav-item {{ request()->routeIs('admin.tagihan.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Tagihan
            </a>

            <a href="#"
               class="nav-item {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Pembayaran
            </a>

            <a href="#" class="nav-item">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Laporan
            </a>

        </nav>

        {{-- User + Logout --}}
        <div class="px-3 py-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-4 py-2.5 mb-1">
                <div class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name ?? '' }}</p>
                    <p class="text-slate-400 text-xs">Administrator</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="nav-item w-full text-red-400 hover:text-red-300 hover:bg-red-500/10">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════════════════════════════ MAIN ══════ --}}
    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

        {{-- Topbar --}}
        <header class="bg-white border-b border-slate-100 sticky top-0 z-10 shadow-sm">
            <div class="flex items-center justify-between px-4 sm:px-6 h-16">

                {{-- Mobile hamburger --}}
                <button onclick="toggleSidebar()"
                        class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page title --}}
                <h1 class="text-slate-800 font-semibold text-base sm:text-lg hidden sm:block">
                    {{ $pageTitle ?? 'Dashboard' }}
                </h1>

                {{-- Right side --}}
                <div class="flex items-center gap-3 ml-auto">
                    <span class="hidden md:block text-sm text-slate-400">
                        {{ now()->translatedFormat('l, d M Y') }}
                    </span>
                    <span class="badge-blue text-xs">Admin</span>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success') || session('error') || session('warning'))
            <div class="px-4 sm:px-6 pt-4">
                @if (session('success'))
                    <div class="alert-success">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-error mt-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('warning'))
                    <div class="alert-warning mt-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        {{ session('warning') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Main content --}}
        <main class="flex-1 px-4 sm:px-6 py-6">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="px-6 py-3 border-t border-slate-100 bg-white">
            <p class="text-xs text-slate-400 text-center">
                © {{ date('Y') }} SAB Merden – Sistem Pengelolaan Air Bersih Desa Merden
            </p>
        </footer>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    const isHidden = sidebar.classList.contains('-translate-x-full');
    sidebar.classList.toggle('-translate-x-full', !isHidden);
    overlay.classList.toggle('hidden', !isHidden);
}
</script>

{{ $scripts ?? '' }}
</body>
</html>
