@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-6">Tambah Pembayaran</h3>

    <form action="{{ route('pembayaran.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Pilih Siswa -->
        <div>
            <label for="siswa_id" class="block text-gray-700 font-medium mb-2">Nama Siswa</label>
            <select name="siswa_id" id="siswa_id" required
                class="w-full border border-gray-300 rounded-md px-3 py-2 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">
                <option value="">-- Pilih Siswa --</option>
                @foreach($siswas as $siswa)
                    <option value="{{ $siswa->id }}">
                        {{ $siswa->nama }} - {{ $siswa->kelas }}
                    </option>
                @endforeach
            </select>
            @error('siswa_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Pembayaran -->
        <div>
            <label for="tanggal" class="block text-gray-700 font-medium mb-2">Tanggal Pembayaran</label>
            <input type="date" name="tanggal" id="tanggal" required
                class="w-full border border-gray-300 rounded-md px-3 py-2 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none"
                value="{{ old('tanggal') }}">
            @error('tanggal')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Metode Pembayaran -->
        <div>
            <label for="metode" class="block text-gray-700 font-medium mb-2">Metode Pembayaran</label>
            <select name="metode" id="metode" required
                class="w-full border border-gray-300 rounded-md px-3 py-2 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none">
                <option value="Cash">Cash</option>
                <option value="Transfer">Transfer</option>
            </select>
            @error('metode')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Periode Pembayaran -->
        <div>
            <label for="periode" class="block text-gray-700 font-medium mb-2">Periode Pembayaran</label>
            <input type="month" name="periode" id="periode" required
                class="w-full border border-gray-300 rounded-md px-3 py-2 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 outline-none"
                value="{{ old('periode') }}">
            @error('periode')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol -->
        <div class="flex gap-2">
            <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow transition">
                Simpan
            </button>
            <a href="{{ route('pembayaran.index') }}" 
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
