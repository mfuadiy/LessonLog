@extends('layouts.app')

@section('content')
<div 
    x-data="{ openExport: false }"
    class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6"
>
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 md:mb-0">Data Pembayaran</h3>

        <div class="flex gap-2">
            <a href="{{ route('pembayaran.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow transition">
               <i class="bi bi-plus-circle"></i> Tambah Pembayaran
            </a>

            <button 
                @click="openExport = true"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow transition">
                <i class="bi bi-file-earmark-excel"></i> Export To Excel
            </button>
        </div>
    </div>

    <!-- Pencarian -->
    <form method="GET" action="{{ route('pembayaran.index') }}" 
          class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">

        <input type="text" name="search" placeholder="Cari nama atau kelas..." 
               value="{{ $search ?? '' }}"
               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:ring-blue-200">

        <input type="date" name="tanggal_mulai" 
               value="{{ request('tanggal_mulai') }}"
               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:ring-blue-200">

        <input type="date" name="tanggal_selesai" 
               value="{{ request('tanggal_selesai') }}"
               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:ring-blue-200">

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
                        <span class="px-2 py-1 rounded-md text-sm
                            @if($p->metode == 'Cash') bg-green-100 text-green-700
                            @elseif($p->metode == 'Transfer') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $p->metode }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-center flex gap-2 justify-center">
                        <button 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm"
                            x-data
                            @click="$dispatch('open-edit-pembayaran', {{ $p }})">
                            Edit
                        </button>

                        <form action="{{ route('pembayaran.destroy', $p->id) }}" method="POST"
                              onsubmit="return confirm('Yakin mau hapus pembayaran ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">
                        Belum ada data pembayaran
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL EXPORT -->
     <div 
        x-show="openExport"
        x-transition
        @click.self="openExport = false"
        style="display:none"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    >
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-lg font-bold mb-4">Export Pembayaran</h2>

            <form method="GET" action="{{ route('pembayaran.export') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium">Dari Tanggal</label>
                    <input type="date" name="tanggal_mulai" required
                           class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Sampai Tanggal</label>
                    <input type="date" name="tanggal_selesai" required
                           class="w-full border rounded px-3 py-2">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            @click="openExport = false"
                            class="px-4 py-2 bg-gray-300 rounded">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded">
                        Export
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        {{ $data->appends(request()->query())->links('vendor.pagination.tailwind-custom') }}
    </div>
</div>
@endsection
