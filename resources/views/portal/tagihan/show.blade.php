<x-layouts.pelanggan>
    <x-slot name="title">Tagihan {{ $tagihan->nomor_tagihan }}</x-slot>

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('portal.tagihan.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="font-bold text-slate-800">Tagihan Periode {{ $tagihan->periode }}</h2>
            <p class="text-sm text-slate-400 font-mono">{{ $tagihan->nomor_tagihan }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-5 gap-5">

        {{-- Rincian --}}
        <div class="section-card sm:col-span-3">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Rincian Tagihan</h3>
                @php
                    $badgeMap = ['terbit'=>'badge-yellow','sebagian'=>'badge-orange','lunas'=>'badge-green','jatuh_tempo'=>'badge-red','void'=>'badge-gray'];
                @endphp
                <span class="{{ $badgeMap[$tagihan->status->value] ?? 'badge-gray' }}">{{ $tagihan->status->label() }}</span>
            </div>
            <div class="p-5">
                <div class="bg-slate-50 rounded-xl p-4 space-y-3 mb-5">
                    @foreach([
                        ['Pemakaian', number_format($tagihan->pemakaian_kubik, 2) . ' m³'],
                        ['Harga per m³', 'Rp ' . number_format($tagihan->harga_per_kubik, 0, ',', '.')],
                        ['Subtotal', 'Rp ' . number_format($tagihan->pemakaian_kubik * $tagihan->harga_per_kubik, 0, ',', '.')],
                        ['Biaya Admin', 'Rp ' . number_format($tagihan->biaya_admin, 0, ',', '.')],
                    ] as [$label, $val])
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">{{ $label }}</span>
                            <span class="font-medium text-slate-700">{{ $val }}</span>
                        </div>
                    @endforeach
                    @if($tagihan->denda > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-red-500">Denda Keterlambatan</span>
                            <span class="font-medium text-red-600">+ Rp {{ number_format($tagihan->denda, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-slate-200 pt-3 flex justify-between">
                        <span class="font-bold text-slate-800">Total Tagihan</span>
                        <span class="font-bold text-blue-700 text-xl">
                            Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Date info --}}
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-slate-400 text-xs mb-0.5">Tanggal Terbit</p>
                        <p class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($tagihan->tanggal_terbit)->format('d M Y') }}</p>
                    </div>
                    <div class="bg-{{ $tagihan->status->value === 'jatuh_tempo' ? 'red' : 'slate' }}-50 rounded-xl p-3">
                        <p class="text-slate-400 text-xs mb-0.5">Jatuh Tempo</p>
                        <p class="font-medium {{ $tagihan->status->value === 'jatuh_tempo' ? 'text-red-600' : 'text-slate-700' }}">
                            {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d M Y') }}
                        </p>
                    </div>
                </div>

                {{-- Pay button --}}
                @if($tagihan->status->isBisaBayar())
                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <p class="text-sm text-slate-500 mb-3">Bayar tagihan ini secara online via Midtrans</p>
                        <button id="pay-btn"
                                onclick="initPembayaran({{ $tagihan->id }})"
                                class="w-full btn-primary py-3 text-base font-semibold shadow-lg shadow-blue-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Bayar Sekarang — Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                        </button>
                    </div>
                @elseif($tagihan->status->value === 'lunas')
                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-green-800 text-sm">Tagihan Lunas</p>
                                @if($tagihan->tanggal_lunas)
                                    <p class="text-green-600 text-xs">{{ \Carbon\Carbon::parse($tagihan->tanggal_lunas)->format('d M Y, H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Meter Reading + Transaksi --}}
        <div class="sm:col-span-2 space-y-5">
            @if($tagihan->meterReading)
                <div class="section-card">
                    <div class="section-card-header">
                        <h3 class="font-semibold text-slate-800 text-sm">Data Meteran</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach([
                            ['Kubik Awal', number_format($tagihan->meterReading->kubik_awal, 2) . ' m³'],
                            ['Kubik Akhir', number_format($tagihan->meterReading->kubik_akhir, 2) . ' m³'],
                            ['Pemakaian', number_format($tagihan->meterReading->pemakaian, 2) . ' m³'],
                        ] as [$l, $v])
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ $l }}</span>
                                <span class="font-medium text-slate-700">{{ $v }}</span>
                            </div>
                        @endforeach

                        {{-- Foto Meteran --}}
                        @if($tagihan->meterReading->foto_meteran)
                            <div class="pt-3 border-t border-slate-100">
                                <p class="text-xs text-slate-400 mb-2">Foto Meteran</p>
                                <a href="{{ asset('storage/' . $tagihan->meterReading->foto_meteran) }}"
                                   target="_blank" class="block group relative">
                                    <img src="{{ asset('storage/' . $tagihan->meterReading->foto_meteran) }}"
                                         alt="Foto meteran"
                                         class="w-full rounded-xl border border-slate-200 object-cover max-h-48 group-hover:brightness-90 transition">
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                        <div class="bg-black/50 rounded-full p-2">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <p class="text-xs text-slate-400 mt-1.5 text-center">Klik untuk perbesar</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="section-card">
                <div class="section-card-header">
                    <h3 class="font-semibold text-slate-800 text-sm">Riwayat Pembayaran</h3>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($tagihan->transaksi ?? [] as $trx)
                        @php $tBadge = ['pending'=>'badge-yellow','success'=>'badge-green','failed'=>'badge-red','cancelled'=>'badge-gray','expired'=>'badge-gray']; @endphp
                        <div class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-mono text-slate-600">{{ Str::limit($trx->kode_transaksi, 15) }}</p>
                                <p class="text-xs text-slate-400">{{ $trx->metode_pembayaran ?? '-' }}</p>
                            </div>
                            <span class="{{ $tBadge[$trx->status->value] ?? 'badge-gray' }} text-xs">
                                {{ $trx->status->label() }}
                            </span>
                        </div>
                    @empty
                        <div class="p-5 text-center text-sm text-slate-400">Belum ada transaksi</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

<x-slot name="scripts">
<script src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
async function initPembayaran(tagihanId) {
    const btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.textContent = 'Memproses...';
    try {
        const resp = await fetch(`/portal/tagihan/${tagihanId}/bayar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.message || 'Gagal memulai pembayaran');
        window.snap.pay(data.snap_token, {
            onSuccess: function(result) {
                window.location.reload();
            },
            onPending: function(result) {
                window.location.reload();
            },
            onError: function(result) {
                alert('Pembayaran gagal. Silakan coba lagi.');
                btn.disabled = false;
                btn.textContent = 'Bayar Sekarang';
            },
            onClose: function() {
                // If user closes normally
                if (pollingInterval) clearInterval(pollingInterval);
                btn.disabled = false;
                btn.textContent = 'Bayar Sekarang — Rp {{ number_format($tagihan->total_tagihan, 0, ",", ".") }}';
            }
        });

        // Polling status secara real-time dari backend
        let pollingInterval = setInterval(async () => {
            try {
                let statusResp = await fetch(`/portal/tagihan/${tagihanId}/bayar/status`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (statusResp.ok) {
                    let statusData = await statusResp.json();
                    if (statusData.tagihan_status === 'lunas') {
                        clearInterval(pollingInterval);
                        window.snap.hide(); // Tutup popup snap
                        alert('🎉 Pembayaran Berhasil! Tagihan Anda sudah lunas.');
                        window.location.reload(); // Reload halaman untuk memunculkan tiket/faktur
                    }
                }
            } catch (e) {}
        }, 3000); // Cek setiap 3 detik

    } catch (e) {
        alert(e.message);
        btn.disabled = false;
        btn.textContent = 'Bayar Sekarang';
    }
}
</script>
</x-slot>
</x-layouts.pelanggan>
