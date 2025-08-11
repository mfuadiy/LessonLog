<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('absensi', 'siswa_id')) {
                $table->unsignedBigInteger('siswa_id')->after('id');
            }
            // Kolom tanggal udah ada, jadi nggak usah ditambah lagi
            if (!Schema::hasColumn('absensi', 'status')) {
                $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->after('tanggal');
            }
            if (!Schema::hasColumn('absensi', 'pertemuan')) {
                $table->integer('pertemuan')->nullable()->after('status');
            }
            if (!Schema::hasColumn('absensi', 'reschedule_date')) {
                $table->date('reschedule_date')->nullable()->after('pertemuan');
            }

            // Foreign key kalau mau
            if (!Schema::hasColumn('absensi', 'siswa_id')) {
                $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            }

            // Drop foreign key dulu (pastikan nama constraint sesuai)
            $table->dropForeign(['user_id']);
            // Hapus kolom user_id
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            //
            $table->dropForeign(['siswa_id']);
            $table->dropColumn(['siswa_id', 'tanggal', 'status', 'pertemuan', 'reschedule_date']);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
