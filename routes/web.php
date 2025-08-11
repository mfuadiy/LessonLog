<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SettingController;
use Faker\Provider\ar_EG\Person;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('absensi.index');
});

// resource route untuk Siswa
Route::resource('siswa', SiswaController::class);

// resource route untuk Absensi
Route::resource('absensi', AbsensiController::class)->except(['show', 'create']);
Route::get('/absensi/pilih-jadwal', [AbsensiController::class, 'pilihJadwal'])->name('absensi.pilihJadwal');
Route::get('/absensi/get-siswa/{jadwal}', [AbsensiController::class, 'getSiswa'])->name('absensi.getSiswa');
Route::post('/absensi/store-ajax', [AbsensiController::class, 'storeAjax'])->name('absensi.storeAjax');
Route::post('/absensi/reschedule', [AbsensiController::class, 'reschedule'])->name('absensi.reschedule');

// resource route untuk Pembayaran  
Route::resource('pembayaran', PembayaranController::class);


// resource route untuk Setting
Route::post('/update-tanggal', [SettingController::class, 'updateDate'])->name('update-tanggal');
