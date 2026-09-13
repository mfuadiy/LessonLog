<?php

namespace App\Exports;

use App\Models\Absensi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsensiExport implements FromCollection, WithEvents, ShouldAutoSize
{
    protected $start;
    protected $end;
    protected $strukturBulan = [];

    public function __construct($start, $end)
    {
        $this->start = Carbon::parse($start)->startOfDay();
        $this->end   = Carbon::parse($end)->endOfDay();

        $this->buildStrukturBulan();
    }

    /**
     * Membangun daftar bulan & mencari jumlah maksimum pertemuan per bulan
     */
    protected function buildStrukturBulan()
    {
        $periodeBulan = CarbonPeriod::create(
            $this->start->copy()->startOfMonth(),
            '1 month',
            $this->end->copy()->startOfMonth()
        );

        foreach ($periodeBulan as $bulan) {
            $awalBulan = $bulan->copy()->startOfMonth();
            $akhirBulan = $bulan->copy()->endOfMonth();

            if ($awalBulan < $this->start) {
                $awalBulan = $this->start->copy();
            }
            if ($akhirBulan > $this->end) {
                $akhirBulan = $this->end->copy();
            }

            // Hitung max pertemuan yang dimiliki oleh seorang siswa dalam bulan ini
            $maxPertemuan = Absensi::whereBetween('tanggal', [
                $awalBulan->toDateString(),
                $akhirBulan->toDateString()
            ])
                ->selectRaw('siswa_id, COUNT(*) as total')
                ->groupBy('siswa_id')
                ->pluck('total')
                ->max() ?? 0;

            $this->strukturBulan[] = [
                'bulan_key'  => $bulan->format('Y-m'),
                'bulan_nama' => $bulan->translatedFormat('F Y'),
                'max_p'      => $maxPertemuan,
                'start'      => $awalBulan,
                'end'        => $akhirBulan,
            ];
        }
    }

    public function collection()
    {
        // Ambil data absensi
        $absensiRaw = Absensi::with('siswa')
            ->whereBetween('tanggal', [
                $this->start->toDateString(),
                $this->end->toDateString()
            ])
            ->orderBy('siswa_id')
            ->orderBy('tanggal')
            ->get();

        // Kelompokkan absensi berdasarkan siswa & bulan
        $grouped = [];
        foreach ($absensiRaw as $item) {
            $siswaId = $item->siswa_id;
            $nama = $item->siswa->nama ?? '-';
            $bulanKey = Carbon::parse($item->tanggal)->format('Y-m');

            if (!isset($grouped[$siswaId])) {
                $grouped[$siswaId] = [
                    'nama' => $nama,
                    'bulan' => []
                ];
            }

            $grouped[$siswaId]['bulan'][$bulanKey][] = Carbon::parse($item->tanggal)->toDateString();
        }

        // Susun baris data Excel sesuai struktur kolom bulan & pertemuan
        $data = [];
        foreach ($grouped as $siswaId => $siswaData) {
            $row = ['Nama Siswa' => $siswaData['nama']];

            foreach ($this->strukturBulan as $bIdx => $b) {
                $bulanKey = $b['bulan_key'];
                $absensiSiswaInBulan = $siswaData['bulan'][$bulanKey] ?? [];

                // Isi tanggal pertemuan sebanyak max_p pada bulan variable
                for ($p = 0; $p < $b['max_p']; $p++) {
                    $key = 'col_' . $bIdx . '_' . $p;
                    $row[$key] = $absensiSiswaInBulan[$p] ?? '';
                }
            }

            $data[] = $row;
        }

        return collect($data);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;
                $sheet->setCellValue('A1', 'Nama Siswa');
                $sheet->mergeCells('A1:A2');

                $col = 2; // Mulai dari Kolom B (Index 2)

                foreach ($this->strukturBulan as $b) {
                    if ($b['max_p'] <= 0) {
                        continue;
                    }

                    $bulanStartCol = $col;

                    // Buat header sub-kolom (Pertemuan 1, Pertemuan 2, dst.)
                    for ($p = 1; $p <= $b['max_p']; $p++) {
                        $label = sprintf('Pertemuan ke-%d', $p);
                        $sheet->setCellValueByColumnAndRow($col, 2, $label);
                        $col++;
                    }

                    $bulanEndCol = $col - 1;

                    // Merge Header Utama (Nama Bulan) di atas sub-kolom
                    $sheet->setCellValueByColumnAndRow($bulanStartCol, 1, $b['bulan_nama']);
                    $sheet->mergeCellsByColumnAndRow($bulanStartCol, 1, $bulanEndCol, 1);
                }

                // Styling Alignment Header
                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '2')
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                // ===== CONDITIONAL FORMATTING: MERAH JIKA TIDAK HADIR / KOSONG =====
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                if ($highestRow >= 3) {
                    $range = 'B3:' . $highestCol . $highestRow;

                    $red = new Conditional();
                    $red->setConditionType(Conditional::CONDITION_CONTAINSBLANKS);
                    $red->getStyle()->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFC7CE'); // merah soft

                    $sheet->getStyle($range)->setConditionalStyles([$red]);
                }
            }
        ];
    }
}
