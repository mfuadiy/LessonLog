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
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold">Kelas</th>
                    <th class="px-4 py-3 text-left font-semibold">Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $s)
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="px-4 py-3">{{ $s->nama }}</td>
                    <td class="px-4 py-3">{{ $s->kelas }}</td>
                    <td class="px-4 py-3">{{ $s->level }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-3 text-center text-gray-500 italic">
                        Belum ada siswa
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-6">
        {{ $data->appends(['search' => $search])->links('vendor.pagination.tailwind-custom') }}
    </div>
</div>
@endsection
