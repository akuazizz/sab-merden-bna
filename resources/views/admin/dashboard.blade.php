<x-layouts.admin>
    <x-slot name="pageTitle">Dashboard</x-slot>

    {{-- ── Stat Cards ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

        {{-- 1: Total Pelanggan --}}
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Pelanggan Aktif</p>
                    <p class="text-3xl font-bold text-slate-800">{{ number_format($stats['total_pelanggan']) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Terdaftar aktif</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50">
                <a href="{{ route('admin.pelanggan.index') }}" class="text-xs text-blue-600 font-medium hover:text-blue-700 flex items-center gap-1">
                    Lihat semua
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- 2: Tagihan Bulan Ini --}}
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">
                        Tagihan {{ now()->translatedFormat('M Y') }}
                    </p>
                    <p class="text-3xl font-bold text-slate-800">{{ number_format($stats['tagihan_bulan_ini']) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Tagihan diterbitkan</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50">
                <a href="{{ route('admin.meteran.create') }}" class="text-xs text-yellow-600 font-medium hover:text-yellow-700 flex items-center gap-1">
                    Input meteran
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- 3: Total Tunggakan --}}
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Total Tunggakan</p>
                    <p class="text-2xl font-bold text-slate-800 leading-tight">
                        Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Belum terbayar</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50">
                <span class="text-xs text-orange-500 font-medium">Perlu tindak lanjut</span>
            </div>
        </div>

        {{-- 4: Jatuh Tempo --}}
        <div class="stat-card border-red-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Jatuh Tempo</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($stats['tagihan_jatuh_tempo']) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Tagihan overdue</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-red-50">
                @if($stats['tagihan_jatuh_tempo'] > 0)
                    <form method="POST" action="{{ route('admin.tagihan.mark-overdue') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-red-600 font-medium hover:text-red-700 flex items-center gap-1">
                            Mark overdue sekarang
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </form>
                @else
                    <span class="text-xs text-green-600 font-medium">✓ Semua tagihan aman</span>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Chart + Quick Actions ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

        <div class="section-card lg:col-span-2">
            <div class="section-card-header">
                <div>
                    <h3 class="font-semibold text-slate-800 text-sm">Statistik Tagihan 6 Bulan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Total tagihan terbit per bulan</p>
                </div>
                <span class="text-xs text-blue-600 font-medium bg-blue-50 px-2 py-1 rounded-lg">6 Bulan Terakhir</span>
            </div>
            <div class="p-6 pb-4">
                <div id="chart-tagihan" class="h-48 flex items-end gap-2 px-2">
                    @foreach($chartData as $item)
                        @php
                            $pct = $chartMax > 0 ? round(($item['count'] / $chartMax) * 100) : 0;
                            $barH = $pct < 6 && $item['count'] == 0 ? 6 : max($pct, 8);
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-600">{{ $item['count'] > 0 ? $item['count'] : '' }}</span>
                            <div class="w-full rounded-t-xl relative group cursor-default transition-all duration-300"
                                 style="height: {{ $barH }}%; background: {{ $item['count'] > 0 ? 'linear-gradient(to top, #2563eb, #60a5fa)' : '#e2e8f0' }};">
                                @if($item['count'] > 0)
                                <div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2.5 py-1 rounded-lg hidden group-hover:block whitespace-nowrap shadow-lg z-10 pointer-events-none">
                                    {{ $item['label'] }}: {{ $item['count'] }} tagihan
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-800"></div>
                                </div>
                                @endif
                            </div>
                            <span class="text-xs text-slate-400 font-medium">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Aksi Cepat</h3>
            </div>
            <div class="p-4 space-y-3">

                {{-- Daftar Pelanggan --}}
                <a href="{{ route('admin.pelanggan.create') }}"
                   class="flex items-center gap-4 p-3.5 rounded-2xl border border-transparent
                          hover:border-blue-100 hover:bg-blue-50 transition-all duration-200 group">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);box-shadow:0 4px 10px rgba(59,130,246,.35);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-blue-700">Daftar Pelanggan</p>
                        <p class="text-xs text-slate-400">Tambah pelanggan baru</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                {{-- Input Meteran --}}
                <a href="{{ route('admin.meteran.create') }}"
                   class="flex items-center gap-4 p-3.5 rounded-2xl border border-transparent
                          hover:border-green-100 hover:bg-green-50 transition-all duration-200 group">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#22c55e,#15803d);box-shadow:0 4px 10px rgba(34,197,94,.35);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-green-700">Input Meteran</p>
                        <p class="text-xs text-slate-400">Catat meter bulan ini</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-green-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                {{-- Monitor Tagihan --}}
                <a href="{{ route('admin.tagihan.index') }}"
                   class="flex items-center gap-4 p-3.5 rounded-2xl border border-transparent
                          hover:border-amber-100 hover:bg-amber-50 transition-all duration-200 group">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 10px rgba(245,158,11,.35);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-amber-700">Monitor Tagihan</p>
                        <p class="text-xs text-slate-400">Lihat semua tagihan</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-amber-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                {{-- Laporan --}}
                <a href="{{ route('admin.laporan.index') }}"
                   class="flex items-center gap-4 p-3.5 rounded-2xl border border-transparent
                          hover:border-purple-100 hover:bg-purple-50 transition-all duration-200 group">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#a855f7,#7c3aed);box-shadow:0 4px 10px rgba(168,85,247,.35);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-purple-700">Laporan Bulanan</p>
                        <p class="text-xs text-slate-400">Unduh & lihat laporan</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-purple-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

            </div>
        </div>
    </div>

    {{-- ── Tagihan Terbaru ─────────────────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <div>
                <h3 class="font-semibold text-slate-800 text-sm">Tagihan Terbaru</h3>
                <p class="text-xs text-slate-400 mt-0.5">Tagihan yang baru diterbitkan</p>
            </div>
            <a href="{{ route('admin.tagihan.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                Lihat semua →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto-clean">
                <thead>
                    <tr>
                        <th>No. Tagihan</th>
                        <th>Pelanggan</th>
                        <th>Periode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>


                    @forelse($tagihanTerbaru as $tagihan)
                        <tr>
                            <td class="font-mono text-xs text-slate-600">{{ $tagihan->nomor_tagihan }}</td>
                            <td>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">{{ $tagihan->pelanggan?->nama }}</p>
                                    <p class="text-xs text-slate-400">{{ $tagihan->pelanggan?->nomor_pelanggan }}</p>
                                </div>
                            </td>
                            <td class="text-sm text-slate-600">{{ $tagihan->periode }}</td>
                            <td class="text-sm font-semibold text-slate-800">
                                Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                            </td>
                            <td>
                                @php
                                    $badgeMap = [
                                        'terbit'      => 'badge-yellow',
                                        'sebagian'    => 'badge-orange',
                                        'lunas'       => 'badge-green',
                                        'jatuh_tempo' => 'badge-red',
                                        'void'        => 'badge-gray',
                                    ];
                                    $badgeClass = $badgeMap[$tagihan->status->value] ?? 'badge-gray';
                                @endphp
                                <span class="{{ $badgeClass }}">{{ $tagihan->status->label() }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.tagihan.show', $tagihan->id) }}"
                                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400 text-sm">
                                Belum ada tagihan diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.admin>
