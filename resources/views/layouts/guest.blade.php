<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SAB Merden' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-[Inter] bg-white antialiased m-0 overflow-x-hidden">

    <div class="min-h-screen w-full">

        <header class="bg-white border-b border-slate-100 shadow-sm sticky top-0 z-10">
            <div class="w-full px-6 h-16 flex items-center justify-between">

                <a href="{{ url('/') }}" class="flex items-center">
                    <x-app-logo size="sm" :dark="false" />
                </a>

                <nav class="flex items-center gap-6">
                    <a href="{{ url('/') }}" class="text-sm text-slate-500 hover:text-blue-600 transition">
                        Beranda
                    </a>

                    @auth
                        <a href="{{ route('portal.dashboard') }}"
                            class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-blue-600 transition">
                            Masuk
                        </a>
                    @endauth
                </nav>

            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-[#0f2744] text-white mt-16">
            <div class="w-full px-6 py-10 grid grid-cols-1 sm:grid-cols-3 gap-8">

                <div>
                    <div class="mb-3">
                        <x-app-logo size="sm" :dark="true" />
                    </div>

                    <p class="text-slate-400 text-sm">
                        Sistem Pengelolaan Air Bersih Desa Merden yang akurat dan modern.
                    </p>
                </div>

                <div>
                    <h4 class="font-semibold text-sm mb-3">Kontak</h4>
                    <p class="text-slate-400 text-sm">Desa Merden</p>
                    <p class="text-slate-400 text-sm">Banjarnegara</p>
                </div>

                <div>
                    <h4 class="font-semibold text-sm mb-3">Tautan</h4>
                    <a href="{{ route('login') }}" class="text-slate-400 text-sm hover:text-white">
                        Login
                    </a>
                </div>

            </div>

            <div class="border-t border-white/10 py-4 text-center text-xs text-slate-500">
                © {{ date('Y') }} SAB Merden
            </div>
        </footer>

    </div>

</body>

</html>