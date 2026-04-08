<x-layouts.admin>
    <x-slot name="pageTitle">{{ isset($reading) ? 'Koreksi Meteran' : 'Input Meteran' }}</x-slot>

    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.meteran.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-slate-800 text-lg">{{ isset($reading) ? 'Koreksi Data Meter' : 'Input Meter Bulanan' }}</h2>
                <p class="text-sm text-slate-400">{{ isset($reading) ? $reading->pelanggan?->nama . ' – ' . $reading->periode : 'Catat pembacaan meter air pelanggan' }}</p>
            </div>
        </div>

        <div class="section-card">
            <form method="POST"
                  action="{{ isset($reading) ? route('admin.meteran.update', $reading->id) : route('admin.meteran.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if(isset($reading)) @method('PUT') @endif

                <div class="p-6 space-y-5">

                    @if(!isset($reading))
                        {{-- Pelanggan --}}
                        <div>
                            <label class="form-label" for="pelanggan_id">Pelanggan <span class="text-red-500">*</span></label>
                            <select id="pelanggan_id" name="pelanggan_id"
                                    class="form-input @error('pelanggan_id') border-red-400 @enderror"
                                    onchange="fetchSuggestion(this.value)">
                                <option value="">— Pilih Pelanggan —</option>
                                @foreach($pelangganList as $p)
                                    <option value="{{ $p->id }}" {{ old('pelanggan_id', request('pelanggan_id')) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }} ({{ $p->nomor_pelanggan }})
                                    </option>
                                @endforeach
                            </select>
                            @error('pelanggan_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Periode --}}
                        <div>
                            <label class="form-label" for="periode">Periode <span class="text-red-500">*</span></label>
                            <input type="month" id="periode" name="periode"
                                   value="{{ old('periode', now()->format('Y-m')) }}"
                                   class="form-input @error('periode') border-red-400 @enderror">
                            @error('periode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Kubik Awal --}}
                    <div>
                        <label class="form-label" for="kubik_awal">
                            Kubik Awal (m³)
                            <span id="suggestion-badge" class="ml-2 text-xs text-blue-500 hidden"></span>
                        </label>
                        <input type="number" id="kubik_awal" name="kubik_awal" step="0.01" min="0"
                               value="{{ old('kubik_awal', $reading->kubik_awal ?? '') }}"
                               class="form-input font-mono @error('kubik_awal') border-red-400 @enderror"
                               placeholder="0.00">
                        @error('kubik_awal') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Kubik Akhir --}}
                    <div>
                        <label class="form-label" for="kubik_akhir">Kubik Akhir (m³) <span class="text-red-500">*</span></label>
                        <input type="number" id="kubik_akhir" name="kubik_akhir" step="0.01" min="0"
                               value="{{ old('kubik_akhir', $reading->kubik_akhir ?? '') }}"
                               class="form-input font-mono @error('kubik_akhir') border-red-400 @enderror"
                               placeholder="0.00" oninput="hitungPemakaian()">
                        @error('kubik_akhir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Pemakaian Preview --}}
                    <div id="pemakaian-preview" class="hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-center justify-between">
                            <span class="text-sm text-blue-700">Estimasi Pemakaian</span>
                            <span id="pemakaian-val" class="text-xl font-bold text-blue-700">0.00 m³</span>
                        </div>
                    </div>

                    {{-- Foto Meter --}}
                    <div>
                        <label class="form-label" for="foto_meteran">Foto Meteran (Opsional)</label>
                        <input type="file" id="foto_meteran" name="foto_meteran" accept="image/*"
                               class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4
                                      file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700
                                      file:font-medium hover:file:bg-blue-100 transition">
                        @if(isset($reading) && $reading->foto_meteran)
                            <div class="mt-2 flex items-center gap-3">
                                <img src="{{ asset('storage/' . $reading->foto_meteran) }}" alt="Foto meter"
                                     class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                                <p class="text-xs text-slate-400">Foto saat ini. Upload baru untuk mengganti.</p>
                            </div>
                        @endif
                    </div>

                    @if(isset($reading))
                        {{-- Alasan Koreksi --}}
                        <div>
                            <label class="form-label" for="alasan_koreksi">Alasan Koreksi <span class="text-red-500">*</span></label>
                            <textarea id="alasan_koreksi" name="alasan_koreksi" rows="2"
                                      class="form-input resize-none @error('alasan_koreksi') border-red-400 @enderror"
                                      placeholder="Jelaskan alasan koreksi data meteran...">{{ old('alasan_koreksi') }}</textarea>
                            @error('alasan_koreksi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.meteran.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ isset($reading) ? 'Simpan Koreksi' : 'Simpan & Generate Tagihan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

<x-slot name="scripts">
<script>
function hitungPemakaian() {
    const awal  = parseFloat(document.getElementById('kubik_awal')?.value || 0);
    const akhir = parseFloat(document.getElementById('kubik_akhir')?.value || 0);
    const pemakaian = akhir - awal;
    const preview = document.getElementById('pemakaian-preview');
    const val     = document.getElementById('pemakaian-val');
    if (akhir > 0 && awal >= 0) {
        val.textContent = pemakaian.toFixed(2) + ' m³';
        val.className   = 'text-xl font-bold ' + (pemakaian >= 0 ? 'text-blue-700' : 'text-red-600');
        preview.classList.remove('hidden');
    }
}

async function fetchSuggestion(pelangganId) {
    if (!pelangganId) return;
    try {
        const periode = document.getElementById('periode')?.value;
        const resp    = await fetch(`/admin/meteran/suggestion?pelanggan_id=${pelangganId}&periode=${periode}`);
        const data    = await resp.json();
        if (data.kubik_akhir_sebelumnya !== undefined) {
            document.getElementById('kubik_awal').value = data.kubik_akhir_sebelumnya;
            const badge = document.getElementById('suggestion-badge');
            badge.textContent = `Auto-filled dari ${data.periode_sebelumnya}`;
            badge.classList.remove('hidden');
            hitungPemakaian();
        }
    } catch(e) { /* silent */ }
}
</script>
</x-slot>
</x-layouts.admin>
