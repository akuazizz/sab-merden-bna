<x-layouts.admin>
    <x-slot name="pageTitle">Riwayat Pembayaran</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Riwayat Pembayaran</h2>
            <p class="text-slate-500 text-sm mt-0.5">Semua transaksi pembayaran tagihan air</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="stat-card text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Sukses</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_success']) }}</p>
        </div>
        <div class="stat-card text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['total_pending']) }}</p>
        </div>
        <div class="stat-card text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Gagal</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['total_failed']) }}</p>
        </div>
        <div class="stat-card text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Total Pendapatan</p>
            <p class="text-lg font-bold text-blue-700">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="section-card mb-5">
        <form method="GET" class="flex flex-wrap gap-3 p-4">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label text-xs">Cari Kode Transaksi</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="SAB-xxxx..." class="form-input py-2">
            </div>
            <div class="min-w-[160px]">
                <label class="form-label text-xs">Periode Tagihan</label>
                <input type="month" name="periode" value="{{ $periode }}" class="form-input py-2">
            </div>
            <div class="min-w-[160px]">
                <label class="form-label text-xs">Status</label>
                <select name="status" class="form-input py-2">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $opt)
                        <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>
                            {{ ucfirst($opt) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary py-2 px-5">Filter</button>
                <a href="{{ route('admin.pembayaran.index') }}" class="btn-secondary py-2 px-4">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="section-card">
        <div class="overflow-x-auto">
            <table class="table-auto-clean">
                <thead>
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Tagihan</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $badgeMap = [
                            'success'   => 'badge-green',
                            'pending'   => 'badge-yellow',
                            'failed'    => 'badge-red',
                            'cancelled' => 'badge-gray',
                            'expired'   => 'badge-gray',
                        ];
                    @endphp
                    @forelse($transaksi as $trx)
                        <tr>
                            <td class="font-mono text-xs text-slate-600">
                                {{ $trx->kode_transaksi }}
                            </td>
                            <td>
                                <p class="font-medium text-slate-800 text-sm">
                                    {{ $trx->tagihan?->pelanggan?->nama ?? '-' }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ $trx->tagihan?->pelanggan?->nomor_pelanggan ?? '' }}
                                </p>
                            </td>
                            <td class="text-sm text-slate-600">
                                <p class="font-mono text-xs">{{ $trx->tagihan?->nomor_tagihan }}</p>
                                <p class="text-slate-400 text-xs">{{ $trx->tagihan?->periode }}</p>
                            </td>
                            <td class="font-semibold text-slate-800">
                                Rp {{ number_format($trx->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="text-sm text-slate-500 capitalize">
                                {{ $trx->metode_pembayaran ?? '-' }}
                            </td>
                            <td>
                                <span class="{{ $badgeMap[$trx->status->value] ?? 'badge-gray' }}">
                                    {{ ucfirst($trx->status->value) }}
                                </span>
                            </td>
                            <td class="text-sm text-slate-500">
                                {{ $trx->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.pembayaran.show', $trx->id) }}"
                                   class="text-xs text-blue-600 font-medium px-2 py-1 rounded hover:bg-blue-50 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-sm">
                                Belum ada transaksi pembayaran
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transaksi->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $transaksi->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
