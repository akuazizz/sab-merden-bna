<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Payment\Models\Transaksi;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanBulananExport
{
    public function __construct(
        private int $tahun,
        private int $bulan,
    ) {}

    public function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle("Laporan SAB Merden {$this->tahun}-{$this->bulan}")
            ->setCreator('SAB Merden System')
            ->setCompany('SAB Desa Merden');

        $this->buildRingkasan($spreadsheet);
        $this->buildDaftarTagihan($spreadsheet);
        $this->buildRiwayatTransaksi($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = "Laporan_SAB_Merden_{$this->tahun}-" . sprintf('%02d', $this->bulan) . ".xlsx";

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── Sheet 1: Ringkasan ────────────────────────────────────────────

    private function buildRingkasan(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');

        $periode     = sprintf('%04d-%02d', $this->tahun, $this->bulan);
        $labelBulan  = \Carbon\Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y');

        // Hitung stats
        $totalTagihan    = Tagihan::where('periode', $periode)->whereNotIn('status', ['draft'])->count();
        $totalLunas      = Tagihan::where('periode', $periode)->where('status', 'lunas')->count();
        $totalBelumBayar = Tagihan::where('periode', $periode)->whereIn('status', ['terbit', 'jatuh_tempo'])->count();
        $totalVoid       = Tagihan::where('periode', $periode)->where('status', 'void')->count();
        $totalPendapatan = Transaksi::where('status', 'success')
            ->whereHas('tagihan', fn($q) => $q->where('periode', $periode))->sum('jumlah');
        $totalTunggakan  = Tagihan::where('periode', $periode)->whereIn('status', ['terbit', 'jatuh_tempo'])->sum('total_tagihan');
        $persenLunas     = $totalTagihan > 0 ? round($totalLunas / $totalTagihan * 100, 1) : 0;

        // Judul utama
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', '📋 LAPORAN KEUANGAN SAB MERDEN');
        $this->styleHeader($sheet, 'A1:C1', 'FF1E3A5F', 14);

        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', "Periode: {$labelBulan}");
        $this->styleHeader($sheet, 'A2:C2', 'FF3B82F6', 11);

        // Info dokumen
        $sheet->setCellValue('A3', 'Digenerate:');
        $sheet->setCellValue('B3', now()->format('d M Y, H:i'));
        $sheet->setCellValue('A4', 'Oleh:');
        $sheet->setCellValue('B4', 'SAB Merden System');

        // Tabel ringkasan
        $sheet->setCellValue('A6', 'INDIKATOR');
        $sheet->setCellValue('B6', 'NILAI');
        $this->styleHeader($sheet, 'A6:B6', 'FF2563EB', 10);

        $data = [
            ['Total Tagihan Terbit',  $totalTagihan],
            ['Tagihan Lunas',         $totalLunas],
            ['Belum Bayar',           $totalBelumBayar],
            ['Void / Dibatalkan',     $totalVoid],
            ['Total Pendapatan',      'Rp ' . number_format($totalPendapatan, 0, ',', '.')],
            ['Total Tunggakan',       'Rp ' . number_format($totalTunggakan, 0, ',', '.')],
            ['Persentase Lunas',      $persenLunas . '%'],
        ];

        foreach ($data as $i => $row) {
            $r = $i + 7;
            $sheet->setCellValue("A{$r}", $row[0]);
            $sheet->setCellValue("B{$r}", $row[1]);
            $bg = $i % 2 === 0 ? 'FFF0F4FF' : 'FFFFFFFF';
            $sheet->getStyle("A{$r}:B{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
        }

        // Border tabel
        $sheet->getStyle('A6:B13')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
    }

    // ── Sheet 2: Daftar Tagihan ───────────────────────────────────────

    private function buildDaftarTagihan(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Daftar Tagihan');

        $periode = sprintf('%04d-%02d', $this->tahun, $this->bulan);

        $headers = [
            'No. Tagihan', 'No. Pelanggan', 'Nama Pelanggan', 'Alamat', 'Telepon',
            'Pemakaian (m³)', 'Harga/m³ (Rp)', 'Biaya Admin (Rp)', 'Total (Rp)',
            'Tgl Terbit', 'Jatuh Tempo', 'Tgl Lunas', 'Status',
        ];
        $cols = range('A', 'M');

        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '1', $h);
        }
        $this->styleHeader($sheet, 'A1:M1', 'FF1E3A5F', 10);

        $tagihans = Tagihan::with('pelanggan:id,nomor_pelanggan,nama,alamat,telepon')
            ->where('periode', $periode)->whereNotIn('status', ['draft'])->orderBy('nomor_tagihan')->get();

        foreach ($tagihans as $i => $t) {
            $r = $i + 2;
            $row = [
                $t->nomor_tagihan,
                $t->pelanggan?->nomor_pelanggan,
                $t->pelanggan?->nama,
                $t->pelanggan?->alamat,
                $t->pelanggan?->telepon,
                (float) $t->pemakaian_kubik,
                (float) $t->harga_per_kubik,
                (float) $t->biaya_admin,
                (float) $t->total_tagihan,
                \Carbon\Carbon::parse($t->tanggal_terbit)->format('d/m/Y'),
                \Carbon\Carbon::parse($t->tanggal_jatuh_tempo)->format('d/m/Y'),
                $t->tanggal_lunas ? \Carbon\Carbon::parse($t->tanggal_lunas)->format('d/m/Y') : '',
                ucfirst($t->status->value),
            ];
            foreach ($row as $j => $val) {
                $sheet->setCellValue($cols[$j] . $r, $val);
            }
            $bg = $i % 2 === 0 ? 'FFF8FAFC' : 'FFFFFFFF';
            $sheet->getStyle("A{$r}:M{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
        }

        // Auto-fit + border
        $lastRow = max(2, count($tagihans) + 1);
        $sheet->getStyle("A1:M{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        foreach (['A'=>18,'B'=>14,'C'=>22,'D'=>25,'E'=>14,'F'=>13,'G'=>14,'H'=>14,'I'=>16,'J'=>12,'K'=>13,'L'=>12,'M'=>12] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
    }

    // ── Sheet 3: Riwayat Transaksi ────────────────────────────────────

    private function buildRiwayatTransaksi(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Riwayat Transaksi');

        $periode = sprintf('%04d-%02d', $this->tahun, $this->bulan);

        $headers = ['Kode Transaksi', 'No. Pelanggan', 'Nama Pelanggan', 'No. Tagihan', 'Jumlah (Rp)', 'Metode', 'Status', 'Tgl Bayar', 'Tgl Dibuat'];
        $cols = ['A','B','C','D','E','F','G','H','I'];

        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '1', $h);
        }
        $this->styleHeader($sheet, 'A1:I1', 'FF065F46', 10);

        $transaksis = Transaksi::with(['tagihan.pelanggan:id,nomor_pelanggan,nama'])
            ->whereHas('tagihan', fn($q) => $q->where('periode', $periode))
            ->orderByDesc('created_at')->get();

        foreach ($transaksis as $i => $x) {
            $r = $i + 2;
            $row = [
                $x->kode_transaksi,
                $x->tagihan?->pelanggan?->nomor_pelanggan,
                $x->tagihan?->pelanggan?->nama,
                $x->tagihan?->nomor_tagihan,
                (float) $x->jumlah,
                ucfirst($x->metode_pembayaran ?? '-'),
                ucfirst($x->status->value),
                $x->paid_at ? \Carbon\Carbon::parse($x->paid_at)->format('d/m/Y H:i') : '',
                $x->created_at->format('d/m/Y H:i'),
            ];
            foreach ($row as $j => $val) {
                $sheet->setCellValue($cols[$j] . $r, $val);
            }
            $bg = $i % 2 === 0 ? 'FFF0FDF4' : 'FFFFFFFF';
            $sheet->getStyle("A{$r}:I{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
        }

        $lastRow = max(2, count($transaksis) + 1);
        $sheet->getStyle("A1:I{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        foreach (['A'=>20,'B'=>14,'C'=>22,'D'=>18,'E'=>14,'F'=>12,'G'=>12,'H'=>18,'I'=>18] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
    }

    // ── Helper ────────────────────────────────────────────────────────

    private function styleHeader($sheet, string $range, string $bgColor, int $fontSize): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => $fontSize, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
    }
}
