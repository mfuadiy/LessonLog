<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            ['key' => 'tanggal_sistem', 'value' => now()->format('Y-m-d')],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta'],
            ['key' => 'jam_mulai_kelas', 'value' => '08:00'],
            ['key' => 'jam_selesai_kelas', 'value' => '16:00'],
            ['key' => 'maks_jumlah_peserta', 'value' => '30'],
            ['key' => 'durasi_pertemuan', 'value' => '90'],
            ['key' => 'nama_lembaga', 'value' => 'Course Academy'],
            ['key' => 'logo_lembaga', 'value' => 'uploads/logo.png'],
            ['key' => 'kontak_admin', 'value' => '+628123456789'],
            ['key' => 'email_admin', 'value' => 'admin@course.com'],
            ['key' => 'tema_warna', 'value' => '#1E40AF'],
            ['key' => 'metode_pembayaran', 'value' => 'transfer_bank,ewallet'],
            ['key' => 'notifikasi_auto', 'value' => '1'],
            ['key' => 'pesan_welcome', 'value' => 'Selamat datang di Course Academy!'],
        ]);
    }
}
