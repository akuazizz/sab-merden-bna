<x-layouts.admin>
    <x-slot name="pageTitle">{{ isset($pelanggan) ? 'Edit Pelanggan' : 'Daftar Pelanggan Baru' }}</x-slot>

    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.pelanggan.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-slate-800 text-lg">
                    {{ isset($pelanggan) ? 'Edit Data Pelanggan' : 'Daftar Pelanggan Baru' }}
                </h2>
                <p class="text-sm text-slate-400">{{ isset($pelanggan) ? $pelanggan->nomor_pelanggan : 'Isi form berikut dengan lengkap' }}</p>
            </div>
        </div>

        <div class="section-card">
            <form method="POST"
                  action="{{ isset($pelanggan) ? route('admin.pelanggan.update', $pelanggan->id) : route('admin.pelanggan.store') }}">
                @csrf
                @if(isset($pelanggan)) @method('PUT') @endif

                <div class="p-6 space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Nama --}}
                        <div class="sm:col-span-2">
                            <label class="form-label" for="nama">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="nama" name="nama" value="{{ old('nama', $pelanggan->nama ?? '') }}"
                                   class="form-input @error('nama') border-red-400 @enderror"
                                   placeholder="Nama sesuai KTP">
                            @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- NIK --}}
                        <div>
                            <label class="form-label" for="nik">NIK</label>
                            <input type="text" id="nik" name="nik" value="{{ old('nik', $pelanggan->nik ?? '') }}"
                                   class="form-input font-mono @error('nik') border-red-400 @enderror"
                                   placeholder="16 digit NIK" maxlength="16">
                            @error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Telepon --}}
                        <div>
                            <label class="form-label" for="telepon">Nomor Telepon</label>
                            <input type="tel" id="telepon" name="telepon" value="{{ old('telepon', $pelanggan->telepon ?? '') }}"
                                   class="form-input @error('telepon') border-red-400 @enderror"
                                   placeholder="08xxxxxxxxxx">
                            @error('telepon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="sm:col-span-2">
                            <label class="form-label" for="alamat">Alamat Lengkap <span class="text-red-500">*</span></label>
                            <textarea id="alamat" name="alamat" rows="2" class="form-input resize-none @error('alamat') border-red-400 @enderror"
                                      placeholder="Alamat rumah pelanggan">{{ old('alamat', $pelanggan->alamat ?? '') }}</textarea>
                            @error('alamat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Dusun --}}
                        <div>
                            <label class="form-label" for="dusun">Dusun</label>
                            <input type="text" id="dusun" name="dusun" value="{{ old('dusun', $pelanggan->dusun ?? '') }}"
                                   class="form-input @error('dusun') border-red-400 @enderror"
                                   placeholder="Nama dusun">
                            @error('dusun') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- RT/RW --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" for="rt">RT</label>
                                <input type="text" id="rt" name="rt" value="{{ old('rt', $pelanggan->rt ?? '') }}"
                                       class="form-input" placeholder="001" maxlength="3">
                            </div>
                            <div>
                                <label class="form-label" for="rw">RW</label>
                                <input type="text" id="rw" name="rw" value="{{ old('rw', $pelanggan->rw ?? '') }}"
                                       class="form-input" placeholder="001" maxlength="3">
                            </div>
                        </div>

                        {{-- Akun Login Pelanggan (hanya saat create baru) --}}
                        @if(!isset($pelanggan))
                            <div class="sm:col-span-2">
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl mb-1">
                                    <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1">
                                        🔑 Akun Login Portal Pelanggan
                                    </p>
                                    <p class="text-xs text-blue-600">Pelanggan akan menggunakan email dan password ini untuk login dan membayar tagihan di portal.</p>
                                </div>
                            </div>

                            <div>
                                <label class="form-label" for="email">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email"
                                       value="{{ old('email') }}"
                                       class="form-input @error('email') border-red-400 @enderror"
                                       placeholder="email@contoh.com" required>
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label" for="password">Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="password" id="password" name="password"
                                           class="form-input pr-10 @error('password') border-red-400 @enderror"
                                           placeholder="Min. 8 karakter" required minlength="8" autocomplete="new-password">
                                    <button type="button" onclick="togglePwd('password')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="form-label" for="password_confirmation">Konfirmasi Password <span class="text-red-500">*</span></label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-input"
                                       placeholder="Ulangi password" required minlength="8" autocomplete="new-password">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                    <a href="{{ route('admin.pelanggan.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ isset($pelanggan) ? 'Simpan Perubahan' : 'Daftar Pelanggan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>

<x-slot name="scripts">
<script>
function togglePwd(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</x-slot>
