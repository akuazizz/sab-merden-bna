<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SAB Merden' }} — Sistem Pengelolaan Air Bersih</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[Inter] bg-white antialiased w-full overflow-x-hidden">

<div class="min-h-screen w-full">

    {{-- Topbar --}}
    <header class="bg-white border-b border-slate-100 shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 h-16 flex items-center justify-between">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <x-app-logo size="sm" :dark="false" />
            </a>

            {{-- Nav kanan --}}
            <nav class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition-colors border border-slate-200/60">Beranda</a>
                @auth
                    <a href="{{ route('portal.dashboard') }}" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-[#0f2744] text-white mt-16 pb-6">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 py-12 grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <x-app-logo size="sm" :dark="true" />
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                    Sistem Pengelolaan Air Bersih Desa Merden yang akurat, modern, dan terintegrasi.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Kontak</h4>
                <div class="space-y-2 text-sm text-slate-400 leading-relaxed">
                    <p>Desa Merden, Kecamatan Purwanegara</p>
                    <p>Banjarnegara, Jawa Tengah</p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Tautan</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('login') }}" class="hover:text-blue-400 transition-colors">Login Admin / Portal</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 sm:px-8 mt-4 pt-6 border-t border-white/10 text-center text-xs text-slate-500">
            © {{ date('Y') }} SAB Merden. Semua hak dilindungi.
        </div>
    </footer>
</div>

</body>
</html>
