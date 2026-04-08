<x-layouts.pelanggan>
    <x-slot name="title">Riwayat Pembayaran</x-slot>

    <div class="mb-6">
        <h2 class="text-lg font-bold text-slate-800">Riwayat Pembayaran</h2>
        <p class="text-slate-500 text-sm mt-0.5">Semua transaksi pembayaran yang berhasil</p>
    </div>

    <div class="space-y-3">
        @forelse($riwayat as $trx)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">
                                Tagihan Periode {{ $trx->tagihan?->periode }}
                            </p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $trx->kode_transaksi }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-xs text-slate-500">{{ $trx->metode_pembayaran ?? 'Transfer Bank' }}</span>
                                @if($trx->paid_at)
                                    <span class="text-xs text-slate-400">
                                        {{ \Carbon\Carbon::parse($trx->paid_at)->translatedFormat('d M Y, H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:flex-col sm:items-end gap-3">
                        <div class="flex items-center gap-2">
                            <span class="badge-green">Lunas</span>
                            <p class="font-bold text-slate-800 text-lg">
                                Rp {{ number_format($trx->jumlah, 0, ',', '.') }}
                            </p>
                        </div>
                        <a href="{{ route('portal.riwayat.show', $trx->id) }}"
                           class="btn-secondary text-xs px-4 py-1.5">
                            Bukti Bayar
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-slate-600 font-medium">Belum ada riwayat pembayaran</p>
                <p class="text-slate-400 text-sm mt-1">Riwayat akan muncul setelah Anda melakukan pembayaran</p>
                <a href="{{ route('portal.tagihan.index') }}" class="btn-primary text-xs px-5 py-2 mt-4 inline-flex">
                    Lihat Tagihan
                </a>
            </div>
        @endforelse
    </div>

    @if($riwayat->hasPages())
        <div class="mt-5">{{ $riwayat->links() }}</div>
    @endif
</x-layouts.pelanggan>
