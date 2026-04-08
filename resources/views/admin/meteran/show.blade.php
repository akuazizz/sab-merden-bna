<x-layouts.admin>
    <x-slot name="pageTitle">Detail Meteran</x-slot>

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.meteran.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="font-bold text-slate-800 text-lg">Detail Pembacaan Meter</h2>
            <p class="text-slate-400 text-sm">{{ $reading->pelanggan?->nama }} · Periode {{ $reading->periode }}</p>
        </div>
        <a href="{{ route('admin.meteran.edit', $reading->id) }}" class="btn-secondary text-xs px-4 py-2">
            Koreksi Data
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Data Meter --}}
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Data Pembacaan</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 mb-1">Kubik Awal</p>
                        <p class="text-2xl font-bold text-slate-700 font-mono">{{ number_format($reading->kubik_awal, 2) }}</p>
                        <p class="text-xs text-slate-400">m³</p>
                    </div>
                    <div class="flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs text-slate-400 mb-1">Kubik Akhir</p>
                        <p class="text-2xl font-bold text-slate-700 font-mono">{{ number_format($reading->kubik_akhir, 2) }}</p>
                        <p class="text-xs text-slate-400">m³</p>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                    <p class="text-xs text-blue-600 font-medium mb-1">Pemakaian Bulan Ini</p>
                    <p class="text-4xl font-bold text-blue-700">{{ number_format($reading->pemakaian, 2) }}</p>
                    <p class="text-blue-500 text-sm mt-0.5">m³</p>
                </div>

                <div class="space-y-3 pt-2">
                    @foreach([
                        ['Periode', $reading->periode],
                        ['Dicatat pada', \Carbon\Carbon::parse($reading->created_at)->format('d M Y, H:i')],
                        ['Dicatat oleh', $reading->user?->name ?? 'Sistem'],
                    ] as [$label, $value])
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">{{ $label }}</span>
                            <span class="font-medium text-slate-700">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>

                @if($reading->foto_meteran)
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-xs text-slate-400 mb-2">Foto Meteran</p>
                        <a href="{{ asset('storage/' . $reading->foto_meteran) }}" target="_blank" class="block group relative">
                            <img src="{{ asset('storage/' . $reading->foto_meteran) }}" alt="Foto meteran"
                                 class="w-full max-h-48 object-cover rounded-xl border border-slate-200 group-hover:brightness-90 transition">
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <div class="bg-black/50 rounded-full p-2">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                    </svg>
                                </div>
                            </div>
                        </a>
                        <p class="text-xs text-slate-400 mt-1.5 text-center">Klik foto untuk perbesar</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tagihan yang dihasilkan --}}
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Tagihan Terkait</h3>
            </div>
            @php $tagihan = $reading->tagihan; @endphp
            @if($tagihan)
                @php
                    $badgeMap = ['terbit'=>'badge-yellow','sebagian'=>'badge-orange','lunas'=>'badge-green','jatuh_tempo'=>'badge-red','void'=>'badge-gray'];
                @endphp
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-mono text-sm text-slate-600">{{ $tagihan->nomor_tagihan }}</p>
                            <span class="{{ $badgeMap[$tagihan->status->value] ?? 'badge-gray' }} mt-1">{{ $tagihan->status->label() }}</span>
                        </div>
                        <p class="text-2xl font-bold text-slate-800">
                            Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 space-y-2.5 text-sm">
                        @foreach([
                            ['Pemakaian', number_format($tagihan->pemakaian_kubik, 2) . ' m³'],
                            ['Harga per m³', 'Rp ' . number_format($tagihan->harga_per_kubik, 0, ',', '.')],
                            ['Biaya Admin', 'Rp ' . number_format($tagihan->biaya_admin, 0, ',', '.')],
                            ['Denda', 'Rp ' . number_format($tagihan->denda, 0, ',', '.')],
                            ['Jatuh Tempo', \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d M Y')],
                        ] as [$l,$v])
                            <div class="flex justify-between">
                                <span class="text-slate-500">{{ $l }}</span>
                                <span class="font-medium text-slate-700">{{ $v }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.tagihan.show', $tagihan->id) }}" class="btn-secondary w-full justify-center text-sm py-2.5">
                        Lihat Detail Tagihan
                    </a>
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-slate-600 font-medium text-sm">Tagihan belum digenerate</p>
                    <p class="text-slate-400 text-xs mt-1">Hubungi admin sistem untuk generate tagihan</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
