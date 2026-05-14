<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Pelanggan' }} — SAB Merden</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[Inter] bg-slate-50 antialiased w-full overflow-x-hidden min-h-screen flex flex-col">

    {{-- Topbar Pelanggan --}}
    <header class="bg-white border-b border-slate-100 shadow-sm sticky top-0 z-10 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-app-logo size="sm" :dark="false" />
                <span class="text-slate-300 mx-1">|</span>
                <span class="text-slate-500 text-sm hidden sm:inline">Portal Pelanggan</span>
            </div>

            <div class="flex items-center gap-3">
                {{-- Nav --}}
                <nav class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('portal.dashboard') }}"
                       class="text-sm px-3 py-1.5 rounded-lg transition
                              {{ request()->routeIs('portal.dashboard') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-500 hover:text-slate-700' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('portal.tagihan.index') }}"
                       class="text-sm px-3 py-1.5 rounded-lg transition
                              {{ request()->routeIs('portal.tagihan.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-500 hover:text-slate-700' }}">
                        Tagihan
                    </a>
                    <a href="{{ route('portal.riwayat.index') }}"
                       class="text-sm px-3 py-1.5 rounded-lg transition
                              {{ request()->routeIs('portal.riwayat.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-500 hover:text-slate-700' }}">
                        Riwayat
                    </a>
                </nav>

                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'P', 0, 1)) }}
                    </div>
                    <span class="text-sm text-slate-600 hidden md:inline">{{ auth()->user()->name ?? '' }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-slate-400 hover:text-red-500 transition ml-1">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success') || session('error'))
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 pt-4">
            @if(session('success'))
                <div class="alert-success text-sm">✓ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-error text-sm mt-2">⚠ {{ session('error') }}</div>
            @endif
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 py-6">
        {{ $slot }}
    </main>

    <footer class="w-full border-t border-slate-100 mt-8 py-4 text-center text-xs text-slate-400 bg-white">
        © {{ date('Y') }} SAB Merden — Sistem Pengelolaan Air Bersih Desa Merden
    </footer>

    {{ $scripts ?? '' }}
</body>
</html>
