<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller; // <- penting
use App\Models\Siswa;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tanggal = $request->input('tanggal');
        $status = $request->input('status');

        $data = Absensi::with('siswa')
            ->when($search, function ($query, $search) {
                return $query->whereHas('siswa', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('kelas', 'like', "%{$search}%");
                });
            })
            ->when($tanggal, function ($query, $tanggal) {
                return $query->whereDate('tanggal', $tanggal);
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->paginate(10);

        return view('absensi.index', compact('data', 'search', 'tanggal', 'status'));
    }

    public function show($id)
    {
        return redirect()->route('absensi.pilihJadwal');
    }

    public function create()
    {
        $siswa = Siswa::all();
        return view('absensi.create', compact('siswa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id'   => 'required|exists:siswa,id',
            'tanggal'    => 'required|date',
            'jam_masuk'  => 'nullable',
            'jam_pulang' => 'nullable',
            'status'     => 'required|in:Hadir,Izin,Sakit,Alpa'
        ]);

        Absensi::create($request->all());

        return redirect()->route('absensi.index')->with('success', 'Absensi berhasil ditambahkan!');
    }
    public function pilihJadwal()
    {
        $jadwal = Siswa::select('jadwal_les')->distinct()->pluck('jadwal_les');
        return view('absensi.pilih-jadwal', compact('jadwal'));
    }

    public function getSiswa($jadwal)
    {
        $today = now()->toDateString();

        // Ambil siswa sesuai jadwal
        $siswaNormal = Siswa::where('jadwal_les', $jadwal)->get();

        // Ambil absensi hari ini
        $absensiToday = Absensi::whereDate('tanggal', $today)
            ->with('siswa')
            ->get()
            ->keyBy('siswa_id');

        // Gabungkan data dengan status absensi
        $siswaGabungan = $siswaNormal->map(function ($s) use ($absensiToday) {
            $s->absensi_status = $absensiToday[$s->id]->status ?? null;
            $s->absensi_id = $absensiToday[$s->id]->id ?? null;
            $s->reschedule_date = $absensiToday[$s->id]->reschedule_date ?? null;
            return $s;
        });

        return response()->json($siswaGabungan);
    }


    public function storeAjax(Request $request)
    {
        // Cari apakah ada record Reschedule yang belum dijalankan
        $reschedule = Absensi::where('siswa_id', $request->siswa_id)
            ->where('status', 'Reschedule')
            ->whereDate('reschedule_date', Carbon::today())
            ->first();

        if ($reschedule) {
            // Update record reschedule jadi status baru
            $reschedule->update([
                'status' => $request->status,
                'reschedule_date' => null, // kosongkan karena sudah dijalani
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi reschedule berhasil diperbarui',
                'data' => $reschedule
            ]);
        }

        // Kalau tidak ada reschedule, pakai perhitungan normal
        $pertemuanKe = Absensi::where('siswa_id', $request->siswa_id)
            ->max('pertemuan') + 1;

        $absensi = Absensi::updateOrCreate(
            [
                'siswa_id' => $request->siswa_id,
                'tanggal'  => Carbon::today()->format('Y-m-d'),
            ],
            [
                'status'     => $request->status,
                'pertemuan'  => $pertemuanKe,
                'reschedule_date' => $request->reschedule_date ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil disimpan/diupdate',
            'data' => $absensi
        ]);
    }


    public function reschedule(Request $request)
    {
        try {
            // Ambil pertemuan terakhir siswa
            $pertemuanKe = Absensi::where('siswa_id', $request->siswa_id)
                ->orderBy('pertemuan', 'desc')
                ->value('pertemuan') ?? 1;

            $absensi = Absensi::updateOrCreate(
                [
                    'siswa_id' => $request->siswa_id,
                    'tanggal'  => \Carbon\Carbon::today()->format('Y-m-d'),
                ],
                [
                    'status'          => 'Reschedule',
                    'pertemuan'       => $pertemuanKe,
                    'reschedule_date' => $request->reschedule_date,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Reschedule berhasil disimpan',
                'data'    => $absensi
            ]);
        } catch (\Exception $e) {
            // \Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
