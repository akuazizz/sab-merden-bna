<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — SAB Merden</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[Inter] antialiased h-screen overflow-hidden">

<div class="flex h-full">

    {{-- ── LEFT: Branding ─────────────────────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-[55%] relative flex-col justify-between
                bg-gradient-to-br from-[#0c1f3a] via-[#0f2e5a] to-[#1a4fa6] overflow-hidden">

        {{-- Decorative blobs --}}
        <div class="absolute top-[-20%] right-[-10%] w-[500px] h-[500px] bg-blue-400/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-15%] left-[-10%] w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-3xl"></div>

        {{-- Top: Logo --}}
        <div class="relative z-10 px-12 pt-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 2C8 7 4 10.5 4 14a8 8 0 0016 0c0-3.5-4-7-8-12z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-bold text-lg leading-tight">SAB Merden</p>
                    <p class="text-blue-300 text-xs">Sistem Air Bersih</p>
                </div>
            </div>
        </div>

        {{-- Center: Main content --}}
        <div class="relative z-10 px-12 py-8">
            <h2 class="text-5xl font-bold text-white leading-tight mb-4">
                Kelola Air<br>
                <span class="text-blue-300">Lebih Cerdas</span>
            </h2>
            <p class="text-blue-100 text-lg leading-relaxed mb-10">
                Sistem pengelolaan air bersih Desa Merden yang akurat, terintegrasi, dan mudah digunakan.
            </p>

            {{-- Feature pills --}}
            <div class="flex flex-col gap-3">
                @foreach(['✓  Pencatatan meter digital bulanan', '✓  Tagihan otomatis & akurat', '✓  Pembayaran online terintegrasi', '✓  Monitoring real-time'] as $feat)
                    <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-2.5 backdrop-blur-sm border border-white/10">
                        <span class="text-white text-sm">{{ $feat }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Bottom: Wave --}}
        <div class="relative z-10">

            <svg viewBox="0 0 900 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 50 Q225 0 450 50 Q675 100 900 50 L900 100 L0 100Z" fill="white" opacity="0.05"/>
                <path d="M0 70 Q225 20 450 70 Q675 120 900 70 L900 100 L0 100Z" fill="white" opacity="0.05"/>
            </svg>
        </div>
    </div>

    {{-- ── RIGHT: Login Form ───────────────────────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center bg-white px-6 py-10 overflow-y-auto">
        <div class="w-full max-w-sm">

            {{-- Mobile logo (only shows on mobile) --}}
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 2C8 7 4 10.5 4 14a8 8 0 0016 0c0-3.5-4-7-8-12z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-800">SAB Merden</span>
            </div>

            <h1 class="text-2xl font-bold text-slate-800 mb-1">Selamat Datang Kembali</h1>
            <p class="text-slate-500 text-sm mb-8">Masuk untuk mengakses dashboard pengelolaan air bersih.</p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="alert-success mb-5 text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="form-label">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="form-input @error('email') border-red-400 focus:ring-red-300 @enderror"
                           placeholder="admin@sabmerden.desa.id" required autocomplete="email" autofocus>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="form-label mb-0">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-700">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <input id="password" type="password" name="password"
                               class="form-input pr-10 @error('password') border-red-400 @enderror"
                               placeholder="••••••••" required autocomplete="current-password">
                        {{-- Eye toggle --}}
                        <button type="button" onclick="togglePwd()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg id="eye-on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-off" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                    <label for="remember_me" class="text-sm text-slate-600 cursor-pointer">Ingat saya</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full btn-primary py-3 text-base font-semibold mt-2 shadow-lg shadow-blue-200">
                    Masuk
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-8">
                Hanya untuk pengguna terdaftar Sistem SAB Desa Merden
            </p>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    const input  = document.getElementById('password');
    const eyeOn  = document.getElementById('eye-on');
    const eyeOff = document.getElementById('eye-off');
    const isPass = input.type === 'password';
    input.type  = isPass ? 'text' : 'password';
    eyeOn.classList.toggle('hidden', isPass);
    eyeOff.classList.toggle('hidden', !isPass);
}
</script>
</body>
</html>
