@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 md:mb-0">Daftar Absensi</h3>
        <a href="{{ route('absensi.pilihJadwal') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow transition">
           <i class="bi bi-calendar-plus"></i> Tambah Absensi
        </a>
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('absensi.index') }}" 
          class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <input type="text" name="search" 
               placeholder="Cari nama atau kelas..." 
               value="{{ $search ?? '' }}"
               class="w-full border border-gray-300 rounded-md px-3 py-2 
                      focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">

        <input type="date" name="tanggal" value="{{ $tanggal ?? '' }}"
               class="w-full border border-gray-300 rounded-md px-3 py-2 
                      focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">

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

    <!-- Tabel Absensi -->
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Nama Siswa</th>
                    <th class="px-4 py-2 text-left">Kelas</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Pertemuan</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Reschedule</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($data as $absen)
                <tr>
                    <td class="px-4 py-2">{{ $absen->siswa->nama }}</td>
                    <td class="px-4 py-2">{{ $absen->siswa->kelas }}</td>
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
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-gray-500">
                        Belum ada data absensi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
    {{ $data->appends(['search' => $search, 'tanggal' => $tanggal, 'status' => $status])->links('vendor.pagination.tailwind-custom') }}
    </div>

</div>
@endsection
