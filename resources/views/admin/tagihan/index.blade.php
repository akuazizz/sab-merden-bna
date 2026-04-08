<x-layouts.admin>
    <x-slot name="pageTitle">Manajemen Tagihan</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Manajemen Tagihan</h2>
            <p class="text-slate-500 text-sm mt-0.5">Monitor dan kelola tagihan air seluruh pelanggan</p>
        </div>
        <form method="POST" action="{{ route('admin.tagihan.mark-overdue') }}"
              onsubmit="return confirm('Tandai semua tagihan lewat jatuh tempo?')">
            @csrf
            <button type="submit" class="btn-secondary text-xs px-4 py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Sync Jatuh Tempo
            </button>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="stat-card text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Total Tagihan {{ $periode }}</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['count_bulan_ini']) }}</p>
        </div>
        <div class="stat-card text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Jatuh Tempo</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['count_overdue']) }}</p>
        </div>
        <div class="stat-card text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Total Tunggakan</p>
            <p class="text-xl font-bold text-slate-800">Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="section-card mb-5">
        <form method="GET" class="flex flex-wrap gap-3 p-4">
            <div class="min-w-[160px]">
                <label class="form-label text-xs">Periode</label>
                <input type="month" name="periode" value="{{ $periode }}" class="form-input py-2">
            </div>
            <div class="min-w-[180px]">
                <label class="form-label text-xs">Status</label>
                <select name="status" class="form-input py-2">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $opt)
                        <option value="{{ $opt->value }}" {{ $status === $opt->value ? 'selected' : '' }}>
                            {{ $opt->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary py-2 px-5">Filter</button>
                <a href="{{ route('admin.tagihan.index') }}" class="btn-secondary py-2 px-4">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="section-card">
        <div class="overflow-x-auto">
            <table class="table-auto-clean">
                <thead>
                    <tr>
                        <th>No. Tagihan</th>
                        <th>Pelanggan</th>
                        <th>Periode</th>
                        <th>Pemakaian</th>
                        <th>Total</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $badgeMap = [
                            'terbit'      => 'badge-yellow',
                            'sebagian'    => 'badge-orange',
                            'lunas'       => 'badge-green',
                            'jatuh_tempo' => 'badge-red',
                            'void'        => 'badge-gray',
                            'draft'       => 'badge-gray',
                        ];
                    @endphp
                    @forelse($tagihan as $t)
                        <tr>
                            <td class="font-mono text-xs text-slate-600">{{ $t->nomor_tagihan }}</td>
                            <td>
                                <p class="font-medium text-slate-800 text-sm">{{ $t->pelanggan?->nama }}</p>
                                <p class="text-xs text-slate-400">{{ $t->pelanggan?->nomor_pelanggan }}</p>
                            </td>
                            <td class="text-slate-600">{{ $t->periode }}</td>
                            <td class="text-slate-600">{{ number_format($t->pemakaian_kubik, 2) }} m³</td>
                            <td class="font-semibold text-slate-800">
                                Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}
                            </td>
                            <td class="text-sm {{ now()->gt($t->tanggal_jatuh_tempo) && !in_array($t->status->value, ['lunas','void']) ? 'text-red-600 font-medium' : 'text-slate-500' }}">
                                {{ \Carbon\Carbon::parse($t->tanggal_jatuh_tempo)->format('d M Y') }}
                            </td>
                            <td>
                                <span class="{{ $badgeMap[$t->status->value] ?? 'badge-gray' }}">
                                    {{ $t->status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.tagihan.show', $t->id) }}"
                                       class="text-xs text-blue-600 font-medium px-2 py-1 rounded hover:bg-blue-50 transition">
                                        Detail
                                    </a>
                                    @if($t->status->isBisaBayar())
                                        <form method="POST" action="{{ route('admin.tagihan.lunas', $t->id) }}"
                                              onsubmit="return confirm('Tandai lunas {{ $t->nomor_tagihan }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-xs text-green-600 font-medium px-2 py-1 rounded hover:bg-green-50 transition">
                                                Lunas
                                            </button>
                                        </form>
                                    @endif
                                    @if(!in_array($t->status->value, ['lunas','void']))
                                        <button onclick="openVoidModal({{ $t->id }}, '{{ $t->nomor_tagihan }}')"
                                                class="text-xs text-red-500 font-medium px-2 py-1 rounded hover:bg-red-50 transition">
                                            Void
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-sm">
                                Tidak ada tagihan untuk periode {{ $periode }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tagihan->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $tagihan->links() }}</div>
        @endif
    </div>
</x-layouts.admin>

{{-- Void Modal --}}
<div id="void-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="font-bold text-slate-800 mb-1">Batalkan Tagihan</h3>
        <p id="void-subtitle" class="text-slate-500 text-sm mb-5"></p>
        <form id="void-form" method="POST">
            @csrf @method('PATCH')
            <div class="mb-4">
                <label class="form-label">Alasan Pembatalan <span class="text-red-500">*</span></label>
                <textarea name="alasan" rows="3" class="form-input resize-none"
                          placeholder="Jelaskan alasan pembatalan minimal 10 karakter..." required minlength="10"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeVoidModal()" class="btn-secondary flex-1">Batal</button>
                <button type="submit" class="btn-danger flex-1">Batalkan Tagihan</button>
            </div>
        </form>
    </div>
</div>

<x-slot name="scripts">
<script>
function openVoidModal(id, nomor) {
    document.getElementById('void-form').action = `/admin/tagihan/${id}/void`;
    document.getElementById('void-subtitle').textContent = `Tagihan: ${nomor}`;
    document.getElementById('void-modal').classList.remove('hidden');
}
function closeVoidModal() {
    document.getElementById('void-modal').classList.add('hidden');
}
</script>
</x-slot>
