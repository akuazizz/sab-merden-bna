<x-layouts.guest>
    <x-slot name="title">Beranda</x-slot>

    {{-- ══ HERO ══════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0f2744] via-[#1a4080] to-[#2563eb] min-h-[92vh] flex items-center">

        {{-- Background decorative circles --}}
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-[-15%] left-[-5%] w-[400px] h-[400px] bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Wave bottom --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L48 105C96 90 192 60 288 52.5C384 45 480 60 576 67.5C672 75 768 75 864 67.5C960 60 1056 45 1152 52.5C1248 60 1344 90 1392 105L1440 120V120H0Z" fill="#f8fafc"/>
            </svg>
        </div>

        <div class="w-full mx-auto px-6 py-24 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- Left: Text --}}
            <div>
                <div class="inline-flex items-center gap-2 bg-blue-400/20 border border-blue-400/30 rounded-full px-4 py-1.5 mb-6">
                    <div class="w-1.5 h-1.5 bg-blue-300 rounded-full animate-pulse"></div>
                    <span class="text-blue-200 text-xs font-medium">Sistem Resmi Desa Merden</span>
                </div>

                <h1 class="text-4xl sm:text-5xl font-bold text-white leading-tight mb-5">
                    Sistem Pengelolaan<br>
                    <span class="text-blue-300">Air Bersih</span><br>
                    Desa Merden
                </h1>

                <p class="text-blue-100 text-lg leading-relaxed mb-8 max-w-md">
                    Akurat, modern, dan terintegrasi dengan pembayaran digital. Pantau tagihan air Anda kapan saja dan di mana saja.
                </p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 bg-white text-blue-700 font-semibold
                              rounded-xl px-6 py-3 hover:bg-blue-50 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Login Sekarang
                    </a>
                    <a href="#cara-kerja"
                       class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white font-semibold
                              rounded-xl px-6 py-3 hover:bg-white/20 transition-all duration-200">
                        Pelajari Cara Kerja
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                </div>

                {{-- Stats --}}
                <div class="flex gap-8 mt-10 pt-8 border-t border-white/20">
                    <div>
                        <p class="text-3xl font-bold text-white">100%</p>
                        <p class="text-blue-200 text-sm">Akurat</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-white">24/7</p>
                        <p class="text-blue-200 text-sm">Akses Tagihan</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-white">Online</p>
                        <p class="text-blue-200 text-sm">Pembayaran</p>
                    </div>
                </div>
            </div>

            {{-- Right: Illustration --}}
            <div class="hidden lg:flex justify-center items-center">
                <div class="relative">
                    {{-- Glowing water drop SVG --}}
                    <svg width="320" height="380" viewBox="0 0 320 380" fill="none" xmlns="http://www.w3.org/2000/svg">
                        {{-- Shadow --}}
                        <ellipse cx="160" cy="370" rx="80" ry="10" fill="rgba(0,0,0,0.2)"/>

                        {{-- Main water drop --}}
                        <path d="M160 20 C160 20, 40 160, 40 240 C40 306 95 360 160 360 C225 360 280 306 280 240 C280 160 160 20 160 20Z"
                              fill="url(#dropGrad)" opacity="0.9"/>

                        {{-- Highlight --}}
                        <path d="M120 160 C120 160, 90 200, 90 230 C90 260 110 275 125 270 C110 255 108 235 115 215 C122 195 135 175 148 160Z"
                              fill="white" opacity="0.15"/>

                        {{-- Inner reflection --}}
                        <ellipse cx="130" cy="200" rx="15" ry="30" fill="white" opacity="0.2" transform="rotate(-20, 130, 200)"/>

                        {{-- Ripple rings --}}
                        <ellipse cx="160" cy="340" rx="50" ry="12" stroke="white" stroke-width="1.5" opacity="0.2" fill="none"/>
                        <ellipse cx="160" cy="340" rx="70" ry="18" stroke="white" stroke-width="1" opacity="0.1" fill="none"/>

                        {{-- Check mark inside --}}
                        <circle cx="160" cy="260" r="40" fill="white" opacity="0.15"/>
                        <path d="M144 260 L155 271 L176 249" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>

                        <defs>
                            <linearGradient id="dropGrad" x1="40" y1="20" x2="280" y2="360" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#60a5fa"/>
                                <stop offset="100%" stop-color="#1d4ed8"/>
                            </linearGradient>
                        </defs>
                    </svg>

                    {{-- Floating cards --}}
                    <div class="absolute -right-4 top-16 bg-white rounded-xl shadow-xl px-4 py-3 text-xs w-44">
                        <p class="text-slate-400 mb-0.5">Tagihan Bulan Ini</p>
                        <p class="font-bold text-slate-800 text-base">Rp 45.000</p>
                        <span class="badge-green">Belum Bayar</span>
                    </div>
                    <div class="absolute -left-4 bottom-20 bg-white rounded-xl shadow-xl px-4 py-3 text-xs w-44">
                        <p class="text-slate-400 mb-0.5">Pemakaian</p>
                        <p class="font-bold text-slate-800 text-base">18 m³</p>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full w-3/4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ FEATURES ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-slate-50">
        <div class="w-full mx-auto px-6">
            <div class="text-center mb-14">
                <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider mb-2">Fitur Sistem</p>
                <h2 class="text-3xl font-bold text-slate-800">Semua yang Anda Butuhkan</h2>
                <p class="text-slate-500 mt-3 max-w-lg mx-auto">Dari pendataan pelanggan hingga pembayaran online, semua tersedia dalam satu platform.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'bg-blue-100 text-blue-600', 'title' => 'Manajemen Pelanggan', 'desc' => 'Kelola data pelanggan, status langganan, dan riwayat lengkap.'],
                    ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'color' => 'bg-green-100 text-green-600', 'title' => 'Pencatatan Meter', 'desc' => 'Input meter bulanan dengan validasi otomatis dan foto pendukung.'],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => 'bg-yellow-100 text-yellow-600', 'title' => 'Tagihan Detail', 'desc' => 'Tagihan otomatis ter-generate dan tersedia kapan saja.'],
                    ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'bg-purple-100 text-purple-600', 'title' => 'Pembayaran Online', 'desc' => 'Bayar tagihan via transfer bank, e-wallet, dan QRIS.'],
                ] as $fitur)
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                        <div class="w-11 h-11 {{ $fitur['color'] }} rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fitur['icon'] }}"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 mb-2">{{ $fitur['title'] }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">{{ $fitur['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ HOW IT WORKS ══════════════════════════════════════════════════════════ --}}
    <section id="cara-kerja" class="py-20 bg-white">
        <div class="w-full mx-auto px-6">
            <div class="text-center mb-14">
                <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider mb-2">Cara Kerja</p>
                <h2 class="text-3xl font-bold text-slate-800">Mudah, Cepat, Akurat</h2>
            </div>

            <div class="relative">
                {{-- Connector line (desktop) --}}
                <div class="hidden lg:block absolute top-10 left-[12.5%] right-[12.5%] h-0.5 bg-blue-100"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach([
                        ['step' => '1', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'title' => 'Catat Meter', 'desc' => 'Petugas mencatat angka meteran air setiap bulan.'],
                        ['step' => '2', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'title' => 'Tagihan Otomatis', 'desc' => 'Sistem generate tagihan berdasarkan pemakaian.'],
                        ['step' => '3', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'title' => 'Bayar Online', 'desc' => 'Pelanggan bayar lewat web atau mobile banking.'],
                        ['step' => '4', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Status Real-time', 'desc' => 'Status lunas langsung terupdate secara otomatis.'],
                    ] as $step)
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-5">
                                <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                                    </svg>
                                </div>
                                <span class="absolute -top-2 -right-2 w-6 h-6 bg-white border-2 border-blue-200 text-blue-700 rounded-full text-xs font-bold flex items-center justify-center shadow-sm">
                                    {{ $step['step'] }}
                                </span>
                            </div>
                            <h3 class="font-semibold text-slate-800 mb-2">{{ $step['title'] }}</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ══ CTA ══════════════════════════════════════════════════════════════════ --}}
    <section class="py-16 bg-gradient-to-r from-blue-600 to-blue-700">
        <div class="max-w-2xl mx-auto px-6 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Siap memulai?</h2>
            <p class="text-blue-100 mb-8">Login ke akun Anda untuk mulai mengelola tagihan air secara digital.</p>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 bg-white text-blue-700 font-semibold rounded-xl px-8 py-3.5
                      hover:bg-blue-50 transition-all duration-200 shadow-lg hover:shadow-xl">
                Masuk ke Dashboard
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </section>

</x-layouts.guest>
