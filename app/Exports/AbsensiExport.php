<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbsensiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $start;
    protected $end;
    protected $pertemuanList = [];

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    public function collection()
    {
        // Ambil semua absensi dalam rentang tanggal
        $absensi = Absensi::with('siswa')
            ->whereBetween('tanggal', [$this->start, $this->end])
            ->orderBy('siswa_id')
            ->orderBy('pertemuan')
            ->get();

        $data = [];

        foreach ($absensi as $item) {
            $siswaId = $item->siswa_id;
            $nama    = $item->siswa->nama ?? '-';
            $pertemuan = $item->pertemuan;

            // Track semua pertemuan unik
            if (!in_array($pertemuan, $this->pertemuanList)) {
                $this->pertemuanList[] = $pertemuan;
            }

            // Siapkan array untuk tiap siswa
            if (!isset($data[$siswaId])) {
                $data[$siswaId] = ['Nama' => $nama];
            }

            // Masukkan tanggal ke kolom pertemuan
            $data[$siswaId]['Pertemuan ke-' . $pertemuan] = $item->tanggal;
        }

        // Urutkan pertemuan secara numerik
        sort($this->pertemuanList);

        return collect(array_values($data));
    }

    public function headings(): array
    {
        $headings = ['Nama Siswa'];

        // Tambahkan header pertemuan berdasarkan nilai unik dari tabel absensi
        foreach ($this->pertemuanList as $pertemuan) {
            $headings[] = 'Pertemuan ke-' . $pertemuan;
        }

        return $headings;
    }
}
