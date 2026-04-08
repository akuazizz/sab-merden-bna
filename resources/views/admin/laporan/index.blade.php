<x-layouts.admin>
    <x-slot name="pageTitle">Laporan Keuangan</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Laporan Keuangan</h2>
            <p class="text-slate-500 text-sm mt-0.5">Rekap pendapatan dan tunggakan tagihan air</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="tahun" class="form-input py-2 text-sm w-32" onchange="this.form.submit()">
                @foreach($tahunOptions as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Pendapatan {{ $tahun }}</p>
                    <p class="text-xl font-bold text-green-700">Rp {{ number_format($summary['total_pendapatan_tahun'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Tagihan Lunas {{ $tahun }}</p>
                    <p class="text-xl font-bold text-blue-700">{{ number_format($summary['total_tagihan_lunas']) }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Total Tunggakan</p>
                    <p class="text-xl font-bold text-red-700">Rp {{ number_format($summary['total_tunggakan'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Pelanggan Aktif</p>
            <p class="text-2xl font-bold text-slate-800">{{ $summary['total_pelanggan_aktif'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Belum Bayar</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $summary['tagihan_belum_bayar'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Jatuh Tempo</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['tagihan_jatuh_tempo'] }}</p>
        </div>
    </div>

    {{-- Grafik Pendapatan Bulanan --}}
    <div class="section-card mb-6">
        <div class="section-card-header">
            <h3 class="font-semibold text-slate-800 text-sm">Pendapatan Bulanan {{ $tahun }}</h3>
        </div>
        <div class="p-5">
            @php $maxVal = $grafikPendapatan->max('total') ?: 1; @endphp
            <div class="flex items-end gap-2 h-40">
                @foreach($grafikPendapatan as $bulan)
                    @php $height = $bulan['total'] > 0 ? max(8, round(($bulan['total'] / $maxVal) * 100)) : 4; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full text-center text-xs text-slate-500 font-medium">
                            @if($bulan['total'] > 0)
                                {{ number_format($bulan['total']/1000, 0) }}k
                            @endif
                        </div>
                        <div class="w-full rounded-t-lg transition-all duration-500 {{ $bulan['total'] > 0 ? 'bg-blue-500 hover:bg-blue-600' : 'bg-slate-100' }}"
                             style="height: {{ $height }}%"
                             title="{{ $bulan['label'] }}: Rp {{ number_format($bulan['total'], 0, ',', '.') }} ({{ $bulan['jumlah_transaksi'] }} transaksi)">
                        </div>
                        <p class="text-xs text-slate-400">{{ $bulan['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        {{-- Laporan per Periode --}}
        <div class="section-card sm:col-span-2">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Rekap per Periode {{ $tahun }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table-auto-clean">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th class="text-center">Total Tagihan</th>
                            <th class="text-center">Lunas</th>
                            <th class="text-center">Belum Bayar</th>
                            <th class="text-center">Void</th>
                            <th class="text-right">Pendapatan</th>
                            <th class="text-right">Tunggakan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanPeriode as $row)
                            <tr>
                                <td class="font-medium text-slate-700">{{ $row->periode }}</td>
                                <td class="text-center text-slate-600">{{ $row->total_tagihan }}</td>
                                <td class="text-center">
                                    <span class="badge-green">{{ $row->lunas }}</span>
                                </td>
                                <td class="text-center">
                                    @if($row->belum_bayar > 0)
                                        <span class="badge-yellow">{{ $row->belum_bayar }}</span>
                                    @else
                                        <span class="text-slate-400">0</span>
                                    @endif
                                </td>
                                <td class="text-center text-slate-400">{{ $row->void }}</td>
                                <td class="text-right font-semibold text-green-700">
                                    Rp {{ number_format($row->pendapatan, 0, ',', '.') }}
                                </td>
                                <td class="text-right {{ $row->tunggakan > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">
                                    Rp {{ number_format($row->tunggakan, 0, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    @php
                                        [$thn, $bln] = explode('-', $row->periode);
                                    @endphp
                                    <a href="{{ route('admin.laporan.download', ['tahun' => $thn, 'bulan' => $bln]) }}"
                                       class="inline-flex items-center gap-1.5 text-xs text-green-700 bg-green-50 font-medium px-2.5 py-1.5 rounded hover:bg-green-100 transition whitespace-nowrap">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Excel
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400 text-sm">
                                    Belum ada data untuk tahun {{ $tahun }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Tunggakan --}}
        @if($topTunggakan->isNotEmpty())
        <div class="section-card sm:col-span-2">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Top 5 Pelanggan Tunggakan Terbesar</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($topTunggakan as $i => $row)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full {{ $i === 0 ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-500' }} text-xs font-bold flex items-center justify-center">
                                {{ $i + 1 }}
                            </span>
                            <div>
                                <p class="font-medium text-slate-800 text-sm">{{ $row->pelanggan?->nama }}</p>
                                <p class="text-xs text-slate-400">{{ $row->pelanggan?->nomor_pelanggan }} · {{ $row->jumlah_tagihan }} tagihan</p>
                            </div>
                        </div>
                        <span class="font-bold text-red-600">Rp {{ number_format($row->total_tunggakan, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-layouts.admin>
