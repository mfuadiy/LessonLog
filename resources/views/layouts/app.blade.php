<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Fitri Course</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/3135/3135768.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Topbar -->
    <header class="bg-blue-600 text-white flex items-center justify-between px-4 py-3 shadow-md fixed top-0 left-0 right-0 z-40 lg:pl-64 transition-all duration-300">
    <div class="flex items-center gap-3 ml-3 justify-start w-full">
        <!-- Tombol toggle sidebar -->
        <button id="toggleSidebar" class="lg:hidden text-white text-2xl focus:outline-none">
            <i class="bi bi-list"></i>
        </button>

        <!-- Tanggal & Jam (Desktop) -->
        <span id="tanggalJamPC" class="hidden sm:block font-semibold cursor-pointer">
            <i class="bi bi-calendar-event"></i>
        </span>

        <!-- Jam (Mobile) -->
        <span id="jamHP" class="sm:hidden font-semibold"></span>

        <!-- Input Date Picker Transparan -->
        <input type="date" id="manualDate" class="absolute opacity-0 w-0 h-0" />
    </div>

    <div class="flex items-center gap-4 justify-end w-full">
        <div class="relative">
            <button class="flex items-center gap-1" id="userMenuBtn">
                <i class="bi bi-person-circle text-xl"></i> 
                <span class="hidden sm:inline">Akun</span>
            </button>
            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md text-gray-700 z-50">
                <a href="#" class="block px-4 py-2 hover:bg-gray-100">
                    <i class="bi bi-gear"></i> Pengaturan
                </a>
                <hr>
                <form action="" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

    <!-- Overlay saat sidebar dibuka -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden lg:hidden z-40"></div>

    <!-- Layout -->
    <div class="flex flex-1 pt-16">
        <!-- Sidebar -->
        <aside id="sidebar" 
            class="bg-gray-900 text-white w-64 flex flex-col justify-between fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50">

            <div>
                <h4 class="text-lg font-bold px-4 mb-6 flex items-center justify-between">
                    <span><i class="bi bi-mortarboard"></i> Fitri Course</span>
                    <!-- Tombol Close Sidebar (hanya di HP) -->
                    <button id="closeSidebar" class="lg:hidden text-white text-xl">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </h4>
                <nav class="space-y-1">
                    <a href="{{ route('siswa.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 rounded-md hover:bg-gray-700 transition {{ request()->is('siswa*') ? 'bg-blue-600' : '' }}">
                        <i class="bi bi-people"></i> Data Siswa
                    </a>
                    <a href="{{ route('absensi.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 rounded-md hover:bg-gray-700 transition {{ request()->is('absensi*') ? 'bg-blue-600' : '' }}">
                        <i class="bi bi-calendar-check"></i> Data Absensi
                    </a>
                    <a href="{{ route('pembayaran.index') }}" 
                    class="flex items-center gap-2 px-4 py-2 rounded-md hover:bg-gray-700 transition {{ request()->is('pembayaran*') ? 'bg-blue-600' : '' }}">
                        <i class="bi bi-cash-coin"></i> Data Pembayaran
                    </a>

                </nav>
            </div>

            <!-- Divider + Tombol bawah -->
            <div class="border-t border-gray-700 mt-6 px-4 py-4 space-y-2">
                <a href="{{ route('siswa.create') }}" 
                   class="block w-full text-center bg-green-600 hover:bg-green-700 px-3 py-2 rounded-md">
                   <i class="bi bi-person-plus"></i> Tambah Siswa
                </a>
                <a href="{{ route('absensi.pilihJadwal') }}" 
                   class="block w-full text-center bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded-md">
                   <i class="bi bi-calendar-plus"></i> Tambah Absensi
                </a>
            </div>
        </aside>

        <!-- Konten -->
        <main class="flex-1 p-4 lg:ml-64 transition-all duration-300">
            @yield('content')
        </main>
    </div>

    <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');
    const tanggalJamPC = document.getElementById('tanggalJamPC');
    const jamHP = document.getElementById('jamHP');
    const manualDate = document.getElementById('manualDate');

    let selectedDate = null; // Menyimpan tanggal pilihan user

    // Buka sidebar
    toggleBtn?.addEventListener('click', () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    });

    // Tutup sidebar via tombol X
    closeBtn?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    // Klik overlay → tutup sidebar
    overlay?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    // Toggle dropdown user
    userMenuBtn.addEventListener('click', () => {
        userMenu.classList.toggle('hidden');
    });

    // Klik tanggal → buka date picker
    tanggalJamPC.addEventListener('click', () => {
        if (manualDate.showPicker) {
            manualDate.showPicker(); // Browser modern
        } else {
            manualDate.click(); // Browser lama
        }
    });
    jamHP.addEventListener('click', () => {
        if (manualDate.showPicker) {
            manualDate.showPicker(); // Browser modern
        } else {
            manualDate.click(); // Browser lama
        }
    });

    // Saat user pilih tanggal
    manualDate.addEventListener('change', () => {
        selectedDate = manualDate.value; // format YYYY-MM-DD
        updateJam();
    });

   // ====================================
// SETTING UNTUK TESTING
// ====================================
// Null  => pakai tanggal asli
// String => format "YYYY-MM-DD" untuk testing
let testingDate = null; // Contoh: "2025-08-15"

// Jam + Tanggal realtime
function updateJam() {
    // Kalau testingDate ada, pakai itu, kalau nggak pakai tanggal asli
    let now;
    if (testingDate) {
        const [year, month, day] = testingDate.split("-");
        now = new Date(year, month - 1, day);
    } else {
        now = new Date();
    }

    // Format tanggal
    const displayDate = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

    // Jam (kalau testing date, tetap update jam realtime)
    const jamNow = new Date();
    const jam = String(jamNow.getHours()).padStart(2, '0');
    const menit = String(jamNow.getMinutes()).padStart(2, '0');
    const detik = String(jamNow.getSeconds()).padStart(2, '0');
    const waktu = `${jam}:${menit}:${detik}`;

    // Desktop
    tanggalJamPC.innerHTML = `<i class="bi bi-calendar-event"></i> ${displayDate} | ${waktu}`;
    // Mobile
    jamHP.innerHTML = `<i class="bi bi-calendar-event"></i> ${displayDate}`;
}

setInterval(updateJam, 1000);
updateJam();

</script>
</body>
</html>
