<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Exports\PembayaranExport;
use Maatwebsite\Excel\Facades\Excel;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $data = Pembayaran::with('siswa')
            ->when($search, function ($query, $search) {
                $query->whereHas('siswa', function ($q) use ($search) {
                    $q->where('nama', 'like', "%$search%")
                        ->orWhere('kelas', 'like', "%$search%");
                });
            })
            ->when($tanggalMulai && $tanggalSelesai, function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
            })
            ->when($tanggalMulai && !$tanggalSelesai, function ($query) use ($tanggalMulai) {
                $query->where('tanggal', '>=', $tanggalMulai);
            })
            ->when(!$tanggalMulai && $tanggalSelesai, function ($query) use ($tanggalSelesai) {
                $query->where('tanggal', '<=', $tanggalSelesai);
            })
            ->latest()
            ->paginate(10);

        return view('pembayaran.index', compact('data', 'search', 'tanggalMulai', 'tanggalSelesai'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'  => 'required|date',
            'metode'   => 'required|string|in:Cash,Transfer',
            'periode'  => 'required|string|max:20',
        ]);

        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update($request->all());

        return redirect()->route('pembayaran.index')->with('success', '✅ Data pembayaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->delete();

        return redirect()->route('pembayaran.index')->with('success', '🗑️ Data pembayaran berhasil dihapus.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new PembayaranExport(
                $request->search,
                $request->tanggal_mulai,
                $request->tanggal_selesai
            ),
            'laporan_pembayaran.xlsx'
        );
    }

    public function create()
    {
        $siswas = Siswa::all();
        return view('pembayaran.create', compact('siswas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal'  => 'required|date',
            'metode'   => 'required|string|in:Cash,Transfer', // pastikan hanya Cash/Transfer
            'periode'  => 'required|string|max:20', // contoh: Januari 2025
        ]);

        Pembayaran::create([
            'siswa_id' => $request->siswa_id,
            'tanggal'  => $request->tanggal,
            'metode'   => $request->metode,
            'periode'  => $request->periode,
        ]);

        return redirect()
            ->route('pembayaran.index')
            ->with('success', '✅ Pembayaran berhasil ditambahkan.');
    }
}
