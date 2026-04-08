<x-layouts.pelanggan>
    <x-slot name="title">Dashboard Pelanggan</x-slot>

    {{-- ── Greeting ────────────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">
            Halo, {{ $pelanggan->nama }} 👋
        </h1>
        <p class="text-slate-500 text-sm mt-0.5">
            No. Pelanggan: <span class="font-mono font-medium text-blue-600">{{ $pelanggan->nomor_pelanggan }}</span>
            &nbsp;·&nbsp; {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>

    {{-- ── Stat Cards ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        {{-- Tunggakan --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 text-white shadow-lg shadow-blue-200">
            <p class="text-blue-200 text-xs font-medium uppercase tracking-wide mb-1">Total Tunggakan</p>
            <p class="text-3xl font-bold mb-0.5">
                Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}
            </p>
            <p class="text-blue-200 text-xs">
                {{ $stats['tagihan_aktif'] }} tagihan belum lunas
            </p>
        </div>

        {{-- Total Pemakaian Tahun Ini --}}
        <div class="stat-card">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Pemakaian {{ now()->year }}</p>
            <p class="text-3xl font-bold text-slate-800 mb-0.5">
                {{ number_format($stats['total_pemakaian']) }} <span class="text-lg font-normal text-slate-400">m³</span>
            </p>
            <p class="text-xs text-slate-400">Total tahun berjalan</p>
        </div>

        {{-- Status Akun --}}
        <div class="stat-card">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Status Akun</p>
            <div class="flex items-center gap-2 mt-2">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                <span class="font-semibold text-green-700">Aktif</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">{{ $pelanggan->alamat }}</p>
        </div>
    </div>

    {{-- ── Tagihan Aktif + Grafik ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-6">

        {{-- Tagihan Terbaru (3 kolom) --}}
        <div class="section-card lg:col-span-3">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Tagihan Terbaru</h3>
                <a href="{{ route('portal.tagihan.index') }}" class="text-xs text-blue-600 font-medium hover:text-blue-700">
                    Lihat semua →
                </a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($tagihanTerbaru as $tagihan)
                    @php
                        $badgeMap = [
                            'terbit'      => 'badge-yellow',
                            'sebagian'    => 'badge-orange',
                            'lunas'       => 'badge-green',
                            'jatuh_tempo' => 'badge-red',
                            'void'        => 'badge-gray',
                        ];
                    @endphp
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50/50 transition">
                        <div>
                            <p class="text-sm font-medium text-slate-800">Periode {{ $tagihan->periode }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="text-xs text-slate-500">
                                    Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                                </p>
                                <span class="{{ $badgeMap[$tagihan->status->value] ?? 'badge-gray' }} text-xs">
                                    {{ $tagihan->status->label() }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($tagihan->status->isBisaBayar())
                                <a href="{{ route('portal.tagihan.show', $tagihan->id) }}"
                                   class="btn-primary text-xs px-3 py-1.5">
                                    Bayar
                                </a>
                            @else
                                <a href="{{ route('portal.tagihan.show', $tagihan->id) }}"
                                   class="btn-secondary text-xs px-3 py-1.5">
                                    Detail
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Belum ada tagihan</p>
                        <p class="text-slate-400 text-xs mt-1">Tagihan akan muncul setelah meteran dicatat</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Grafik Pemakaian (2 kolom) --}}
        <div class="section-card lg:col-span-2">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Pemakaian Air</h3>
                <span class="text-xs text-slate-400">6 bulan terakhir</span>
            </div>
            <div class="p-5">
                @if($grafikPemakaian->isNotEmpty())
                    <div class="flex items-end gap-2 h-36">
                        @php $maxPemakaian = $grafikPemakaian->max('pemakaian') ?: 1; @endphp
                        @foreach($grafikPemakaian as $data)
                            @php $pct = $data['pemakaian'] / $maxPemakaian * 100; @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <span class="text-xs text-slate-500 font-medium">{{ $data['pemakaian'] }}</span>
                                <div class="w-full rounded-t-lg bg-blue-500 hover:bg-blue-600 transition-colors duration-200 relative group cursor-default"
                                     style="height: {{ max(8, $pct) }}%; min-height: 6px;">
                                </div>
                                <span class="text-xs text-slate-400" style="font-size:10px">{{ $data['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-center text-xs text-slate-400 mt-3">Pemakaian dalam m³</p>
                @else
                    <div class="h-36 flex items-center justify-center">
                        <p class="text-slate-400 text-sm">Belum ada data pemakaian</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Quick Links ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('portal.tagihan.index') }}"
           class="flex items-center gap-4 bg-white border border-slate-100 rounded-2xl p-5
                  hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-800 text-sm">Daftar Tagihan</p>
                <p class="text-slate-400 text-xs mt-0.5">Lihat & bayar tagihan</p>
            </div>
        </a>

        <a href="{{ route('portal.riwayat.index') }}"
           class="flex items-center gap-4 bg-white border border-slate-100 rounded-2xl p-5
                  hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-800 text-sm">Riwayat Bayar</p>
                <p class="text-slate-400 text-xs mt-0.5">Download bukti bayar</p>
            </div>
        </a>

        <div class="flex items-center gap-4 bg-white border border-slate-100 rounded-2xl p-5">
            <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-800 text-sm">{{ auth()->user()->name }}</p>
                <p class="text-slate-400 text-xs mt-0.5">{{ $pelanggan->dusun ?? 'Desa Merden' }}</p>
            </div>
        </div>
    </div>

</x-layouts.pelanggan>
