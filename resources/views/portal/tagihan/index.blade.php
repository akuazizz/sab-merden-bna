<x-layouts.pelanggan>
    <x-slot name="title">Tagihan Saya</x-slot>

    <div class="mb-6">
        <h2 class="text-lg font-bold text-slate-800">Tagihan Saya</h2>
        <p class="text-slate-500 text-sm mt-0.5">Daftar tagihan air Anda</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-5">
        <form method="GET" class="flex gap-3 p-4">
            <select name="status" class="form-input py-2 flex-1">
                <option value="">Semua Status</option>
                <option value="terbit"      {{ $status === 'terbit'      ? 'selected' : '' }}>Belum Bayar</option>
                <option value="jatuh_tempo" {{ $status === 'jatuh_tempo' ? 'selected' : '' }}>Jatuh Tempo</option>
                <option value="lunas"       {{ $status === 'lunas'       ? 'selected' : '' }}>Lunas</option>
            </select>
            <button type="submit" class="btn-primary px-5 py-2">Filter</button>
        </form>
    </div>

    {{-- List --}}
    <div class="space-y-3">
        @forelse($tagihan as $t)
            @php
                $badgeMap = ['terbit'=>'badge-yellow','sebagian'=>'badge-orange','lunas'=>'badge-green','jatuh_tempo'=>'badge-red','void'=>'badge-gray'];
                $isOverdue = $t->status->value === 'jatuh_tempo';
            @endphp
            <div class="bg-white rounded-2xl border {{ $isOverdue ? 'border-red-200' : 'border-slate-100' }} shadow-sm p-5
                        hover:shadow-md transition-shadow duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-slate-800">Periode {{ $t->periode }}</p>
                            <span class="{{ $badgeMap[$t->status->value] ?? 'badge-gray' }}">{{ $t->status->label() }}</span>
                            @if($isOverdue)
                                <span class="badge-red animate-pulse">⚠ Lewat jatuh tempo</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 font-mono">{{ $t->nomor_tagihan }}</p>
                        <div class="flex flex-wrap gap-4 mt-2 text-sm text-slate-500">
                            <span>Pemakaian: <strong class="text-slate-700">{{ number_format($t->pemakaian_kubik, 2) }} m³</strong></span>
                            <span>Jatuh tempo: <strong class="{{ $isOverdue ? 'text-red-600' : 'text-slate-700' }}">
                                {{ \Carbon\Carbon::parse($t->tanggal_jatuh_tempo)->format('d M Y') }}
                            </strong></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:flex-col sm:items-end gap-3">
                        <p class="text-xl font-bold text-slate-800">
                            Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}
                        </p>
                        @if($t->status->isBisaBayar())
                            <a href="{{ route('portal.tagihan.show', $t->id) }}" class="btn-primary text-sm px-5 py-2">
                                Bayar Sekarang
                            </a>
                        @else
                            <a href="{{ route('portal.tagihan.show', $t->id) }}" class="btn-secondary text-sm px-5 py-2">
                                Lihat Detail
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center shadow-sm">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-slate-600 font-medium">Tidak ada tagihan</p>
                <p class="text-slate-400 text-sm mt-1">Tagihan akan muncul setelah meter dicatat oleh petugas</p>
            </div>
        @endforelse
    </div>

    @if($tagihan->hasPages())
        <div class="mt-5">{{ $tagihan->links() }}</div>
    @endif
</x-layouts.pelanggan>
