@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6">
    <!-- Header + Tombol -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-xl font-bold text-gray-800">Daftar Siswa</h3>

        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <a href="{{ route('siswa.create') }}" 
               class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-md transition">
               <i class="bi bi-person-plus mr-2"></i> Tambah Siswa
            </a>

            <!-- Form Pencarian -->
            <form action="{{ route('siswa.index') }}" method="GET" class="flex w-full md:w-auto">
                <input type="text" name="search"
                       class="w-full md:w-64 border border-gray-300 rounded-l-md px-3 py-2 focus:ring focus:ring-blue-200 focus:outline-none"
                       placeholder="Cari nama atau kelas..."
                       value="{{ $search ?? '' }}">
                <button type="submit"
                        class="bg-gray-700 hover:bg-gray-800 text-white px-4 rounded-r-md transition">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm text-gray-700 rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold">Kelas</th>
                    <th class="px-4 py-3 text-left font-semibold">Level</th>
                    <th class="px-4 py-3 text-left font-semibold">Jadwal Les</th>
                    <th class="px-4 py-3 text-center font-semibold">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $s)
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="px-4 py-3">{{ $s->nama }}</td>
                    <td class="px-4 py-3">{{ $s->kelas }}</td>
                    <td class="px-4 py-3">{{ $s->level }}</td>
                    <td class="px-4 py-3">{{ $s->jadwal_les }}</td>
                    <td class="px-4 py-3 text-center flex justify-center gap-2">
                        <!-- Tombol Edit -->
                        <button 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm"
                            x-data
                            @click="$dispatch('open-edit', {{ $s }})">
                            <i class="bi bi-pencil"></i> Edit
                        </button>

                        <!-- Tombol Hapus -->
                        <form action="{{ route('siswa.destroy', $s->id) }}" method="POST" 
                            onsubmit="return confirm('Yakin mau hapus siswa ini?')">
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
                    <td colspan="4" class="px-4 py-3 text-center text-gray-500 italic">
                        Belum ada siswa
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

        <!-- Modal Edit -->
<div x-data="{ open: false, siswa: {} }"
     x-on:open-edit.window="open = true; siswa = $event.detail"
     x-show="open"
     style="display: none"
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <h2 class="text-lg font-bold mb-4">Edit Siswa</h2>
        
        <form :action="`/siswa/${siswa.id}`" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Nama -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Nama</label>
                <input type="text" name="nama" x-model="siswa.nama"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
            </div>
            
            <!-- Kelas -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Kelas</label>
                <input type="text" name="kelas" x-model="siswa.kelas"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
            </div>
            
            <!-- Level -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Level</label>
                <input type="text" name="level" x-model="siswa.level"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
            </div>

            <!-- Jadwal Les -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Jadwal Les</label>
                <input type="text" name="jadwal_les" x-model="siswa.jadwal_les"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
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
    <div class="flex justify-center mt-6">
        {{ $data->appends(['search' => $search])->links('vendor.pagination.tailwind-custom') }}
    </div>
</div>
@endsection
