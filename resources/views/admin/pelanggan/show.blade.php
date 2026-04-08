<x-layouts.admin>
    <x-slot name="pageTitle">Detail Pelanggan</x-slot>

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.pelanggan.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="font-bold text-slate-800 text-lg">{{ $pelanggan->nama }}</h2>
            <p class="text-sm text-slate-400 font-mono">{{ $pelanggan->nomor_pelanggan }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.pelanggan.edit', $pelanggan->id) }}" class="btn-secondary text-xs px-4 py-2">Edit</a>
            @if($pelanggan->status->value === 'aktif')
                <form method="POST" action="{{ route('admin.pelanggan.deactivate', $pelanggan->id) }}"
                      onsubmit="return confirm('Nonaktifkan pelanggan ini?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-danger text-xs px-4 py-2">Nonaktifkan</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.pelanggan.activate', $pelanggan->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-success text-xs px-4 py-2">Aktifkan</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Banner info akun baru (muncul sekali setelah pendaftaran) --}}
    @if(session('akun_baru'))
    @php $akun = session('akun_baru'); @endphp
    <div class="mb-5 p-4 bg-green-50 border border-green-300 rounded-xl flex items-start gap-3">
        <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-green-800 text-sm mb-2">✅ Akun Login Portal berhasil dibuat!</p>
            <p class="text-xs text-green-700 mb-1">Berikan informasi berikut kepada pelanggan untuk login ke portal:</p>
            <div class="bg-white border border-green-200 rounded-lg p-3 mt-2 space-y-1.5 text-sm">
                <div class="flex justify-between items-center gap-4">
                    <span class="text-slate-500 text-xs whitespace-nowrap">URL Login</span>
                    <span class="font-mono font-semibold text-slate-700">{{ url('/login') }}</span>
                </div>
                <div class="flex justify-between items-center gap-4">
                    <span class="text-slate-500 text-xs whitespace-nowrap">Email</span>
                    <span class="font-mono font-bold text-blue-700">{{ $akun['email'] }}</span>
                </div>
                <div class="flex justify-between items-center gap-4">
                    <span class="text-slate-500 text-xs whitespace-nowrap">Password</span>
                    <span class="font-mono font-bold text-blue-700">{{ $akun['password'] }}</span>
                </div>
            </div>
            <p class="text-xs text-amber-600 mt-2 font-medium">⚠️ Informasi ini hanya tampil sekali. Catat sebelum meninggalkan halaman ini.</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Info Pelanggan --}}
        <div class="section-card lg:col-span-1">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Informasi Pelanggan</h3>
                @if($pelanggan->status->value === 'aktif')
                    <span class="badge-green">Aktif</span>
                @else
                    <span class="badge-gray">Nonaktif</span>
                @endif
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 text-3xl font-bold">
                        {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
                    </div>
                </div>
                @foreach([
                    ['label' => 'Nomor Pelanggan', 'value' => $pelanggan->nomor_pelanggan, 'mono' => true],
                    ['label' => 'NIK', 'value' => $pelanggan->nik ?? '-', 'mono' => true],
                    ['label' => 'Email Login', 'value' => $pelanggan->user?->email ?? '— belum ada akun —', 'mono' => false],
                    ['label' => 'Alamat', 'value' => $pelanggan->alamat, 'mono' => false],
                    ['label' => 'Dusun', 'value' => $pelanggan->dusun ?? '-', 'mono' => false],
                    ['label' => 'RT/RW', 'value' => (($pelanggan->rt ?? '-') . ' / ' . ($pelanggan->rw ?? '-')), 'mono' => false],
                    ['label' => 'Telepon', 'value' => $pelanggan->telepon ?? '-', 'mono' => false],
                    ['label' => 'Terdaftar', 'value' => $pelanggan->tanggal_daftar ?? '-', 'mono' => false],
                ] as $row)
                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">{{ $row['label'] }}</p>
                        <p class="text-sm font-medium text-slate-700 {{ $row['mono'] ? 'font-mono' : '' }}">
                            {{ $row['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tagihan Terbaru --}}
        <div class="section-card lg:col-span-2">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Riwayat Tagihan</h3>
                <a href="{{ route('admin.meteran.create', ['pelanggan_id' => $pelanggan->id]) }}"
                   class="btn-primary text-xs px-3 py-1.5">
                    + Input Meter
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-auto-clean">
                    <thead>
                        <tr>
                            <th>No. Tagihan</th>
                            <th>Periode</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tagihanList = $pelanggan->tagihan()->orderByDesc('periode')->limit(10)->get();
                            $badgeMap = ['terbit'=>'badge-yellow','sebagian'=>'badge-orange','lunas'=>'badge-green','jatuh_tempo'=>'badge-red','void'=>'badge-gray'];
                        @endphp
                        @forelse($tagihanList as $t)
                            <tr>
                                <td class="font-mono text-xs">{{ $t->nomor_tagihan }}</td>
                                <td>{{ $t->periode }}</td>
                                <td class="font-semibold">Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}</td>
                                <td><span class="{{ $badgeMap[$t->status->value] ?? 'badge-gray' }}">{{ $t->status->label() }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.tagihan.show', $t->id) }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Detail →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-sm">Belum ada tagihan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
