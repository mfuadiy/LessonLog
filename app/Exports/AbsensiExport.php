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
    protected $struktur = [];

    public function __construct($start, $end)
    {
        $this->start = Carbon::parse($start)->startOfDay();
        $this->end   = Carbon::parse($end)->endOfDay();

        $this->buildStrukturPekan();
    }

    /**
     * Bangun struktur bulan & pekan sesuai kalender
     */
    protected function buildStrukturPekan()
    {
        $periodeBulan = CarbonPeriod::create(
            $this->start->copy()->startOfMonth(),
            '1 month',
            $this->end->copy()->startOfMonth()
        );

        foreach ($periodeBulan as $bulan) {

            $awalBulan = $bulan->copy()->startOfMonth();
            $akhirBulan = $bulan->copy()->endOfMonth();

            // potong sesuai start–end
            if ($awalBulan < $this->start) {
                $awalBulan = $this->start->copy();
            }
            if ($akhirBulan > $this->end) {
                $akhirBulan = $this->end->copy();
            }

            $cursor = $awalBulan->copy();
            $pekanKe = 1;

            while ($cursor <= $akhirBulan) {

                // akhir pekan = Sabtu ATAU akhir bulan
                $akhirPekan = $cursor->copy()->endOfWeek(Carbon::SATURDAY);
                if ($akhirPekan > $akhirBulan) {
                    $akhirPekan = $akhirBulan->copy();
                }

                $this->struktur[] = [
                    'bulan_key' => $bulan->format('Y-m'),
                    'bulan_nama' => $bulan->translatedFormat('F Y'),
                    'pekan_ke' => $pekanKe,
                    'start' => $cursor->copy(),
                    'end' => $akhirPekan->copy(),
                ];

                $cursor = $akhirPekan->addDay();
                $pekanKe++;
            }
        }
    }

    public function collection()
    {
        $absensi = Absensi::with('siswa')
            ->whereBetween('tanggal', [
                $this->start->toDateString(),
                $this->end->toDateString()
            ])
            ->orderBy('siswa_id')
            ->orderBy('tanggal')
            ->get();

        $data = [];

        foreach ($absensi as $item) {

            $siswaId = $item->siswa_id;
            $nama = $item->siswa->nama ?? '-';
            $tgl = Carbon::parse($item->tanggal);

            if (!isset($data[$siswaId])) {
                $data[$siswaId] = ['Nama Siswa' => $nama];

                // buat semua kolom pekan
                foreach ($this->struktur as $i => $p) {
                    $data[$siswaId]['col_' . $i] = '';
                }
            }

            foreach ($this->struktur as $i => $p) {
                if ($tgl->between($p['start'], $p['end'])) {
                    $data[$siswaId]['col_' . $i] = $tgl->toDateString();
                    break;
                }
            }
        }

        return collect(array_values($data));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;
                $sheet->setCellValue('A1', 'Nama Siswa');
                $sheet->mergeCells('A1:A2');

                $col = 2;
                $bulanStartCol = $col;
                $currentBulan = null;

                foreach ($this->struktur as $p) {

                    if ($currentBulan !== $p['bulan_key']) {

                        if ($currentBulan !== null) {
                            $sheet->mergeCellsByColumnAndRow(
                                $bulanStartCol,
                                1,
                                $col - 1,
                                1
                            );
                        }

                        $bulanStartCol = $col;
                        $currentBulan = $p['bulan_key'];
                        $sheet->setCellValueByColumnAndRow($col, 1, $p['bulan_nama']);
                    }

                    $label = sprintf(
                        'Pekan %d (%s–%s %s)',
                        $p['pekan_ke'],
                        $p['start']->format('d'),
                        $p['end']->format('d'),
                        $p['start']->translatedFormat('M')
                    );

                    $sheet->setCellValueByColumnAndRow($col, 2, $label);
                    $col++;
                }

                // merge bulan terakhir
                $sheet->mergeCellsByColumnAndRow(
                    $bulanStartCol,
                    1,
                    $col - 1,
                    1
                );

                // alignment
                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '2')
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                // ===== CONDITIONAL FORMATTING: MERAH JIKA TIDAK HADIR =====
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // range absensi (mulai B3 karena A1:A2 header)
                $range = 'B3:' . $highestCol . $highestRow;

                $red = new Conditional();
                $red->setConditionType(Conditional::CONDITION_CONTAINSBLANKS);
                $red->getStyle()->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFC7CE'); // merah soft

                $sheet->getStyle($range)->setConditionalStyles([$red]);
            }
        ];
    }
}
