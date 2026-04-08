<x-layouts.admin>
    <x-slot name="pageTitle">Detail Transaksi</x-slot>

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.pembayaran.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="text-lg font-bold text-slate-800">Detail Transaksi</h2>
            <p class="text-slate-400 text-sm font-mono">{{ $transaksi->kode_transaksi }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        {{-- Info Transaksi --}}
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Info Transaksi</h3>
                @php
                    $badge = ['success'=>'badge-green','pending'=>'badge-yellow','failed'=>'badge-red','cancelled'=>'badge-gray','expired'=>'badge-gray'];
                @endphp
                <span class="{{ $badge[$transaksi->status->value] ?? 'badge-gray' }}">
                    {{ ucfirst($transaksi->status->value) }}
                </span>
            </div>
            <div class="p-5 space-y-3">
                @foreach([
                    ['Kode Transaksi', $transaksi->kode_transaksi],
                    ['Jumlah', 'Rp ' . number_format($transaksi->jumlah, 0, ',', '.')],
                    ['Metode', ucfirst($transaksi->metode_pembayaran ?? '-')],
                    ['Midtrans TxID', $transaksi->midtrans_transaction_id ?? '-'],
                    ['Dibuat', $transaksi->created_at->format('d M Y, H:i')],
                    ['Expired', $transaksi->expired_at?->format('d M Y, H:i') ?? '-'],
                    ['Dibayar', $transaksi->paid_at?->format('d M Y, H:i') ?? '-'],
                ] as [$label, $val])
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">{{ $label }}</span>
                        <span class="font-medium text-slate-700">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Info Pelanggan + Tagihan --}}
        <div class="space-y-5">
            <div class="section-card">
                <div class="section-card-header">
                    <h3 class="font-semibold text-slate-800 text-sm">Pelanggan</h3>
                </div>
                <div class="p-5 space-y-2">
                    @php $p = $transaksi->tagihan?->pelanggan; @endphp
                    <p class="font-semibold text-slate-800">{{ $p?->nama ?? '-' }}</p>
                    <p class="text-sm text-slate-500 font-mono">{{ $p?->nomor_pelanggan }}</p>
                    <p class="text-sm text-slate-500">{{ $p?->alamat }}</p>
                    <p class="text-sm text-slate-500">{{ $p?->telepon }}</p>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <h3 class="font-semibold text-slate-800 text-sm">Tagihan</h3>
                </div>
                <div class="p-5 space-y-2">
                    @php $t = $transaksi->tagihan; @endphp
                    <p class="font-mono text-sm text-slate-700">{{ $t?->nomor_tagihan }}</p>
                    <p class="text-sm text-slate-500">Periode: <strong>{{ $t?->periode }}</strong></p>
                    <p class="text-sm text-slate-500">Total: <strong>Rp {{ number_format($t?->total_tagihan, 0, ',', '.') }}</strong></p>
                    <a href="{{ route('admin.tagihan.show', $t?->id) }}"
                       class="text-xs text-blue-600 hover:underline">Lihat tagihan →</a>
                </div>
            </div>
        </div>

        {{-- Payment Logs --}}
        <div class="section-card sm:col-span-2">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Payment Logs</h3>
                <span class="text-xs text-slate-400">{{ $transaksi->paymentLogs->count() }} log</span>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($transaksi->paymentLogs as $log)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $log->event_type }}</p>
                            <p class="text-xs text-slate-400">{{ $log->created_at->format('d M Y, H:i:s') }} · IP: {{ $log->ip_address }}</p>
                        </div>
                        <div class="text-right">
                            <span class="{{ $log->is_processed ? 'badge-green' : 'badge-yellow' }} text-xs">
                                {{ $log->is_processed ? 'Processed' : 'Pending' }}
                            </span>
                            <p class="text-xs text-slate-400 mt-1">{{ $log->status_raw }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-5 text-center text-sm text-slate-400">Belum ada log</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
