<x-layouts.pelanggan>
    <x-slot name="title">Bukti Pembayaran</x-slot>

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('portal.riwayat.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="font-bold text-slate-800">Bukti Pembayaran</h2>
    </div>

    {{-- Bukti Bayar Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden max-w-lg mx-auto" id="receipt-card">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 text-white">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 2C8 7 4 10.5 4 14a8 8 0 0016 0c0-3.5-4-7-8-12z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-lg">SAB Merden</p>
                    <p class="text-blue-200 text-xs">Bukti Pembayaran Air Bersih</p>
                </div>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 bg-green-400 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-white font-semibold text-sm">PEMBAYARAN BERHASIL</p>
                <p class="text-3xl font-bold mt-1">Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Detail --}}
        <div class="p-6 divide-y divide-slate-100">
            @foreach([
                ['Kode Transaksi', $transaksi->kode_transaksi, true],
                ['Nama Pelanggan', $transaksi->pelanggan?->nama ?? '-', false],
                ['No. Pelanggan', $transaksi->pelanggan?->nomor_pelanggan ?? '-', true],
                ['Tagihan Periode', $transaksi->tagihan?->periode ?? '-', false],
                ['No. Tagihan', $transaksi->tagihan?->nomor_tagihan ?? '-', true],
                ['Metode Pembayaran', $transaksi->metode_pembayaran ?? 'Transfer Bank', false],
                ['Tanggal Pembayaran', $transaksi->paid_at ? \Carbon\Carbon::parse($transaksi->paid_at)->translatedFormat('d F Y, H:i') . ' WIB' : '-', false],
            ] as [$label, $value, $mono])
                <div class="flex justify-between items-start py-3 gap-4">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ $label }}</span>
                    <span class="text-sm font-medium text-slate-800 text-right {{ $mono ? 'font-mono text-xs' : '' }}">
                        {{ $value }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="bg-slate-50 px-6 py-4 text-center">
            <p class="text-xs text-slate-400">
                Dokumen ini merupakan bukti pembayaran resmi<br>
                Sistem Air Bersih Desa Merden
            </p>
        </div>
    </div>

    {{-- Print / Back --}}
    <div class="flex justify-center gap-3 mt-5 max-w-lg mx-auto">
        <a href="{{ route('portal.riwayat.index') }}" class="btn-secondary flex-1 justify-center py-2.5">Kembali</a>
        <button onclick="window.print()" class="btn-primary flex-1 justify-center py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak / Simpan PDF
        </button>
    </div>
</x-layouts.pelanggan>
