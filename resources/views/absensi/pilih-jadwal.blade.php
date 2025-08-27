@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <i class="bi bi-calendar-check text-blue-600"></i> Absensi Les
    </h3>

    <!-- Pilih Jadwal -->
    <div class="mb-4">
    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">
        Pilih Tanggal
    </label>
    <input type="date" id="tanggal" name="tanggal" 
           class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500">
    </div>

    <div class="mb-4">
        <label for="jadwal" class="block text-sm font-medium text-gray-700 mb-1">
            Pilih Jadwal Les
        </label>
        <select id="jadwal" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500">
            <option value="">-- Pilih Jadwal --</option>
            @foreach($jadwal as $j)
                <option value="{{ $j }}">{{ $j }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-6">
    <a href="{{ url()->previous() }}" 
       class="inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-500">
        ← Kembali
    </a>
    </div>

    <!-- Container Absensi -->
    <div id="absen-container" class="hidden mt-6">
        <!-- Info siswa -->
        <h5 id="nama-siswa" class="text-lg font-semibold text-gray-700 mb-4"></h5>

        <!-- Tombol Absensi -->
        <div class="flex flex-wrap gap-2 mb-6">
            <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" onclick="kirimAbsensi('Hadir')">Hadir</button>
            <button class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600" onclick="kirimAbsensi('Izin')">Izin</button>
            <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" onclick="kirimAbsensi('Sakit')">Sakit</button>
            <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="kirimAbsensi('Alpa')">Alpa</button>
            <button class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600" onclick="mintaReschedule()">Reschedule</button>
        </div>

        <!-- Koreksi Absensi -->
        <div id="koreksi-container" class="hidden p-4 bg-gray-100 rounded border border-gray-300 mb-6">
            <p class="font-semibold text-gray-700">Koreksi Absensi:</p>
            <p id="info-koreksi" class="text-sm text-gray-600 mb-3"></p>
            <div class="flex flex-wrap gap-2">
                <button class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200" onclick="koreksiAbsensi('Hadir')">Hadir</button>
                <button class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200" onclick="koreksiAbsensi('Izin')">Izin</button>
                <button class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200" onclick="koreksiAbsensi('Sakit')">Sakit</button>
                <button class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200" onclick="koreksiAbsensi('Alpa')">Alpa</button>
                <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200" onclick="koreksiAbsensi('Reschedule')">Reschedule</button>
            </div>
        </div>

        <!-- Progress -->
        <div class="mb-6">
            <div class="flex justify-between text-sm mb-1">
                <span id="progress-text" class="font-medium text-gray-600">0 / 0</span>
                <span id="progress-percent" class="font-medium text-gray-600">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div id="progress-bar" 
                     class="h-4 bg-red-500 text-xs font-bold text-center text-white leading-4 transition-all duration-300" 
                     style="width:0%">
                     0%
                </div>
            </div>
        </div>

        <!-- Navigasi -->
        <div class="flex justify-between">
            <button class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500" onclick="backSiswa()">Back</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="nextSiswa()">Next</button>
        </div>
    </div>
</div>

<!-- Modal Reschedule -->
<div id="modalReschedule" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h5 class="text-lg font-bold mb-4">Pilih Tanggal Reschedule</h5>
        <input type="date" id="rescheduleDate" 
               class="w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 mb-4">
        <div class="flex justify-end gap-2">
            <button class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" onclick="closeModal()">Batal</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="submitReschedule()">Simpan</button>
        </div>
    </div>
</div>

<script>
    let siswaData = [];
    let currentIndex = 0;
    let absensiHistory = {};

    const absenContainer = document.getElementById('absen-container');
    const koreksiContainer = document.getElementById('koreksi-container');
    const infoKoreksi = document.getElementById('info-koreksi');
    const modalReschedule = document.getElementById('modalReschedule');

    // Event ketika ganti jadwal
    document.getElementById('jadwal').addEventListener('change', loadSiswa);
    document.getElementById('tanggal').addEventListener('change', loadSiswa);

    function loadSiswa() {
        let jadwal = document.getElementById('jadwal').value;
        let tanggal = document.getElementById('tanggal').value;

        // Validasi wajib isi tanggal & jadwal
        if (!jadwal) {
            absenContainer.classList.add('hidden');
            return;
        }
        
        if (!tanggal) {
            alert("⚠️ Harap pilih tanggal terlebih dahulu.");
            return;
        }

        fetch(`/absensi/get-siswa/${jadwal}?tanggal=${tanggal}`)
            .then(res => res.json())
            .then(data => {
                siswaData = data;
                currentIndex = 0;
                absensiHistory = {};

                siswaData.forEach(s => {
                    if (s.absensi_status) {
                        absensiHistory[s.id] = {
                            status: s.absensi_status,
                            id: s.absensi_id,
                            reschedule_date: s.reschedule_date
                        };
                    }
                });

                if (siswaData.length > 0) {
                    tampilkanSiswa(currentIndex);
                    updateProgress();
                    absenContainer.classList.remove('hidden');
                } else {
                    alert("❌ Tidak ada siswa pada jadwal dan tanggal ini.");
                    absenContainer.classList.add('hidden');
                }
            });
    }

    function tampilkanSiswa(index) {
        document.getElementById('nama-siswa').innerText = 
            `(${index+1} dari ${siswaData.length}) ${siswaData[index].nama} - ${siswaData[index].kelas}`;

        if (absensiHistory[siswaData[index].id]) {
            koreksiContainer.classList.remove('hidden');
            infoKoreksi.innerText = `Status sebelumnya: ${absensiHistory[siswaData[index].id].status}`;
        } else {
            koreksiContainer.classList.add('hidden');
        }
    }

    function kirimAbsensi(status) {
        const siswa = siswaData[currentIndex];
        const tanggal = document.getElementById('tanggal').value;

        fetch("{{ route('absensi.storeAjax') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ siswa_id: siswa.id, status, tanggal })
        })
        .then(res => res.json())
        .then(res => {
            absensiHistory[siswa.id] = { status, id: res.data.id };
            tampilkanSiswa(currentIndex);
            updateProgress();
            setTimeout(nextSiswa, 400);
        });
    }

    function koreksiAbsensi(status) {
        const siswa = siswaData[currentIndex];
        const tanggal = document.getElementById('tanggal').value;

        if (status === 'Reschedule') {
            modalReschedule.classList.remove('hidden');
            return;
        }

        fetch("{{ route('absensi.storeAjax') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ siswa_id: siswa.id, status, tanggal, reschedule_date: null })
        })
        .then(res => res.json())
        .then(res => {
            absensiHistory[siswa.id] = { status, id: res.data.id };
            alert("✅ Koreksi absensi berhasil diubah ke: " + status);
            tampilkanSiswa(currentIndex);
        });
    }

    function nextSiswa() {
        if (currentIndex < siswaData.length - 1) {
            currentIndex++;
            tampilkanSiswa(currentIndex);
            updateProgress();
        } else {
            updateProgress(true);
            alert("✅ Semua siswa sudah diabsen!");
        }
    }

    function backSiswa() {
        if (currentIndex > 0) {
            currentIndex--;
            tampilkanSiswa(currentIndex);
            updateProgress();
        } else {
            alert("⚠️ Ini siswa pertama.");
        }
    }

    function updateProgress(finish = false) {
        let total = siswaData.length;
        let current = finish ? total : currentIndex + 1;
        let percent = Math.round((current / total) * 100);

        document.getElementById('progress-text').innerText = `${current} / ${total}`;
        document.getElementById('progress-percent').innerText = `${percent}%`;

        let bar = document.getElementById('progress-bar');
        bar.style.width = percent + "%";
        bar.innerText = percent + "%";

        bar.classList.remove("bg-red-500", "bg-yellow-500", "bg-green-500");
        if (percent < 40) {
            bar.classList.add("bg-red-500");
        } else if (percent < 70) {
            bar.classList.add("bg-yellow-500");
        } else {
            bar.classList.add("bg-green-500");
        }
    }

    function mintaReschedule() {
        modalReschedule.classList.remove('hidden');
    }

    function closeModal() {
        modalReschedule.classList.add('hidden');
    }

    function submitReschedule() {
        const rescheduleDate = document.getElementById('rescheduleDate').value;
        const tanggal = document.getElementById('tanggal').value;
        const siswa = siswaData[currentIndex];

        if (!tanggal) {
            alert("⚠️ Harap pilih tanggal absensi terlebih dahulu.");
            return;
        }
        if (!rescheduleDate) {
            alert("⚠️ Harap pilih tanggal reschedule terlebih dahulu.");
            return;
        }

        fetch("{{ route('absensi.reschedule') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ siswa_id: siswa.id, tanggal, reschedule_date: rescheduleDate })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                absensiHistory[siswa.id] = { status: "Reschedule", id: data.data.id, reschedule_date: rescheduleDate };
                alert("✅ Reschedule berhasil disimpan untuk " + siswa.nama);
                closeModal();
                tampilkanSiswa(currentIndex);
            } else {
                alert("❌ Gagal menyimpan reschedule.");
            }
        });
    }
</script>
@endsection
