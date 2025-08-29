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
        <th class="px-4 py-3 text-left">No</th> 
        <th class="px-4 py-2 text-left">Nama Siswa</th>
        <th class="px-4 py-2 text-left">Tanggal</th>
        <th class="px-4 py-2 text-left">Periode</th>
        <th class="px-4 py-2 text-left">Metode</th>
        <th class="px-4 py-2 text-center">Action</th>
    </tr>
</thead>
<tbody class="bg-white divide-y divide-gray-200">
    @forelse($data as $p)
    <tr>
        <td class="px-4 py-2">{{ $loop->iteration + ($data->currentPage()-1) * $data->perPage() }}</td>
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
        <td class="px-4 py-2 text-center flex gap-2 justify-center">
            <!-- Tombol Edit -->
            <button 
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm"
                x-data
                @click="$dispatch('open-edit-pembayaran', {{ $p }})">
                <i class="bi bi-pencil"></i> Edit
            </button>

            <!-- Tombol Delete -->
            <form action="{{ route('pembayaran.destroy', $p->id) }}" method="POST" 
                  onsubmit="return confirm('Yakin mau hapus pembayaran ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </form>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="text-center py-4 text-gray-500">
            Belum ada data pembayaran
        </td>
    </tr>
    @endforelse
</tbody>

        </table>

        <!-- Modal Edit Pembayaran -->
<div x-data="{ open: false, pembayaran: {} }"
     x-on:open-edit-pembayaran.window="open = true; pembayaran = $event.detail"
     x-show="open"
     style="display: none"
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <h2 class="text-lg font-bold mb-4">Edit Pembayaran</h2>
        
        <form :action="`/pembayaran/${pembayaran.id}`" method="POST">
            @csrf
            @method('PUT')

            <!-- Tanggal -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Tanggal</label>
                <input type="date" name="tanggal" x-model="pembayaran.tanggal"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
            </div>

            <!-- Periode -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Periode</label>
                <input type="month" name="periode" x-model="pembayaran.periode"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
            </div>
            
            <!-- Metode -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Metode</label>
                <select name="metode" x-model="pembayaran.metode"
                        class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
                    <option value="Cash">Cash</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>

            <!-- Tombol -->
            <div class="flex justify-end gap-3">
                <button type="button" 
                        @click="open = false" 
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        {{ $data->appends(['search' => $search])->links('vendor.pagination.tailwind-custom') }}
    </div>
</div>
@endsection
