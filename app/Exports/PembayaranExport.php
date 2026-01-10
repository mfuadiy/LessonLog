<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

class PembayaranExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    protected $search;
    protected $start;
    protected $end;

    public function __construct($search = null, $start = null, $end = null)
    {
        $this->search = $search;
        $this->start  = $start;
        $this->end    = $end;
    }

    public function collection()
    {
        $query = Pembayaran::with('siswa');

        if ($this->search) {
            $query->whereHas('siswa', function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                    ->orWhere('kelas', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->start && $this->end) {
            $query->whereBetween('tanggal', [$this->start, $this->end]);
        }

        return $query
            ->orderBy('tanggal', 'asc')
            ->get()
            ->map(function ($p, $i) {
                return [
                    'No'           => $i + 1,
                    'Nama Siswa'   => $p->siswa->nama ?? '-',
                    'Tanggal'      => Carbon::parse($p->tanggal)->format('d-m-Y'),
                    'Periode'      => $p->periode,
                    'Metode'       => $p->metode ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Tanggal',
            'Periode',
            'Metode Pembayaran',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // Header bold & center
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');

                // Border tabel
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:E{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        ];
    }
}
