<x-layouts.admin>
    <x-slot name="pageTitle">Daftar Meteran</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Pencatatan Meteran</h2>
            <p class="text-slate-500 text-sm mt-0.5">Input dan kelola pembacaan meter air pelanggan</p>
        </div>
        <a href="{{ route('admin.meteran.create') }}" class="btn-primary flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Input Meter Baru
        </a>
    </div>

    {{-- Filter --}}
    <div class="section-card mb-5">
        <form method="GET" class="flex flex-wrap gap-3 p-4">
            <div class="flex-1 min-w-[160px]">
                <label class="form-label text-xs">Periode</label>
                <input type="month" name="periode" value="{{ $periode }}" class="form-input py-2">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="form-label text-xs">Pelanggan</label>
                <select name="pelanggan_id" class="form-input py-2">
                    <option value="">Semua Pelanggan</option>
                    @foreach($pelangganList as $p)
                        <option value="{{ $p->id }}" {{ request('pelanggan_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }} ({{ $p->nomor_pelanggan }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary py-2 px-5">Filter</button>
                <a href="{{ route('admin.meteran.index') }}" class="btn-secondary py-2 px-4">Reset</a>
            </div>
        </form>
    </div>

    {{-- Reminder belum input --}}
    @if($belumInput->isNotEmpty())
        <div class="alert-warning mb-5">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span><strong>{{ $belumInput->count() }} pelanggan</strong> belum dicatat meterannya bulan ini.</span>
        </div>
    @endif

    {{-- Table --}}
    <div class="section-card">
        <div class="overflow-x-auto">
            <table class="table-auto-clean">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Periode</th>
                        <th>Kubik Awal</th>
                        <th>Kubik Akhir</th>
                        <th>Pemakaian</th>
                        <th>Tagihan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readings as $r)
                        <tr>
                            <td>
                                <p class="font-medium text-slate-800 text-sm">{{ $r->pelanggan?->nama }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ $r->pelanggan?->nomor_pelanggan }}</p>
                            </td>
                            <td class="font-medium text-slate-700">{{ $r->periode }}</td>
                            <td class="text-slate-600">{{ number_format($r->kubik_awal, 2) }}</td>
                            <td class="text-slate-600">{{ number_format($r->kubik_akhir, 2) }}</td>
                            <td>
                                <span class="font-semibold text-blue-700">{{ number_format($r->pemakaian, 2) }} m³</span>
                            </td>
                            <td>
                                @if($r->tagihan)
                                    @php $s = $r->tagihan->status->value; @endphp
                                    <span class="{{ ['terbit'=>'badge-yellow','lunas'=>'badge-green','jatuh_tempo'=>'badge-red','void'=>'badge-gray'][$s] ?? 'badge-gray' }}">
                                        {{ $r->tagihan->status->label() }}
                                    </span>
                                @else
                                    <span class="badge-gray">Belum ada</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.meteran.show', $r->id) }}"
                                       class="text-xs text-blue-600 font-medium px-2 py-1 rounded hover:bg-blue-50 transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.meteran.edit', $r->id) }}"
                                       class="text-xs text-slate-600 font-medium px-2 py-1 rounded hover:bg-slate-100 transition">
                                        Koreksi
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <p class="text-slate-400 text-sm">Belum ada data meter untuk periode {{ $periode }}</p>
                                <a href="{{ route('admin.meteran.create', ['periode' => $periode]) }}"
                                   class="btn-primary text-xs px-4 py-2 mt-3 inline-flex">
                                    Input Meter Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($readings->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $readings->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
