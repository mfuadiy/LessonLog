@extends('layouts.app')

@section('content')
<div x-data="{ openExport: false, start: '', end: '' }" class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 md:mb-0">Daftar Absensi</h3>
        <a href="{{ route('absensi.pilihJadwal') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow transition">
           <i class="bi bi-calendar-plus"></i> Tambah Absensi
        </a>
        <button 
            @click="openExport = true"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow transition">
            <i class="bi bi-file-earmark-excel"></i> Export To Excel
        </button>
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('absensi.index') }}" 
          class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Dropdown siswa -->
        <select name="siswa_id" id="search-select"
                class="w-full border border-gray-300 rounded-md px-3 py-2 
                    focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">
            <option value="">-- Pilih Nama / Kelas --</option>
            @foreach($siswaList as $s)
                <option value="{{ $s->id }}" {{ ($siswaId ?? '') == $s->id ? 'selected' : '' }}>
                    {{ $s->nama }} - {{ $s->kelas }}
                </option>
            @endforeach
        </select>

        <!-- Input tanggal -->
        <input type="date" name="tanggal" value="{{ $tanggal ?? '' }}"
               class="w-full border border-gray-300 rounded-md px-3 py-2 
                      focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">

        <!-- Status -->
        <select name="status" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">
            <option value="">-- Semua Status --</option>
            <option value="Hadir" {{ ($status ?? '') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
            <option value="Izin" {{ ($status ?? '') == 'Izin' ? 'selected' : '' }}>Izin</option>
            <option value="Sakit" {{ ($status ?? '') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
            <option value="Alpa" {{ ($status ?? '') == 'Alpa' ? 'selected' : '' }}>Alpa</option>
            <option value="Reschedule" {{ ($status ?? '') == 'Reschedule' ? 'selected' : '' }}>Reschedule</option>
        </select>

        <!-- Tombol -->
        <div class="flex gap-2">
            <button type="submit" 
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow transition">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ route('absensi.index') }}" 
               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow transition">
               Reset
            </a>
        </div>
    </form>

    <!-- Table untuk desktop -->
    <div class="overflow-x-auto hidden md:block">
        @if(session('success'))
            <div id="flash-message" class="mb-4 p-3 bg-red-100 text-red-800 rounded relative">
                {{ session('success') }}

                <!-- Tombol Close -->
                <button id="flash-close" 
                        class="absolute top-2 right-2 text-red-800 hover:text-red-600 font-bold">
                    &times;
                </button>
            </div>

            <script>
                const flash = document.getElementById('flash-message');
                const closeBtn = document.getElementById('flash-close');

                setTimeout(() => {
                    if (flash) {
                        flash.style.transition = "opacity 0.5s ease";
                        flash.style.opacity = 0;
                        setTimeout(() => flash.remove(), 500);
                    }
                }, 5000);

                closeBtn.addEventListener('click', () => {
                    if (flash) {
                        flash.style.transition = "opacity 0.3s ease";
                        flash.style.opacity = 0;
                        setTimeout(() => flash.remove(), 300);
                    }
                });
            </script>
        @endif

        <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">No</th> 
                    <th class="px-4 py-2 text-left">Nama Siswa</th>
                    <th class="px-4 py-2 text-left">Kelas</th>
                    <th class="px-4 py-2 text-left">Jadwal</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Pertemuan</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Reschedule</th>
                    <th class="px-4 py-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($data as $absen)
                <tr>
                    <td class="px-4 py-2">{{ $loop->iteration + ($data->currentPage()-1) * $data->perPage() }}</td>
                    <td class="px-4 py-2">{{ $absen->siswa->nama }}</td>
                    <td class="px-4 py-2">{{ $absen->siswa->kelas }}</td>
                    <td class="px-4 py-2">{{ $absen->siswa->jadwal_les }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d F Y') }}</td>
                    <td class="px-4 py-2">{{ $absen->pertemuan }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded-md text-sm font-medium
                            @if($absen->status == 'Hadir') bg-green-100 text-green-700 
                            @elseif($absen->status == 'Izin') bg-yellow-100 text-yellow-700 
                            @elseif($absen->status == 'Sakit') bg-blue-100 text-blue-700 
                            @elseif($absen->status == 'Reschedule') bg-gray-100 text-gray-700 
                            @else bg-red-100 text-red-700 @endif">
                            {{ $absen->status }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        {{ $absen->reschedule_date 
                            ? \Carbon\Carbon::parse($absen->reschedule_date)->translatedFormat('d F Y') 
                            : '-' }}
                    </td>
                    <td class="px-4 py-2">
                        <form action="{{ route('absensi.destroy', $absen->id) }}" method="POST"
                            onsubmit="return confirm('Yakin hapus absensi {{ $absen->siswa->nama }} tanggal {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d F Y') }}?')"
                            class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md shadow transition">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Export Excel -->
    <!-- 🔽 Modal Export Excel -->
<div 
    x-show="openExport"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    x-transition
>
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="openExport = false">
        <h2 class="text-lg font-bold mb-4">Export Absensi ke Excel</h2>
        
        <form action="{{ route('absensi.exportExcel') }}" method="GET">
            <div class="mb-4">
                <label class="block text-sm font-medium">Tanggal Mulai</label>
                <input type="date" name="start" required
                    class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Tanggal Akhir</label>
                <input type="date" name="end" required
                    class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" @click="openExport = false" 
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </button>
            </div>
        </form>

    </div>
</div>


    <!-- Tampilan Mobile -->
    <div class="grid gap-4 md:hidden">
        @foreach($data as $absen)
        <div class="border border-gray-200 rounded-lg p-4 shadow-sm bg-white">
            <div class="font-bold text-gray-800">{{ $absen->siswa->nama }}</div>
            <div class="text-sm text-gray-500">{{ $absen->siswa->kelas }}</div>
            <div class="mt-2 text-sm">
                Pertemuan: <span class="font-semibold">{{ $absen->pertemuan }}</span>
            </div>
            <div class="mt-1">
                <span class="px-2 py-1 rounded-md text-sm font-medium
                    @if($absen->status == 'Hadir') bg-green-100 text-green-700 
                    @elseif($absen->status == 'Izin') bg-yellow-100 text-yellow-700 
                    @elseif($absen->status == 'Sakit') bg-blue-100 text-blue-700 
                    @elseif($absen->status == 'Reschedule') bg-gray-100 text-gray-700 
                    @else bg-red-100 text-red-700 @endif">
                    {{ $absen->status }}
                </span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                📅 {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d F Y') }}
                @if($absen->reschedule_date)
                    • 🔄 {{ \Carbon\Carbon::parse($absen->reschedule_date)->translatedFormat('d F Y') }}
                @endif
            </div>
            <div class="mt-3">
                <form action="{{ route('absensi.destroy', $absen->id) }}" method="POST"
                    onsubmit="return confirm('Yakin hapus absensi {{ $absen->siswa->nama }} tanggal {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d F Y') }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md shadow transition">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        {{ $data->appends(['siswa_id' => $siswaId, 'tanggal' => $tanggal, 'status' => $status])->links('vendor.pagination.tailwind-custom') }}
    </div>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    new TomSelect("#search-select", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
});
</script>
@endsection
