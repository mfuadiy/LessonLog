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
        $siswaId = $request->input('siswa_id'); // ✅ ambil siswa_id dari dropdown
        $tanggal = $request->input('tanggal');
        $status = $request->input('status');

        $data = Absensi::with('siswa')
            ->when($siswaId, function ($query, $siswaId) {
                return $query->where('siswa_id', $siswaId);
            })
            ->when($tanggal, function ($query, $tanggal) {
                return $query->whereDate('tanggal', $tanggal);
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->paginate(10);

        $siswaList = Siswa::select('id', 'nama', 'kelas')->orderBy('nama')->get();

        return view('absensi.index', compact('data', 'siswaId', 'tanggal', 'status', 'siswaList'));
    }

    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return redirect()->route('absensi.index')
            ->with('success', 'Data absensi berhasil dihapus.');
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

    public function getSiswa(Request $request, $jadwal)
    {
        // Ambil tanggal dari query param (?tanggal=YYYY-MM-DD)
        // Kalau tidak ada, fallback ke hari ini
        $tanggal = $request->query('tanggal', now()->toDateString());

        // Ambil siswa sesuai jadwal
        $siswaNormal = Siswa::where('jadwal_les', $jadwal)->get();

        // Ambil absensi sesuai tanggal
        $absensiByTanggal = Absensi::whereDate('tanggal', $tanggal)
            ->with('siswa')
            ->get()
            ->keyBy('siswa_id');

        // Gabungkan data siswa + status absensi
        $siswaGabungan = $siswaNormal->map(function ($s) use ($absensiByTanggal) {
            $s->absensi_status = $absensiByTanggal[$s->id]->status ?? null;
            $s->absensi_id = $absensiByTanggal[$s->id]->id ?? null;
            $s->reschedule_date = $absensiByTanggal[$s->id]->reschedule_date ?? null;
            return $s;
        });

        return response()->json($siswaGabungan);
    }



    public function storeAjax(Request $request)
    {
        $request->validate([
            'siswa_id'   => 'required|exists:siswa,id',
            'tanggal'    => 'required|date',
            'status'     => 'required|in:Hadir,Izin,Sakit,Alpa,Reschedule',
            'reschedule_date' => 'nullable|date'
        ]);
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
                'tanggal'  => $request->tanggal,
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
                    'tanggal'  => $request->tanggal,
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
