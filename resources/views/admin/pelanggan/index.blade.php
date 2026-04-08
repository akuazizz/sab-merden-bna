<x-layouts.admin>
    <x-slot name="pageTitle">Daftar Pelanggan</x-slot>

    {{-- Header + Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Manajemen Pelanggan</h2>
            <p class="text-slate-500 text-sm mt-0.5">Kelola data pelanggan air bersih Desa Merden</p>
        </div>
        <a href="{{ route('admin.pelanggan.create') }}" class="btn-primary flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Daftar Pelanggan Baru
        </a>
    </div>

    {{-- Search + Filter --}}
    <div class="section-card mb-5">
        <form method="GET" action="{{ route('admin.pelanggan.index') }}" class="flex gap-3 p-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" value="{{ $keyword }}"
                       class="form-input pl-10" placeholder="Cari nama atau nomor pelanggan...">
            </div>
            <button type="submit" class="btn-primary px-5">Cari</button>
            @if($keyword)
                <a href="{{ route('admin.pelanggan.index') }}" class="btn-secondary px-4">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="section-card">
        <div class="overflow-x-auto">
            <table class="table-auto-clean">
                <thead>
                    <tr>
                        <th>No. Pelanggan</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggan as $p)
                        <tr>
                            <td class="font-mono text-xs text-slate-600">{{ $p->nomor_pelanggan }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($p->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ $p->nama }}</p>
                                        @if($p->nik)
                                            <p class="text-xs text-slate-400 font-mono">NIK: {{ $p->nik }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-slate-600 max-w-[180px]">
                                <p class="truncate">{{ $p->alamat }}</p>
                                @if($p->dusun)
                                    <p class="text-xs text-slate-400">Dusun {{ $p->dusun }}</p>
                                @endif
                            </td>
                            <td class="text-sm text-slate-600">{{ $p->telepon ?? '-' }}</td>
                            <td>
                                @if($p->status->value === 'aktif')
                                    <span class="badge-green">Aktif</span>
                                @else
                                    <span class="badge-gray">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.pelanggan.show', $p->id) }}"
                                       class="text-xs text-blue-600 hover:text-blue-700 font-medium px-2 py-1 rounded hover:bg-blue-50 transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.pelanggan.edit', $p->id) }}"
                                       class="text-xs text-slate-600 hover:text-slate-700 font-medium px-2 py-1 rounded hover:bg-slate-100 transition">
                                        Edit
                                    </a>
                                    @if($p->status->value === 'aktif')
                                        <form method="POST" action="{{ route('admin.pelanggan.deactivate', $p->id) }}"
                                              onsubmit="return confirm('Nonaktifkan {{ $p->nama }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="text-xs text-orange-600 hover:text-orange-700 font-medium px-2 py-1 rounded hover:bg-orange-50 transition">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.pelanggan.activate', $p->id) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="text-xs text-green-600 hover:text-green-700 font-medium px-2 py-1 rounded hover:bg-green-50 transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center">
                                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/>
                                        </svg>
                                    </div>
                                    <p class="text-slate-500 font-medium text-sm">Tidak ada pelanggan ditemukan</p>
                                    @if($keyword)
                                        <p class="text-slate-400 text-xs">Coba kata kunci lain</p>
                                    @else
                                        <a href="{{ route('admin.pelanggan.create') }}" class="btn-primary text-xs px-4 py-2 mt-1">
                                            Daftar Pelanggan Pertama
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pelanggan->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $pelanggan->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
