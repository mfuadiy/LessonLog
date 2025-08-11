@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 md:mb-0">Data Pembayaran</h3>
        <a href="{{ route('pembayaran.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow transition">
           <i class="bi bi-plus-circle"></i> Tambah Pembayaran
        </a>
    </div>

    <!-- Pencarian -->
    <form method="GET" action="{{ route('pembayaran.index') }}" 
      class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">

    <!-- Search -->
    <input type="text" name="search" placeholder="Cari nama atau kelas..." 
           value="{{ $search ?? '' }}"
           class="w-full border border-gray-300 rounded-md px-3 py-2 
                  focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">

    <!-- Dari Tanggal -->
    <input type="date" name="tanggal_mulai" 
           value="{{ request('tanggal_mulai') }}"
           class="w-full border border-gray-300 rounded-md px-3 py-2 
                  focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">

    <!-- Sampai Tanggal -->
    <input type="date" name="tanggal_selesai" 
           value="{{ request('tanggal_selesai') }}"
           class="w-full border border-gray-300 rounded-md px-3 py-2 
                  focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">

    <!-- Tombol -->
    <div class="flex gap-2 md:col-span-2">
        <button type="submit" 
                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <a href="{{ route('pembayaran.index') }}" 
           class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow">
           Reset
        </a>
    </div>
</form>

@if(session('success'))
    <div id="alert-success" 
         class="mb-4 flex items-center justify-between p-4 bg-green-100 border border-green-300 text-green-800 rounded-md transition-opacity duration-500">
        <span>{{ session('success') }}</span>
        <button onclick="document.getElementById('alert-success').remove()" 
                class="text-green-700 hover:text-green-900">
            ✕
        </button>
    </div>

    <script>
        setTimeout(() => {
            let alertBox = document.getElementById('alert-success');
            if (alertBox) {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500); // tunggu animasi fade out
            }
        }, 5000); // 5 detik
    </script>
@endif


    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Nama Siswa</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Periode</th>
                    <th class="px-4 py-2 text-left">Metode</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($data as $p)
                <tr>
                    <td class="px-4 py-2">{{ $p->siswa->nama }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y') }}</td>
                    <td class="px-4 py-2">{{ $p->periode }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded-md text-sm font-medium
                            @if($p->metode == 'Cash') bg-green-100 text-green-700
                            @elseif($p->metode == 'Transfer') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $p->metode ?? '-' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-500">
                        Belum ada data pembayaran
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        {{ $data->appends(['search' => $search])->links('vendor.pagination.tailwind-custom') }}
    </div>
</div>
@endsection
