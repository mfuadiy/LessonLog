@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-6">Tambah Siswa</h3>

    <form action="{{ route('siswa.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Nama -->
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
            <input type="text" name="nama" id="nama"
                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 
                          focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none"
                   value="{{ old('nama') }}" required>
            @error('nama')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kelas -->
        <div>
            <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
            <input type="text" name="kelas" id="kelas"
                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 
                          focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none"
                   value="{{ old('kelas') }}" required>
            @error('kelas')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Level -->
        <div>
            <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
            <input type="text" name="level" id="level"
                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 
                          focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none"
                   value="{{ old('level') }}" required>
            @error('level')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jadwal Les -->
        <div>
            <label for="jadwal_les" class="block text-sm font-medium text-gray-700">Jadwal Les</label>
            <input type="text" name="jadwal_les" id="jadwal_les"
                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 
                          focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none"
                   value="{{ old('jadwal_les') }}" required>
            @error('jadwal_les')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('siswa.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow transition">
               Batal
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow transition">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
