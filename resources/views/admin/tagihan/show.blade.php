<x-layouts.admin>
    <x-slot name="pageTitle">Detail Tagihan</x-slot>

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.tagihan.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1 min-w-0">
            <h2 class="font-bold text-slate-800 text-lg font-mono">{{ $tagihan->nomor_tagihan }}</h2>
            <p class="text-sm text-slate-400">Periode {{ $tagihan->periode }} · {{ $tagihan->pelanggan?->nama }}</p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            @php $status = $tagihan->status->value; @endphp
            @if($tagihan->status->isBisaBayar())
                <form method="POST" action="{{ route('admin.tagihan.lunas', $tagihan->id) }}"
                      onsubmit="return confirm('Tandai tagihan ini LUNAS?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-success text-xs px-4 py-2">✓ Tandai Lunas</button>
                </form>
            @endif
            @if(!in_array($status, ['lunas','void']))
                <button onclick="document.getElementById('void-form-section').classList.toggle('hidden')"
                        class="btn-danger text-xs px-4 py-2">Void</button>
            @endif
        </div>
    </div>

    {{-- Void form (tersembunyi kecuali diklik) --}}
    <div id="void-form-section" class="hidden mb-5">
        <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
            <h3 class="font-semibold text-red-800 mb-3">Batalkan Tagihan</h3>
            <form method="POST" action="{{ route('admin.tagihan.void', $tagihan->id) }}">
                @csrf @method('PATCH')
                <div class="flex gap-3">
                    <textarea name="alasan" rows="2" class="form-input flex-1 resize-none text-sm"
                              placeholder="Alasan pembatalan (min 10 karakter)..." required minlength="10"></textarea>
                    <button type="submit" class="btn-danger self-start px-5 py-2.5 flex-shrink-0">Konfirmasi Void</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Detail Tagihan --}}
        <div class="section-card lg:col-span-2">
            <div class="section-card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Rincian Tagihan</h3>
                @php
                    $badgeMap = ['terbit'=>'badge-yellow','sebagian'=>'badge-orange','lunas'=>'badge-green','jatuh_tempo'=>'badge-red','void'=>'badge-gray','draft'=>'badge-gray'];
                @endphp
                <span class="{{ $badgeMap[$tagihan->status->value] ?? 'badge-gray' }}">
                    {{ $tagihan->status->label() }}
                </span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-4 mb-5">
                    @foreach([
                        ['Periode', $tagihan->periode],
                        ['Tanggal Terbit', \Carbon\Carbon::parse($tagihan->tanggal_terbit)->format('d M Y')],
                        ['Tanggal Jatuh Tempo', \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d M Y')],
                        ['Tanggal Lunas', $tagihan->tanggal_lunas ? \Carbon\Carbon::parse($tagihan->tanggal_lunas)->format('d M Y H:i') : '-'],
                    ] as [$label, $value])
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">{{ $label }}</p>
                            <p class="text-sm font-medium text-slate-700">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Billing breakdown --}}
                <div class="bg-slate-50 rounded-xl p-4 space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Pemakaian</span>
                        <span class="font-medium">{{ number_format($tagihan->pemakaian_kubik, 2) }} m³</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Harga per m³</span>
                        <span class="font-medium">Rp {{ number_format($tagihan->harga_per_kubik, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Subtotal Pemakaian</span>
                        <span class="font-medium">Rp {{ number_format($tagihan->pemakaian_kubik * $tagihan->harga_per_kubik, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Biaya Admin</span>
                        <span class="font-medium">Rp {{ number_format($tagihan->biaya_admin, 0, ',', '.') }}</span>
                    </div>
                    @if($tagihan->denda > 0)
                        <div class="flex justify-between text-sm text-red-600">
                            <span>Denda Keterlambatan</span>
                            <span class="font-medium">Rp {{ number_format($tagihan->denda, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-slate-200 pt-2.5 flex justify-between">
                        <span class="font-bold text-slate-800">Total Tagihan</span>
                        <span class="font-bold text-blue-700 text-lg">
                            Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                @if($tagihan->catatan)
                    <div class="mt-4 bg-yellow-50 border border-yellow-100 rounded-xl p-3">
                        <p class="text-xs text-yellow-700 font-medium mb-0.5">Catatan</p>
                        <p class="text-sm text-yellow-800">{{ $tagihan->catatan }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info Pelanggan + Transaksi --}}
        <div class="space-y-5">
            <div class="section-card">
                <div class="section-card-header">
                    <h3 class="font-semibold text-slate-800 text-sm">Pelanggan</h3>
                </div>
                <div class="p-5 space-y-2">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold">
                            {{ strtoupper(substr($tagihan->pelanggan?->nama ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-800 text-sm">{{ $tagihan->pelanggan?->nama }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ $tagihan->pelanggan?->nomor_pelanggan }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">{{ $tagihan->pelanggan?->alamat }}</p>
                    <p class="text-xs text-slate-500">{{ $tagihan->pelanggan?->telepon ?? '-' }}</p>
                    <a href="{{ route('admin.pelanggan.show', $tagihan->pelanggan_id) }}"
                       class="text-xs text-blue-600 font-medium hover:text-blue-700">
                        Lihat profil pelanggan →
                    </a>
                </div>
            </div>

            {{-- Transaksi --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h3 class="font-semibold text-slate-800 text-sm">Riwayat Transaksi</h3>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($tagihan->transaksi ?? [] as $trx)
                        <div class="px-5 py-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs font-mono text-slate-600">{{ $trx->kode_transaksi }}</p>
                                    <p class="text-xs text-slate-400">{{ $trx->metode_pembayaran ?? 'Belum dibayar' }}</p>
                                </div>
                                @php
                                    $tBadge = ['pending'=>'badge-yellow','success'=>'badge-green','failed'=>'badge-red','cancelled'=>'badge-gray','expired'=>'badge-gray'];
                                @endphp
                                <span class="{{ $tBadge[$trx->status->value] ?? 'badge-gray' }} text-xs">
                                    {{ $trx->status->label() }}
                                </span>
                            </div>
                            @if($trx->paid_at)
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ \Carbon\Carbon::parse($trx->paid_at)->format('d M Y H:i') }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="p-5 text-center text-sm text-slate-400">
                            Belum ada transaksi
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
