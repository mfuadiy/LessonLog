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
            // hapus jam masuk dan pulang
            $table->dropColumn(['jam_masuk', 'jam_pulang']);
            // tambahkan pertemuan
            $table->integer('pertemuan')->after('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->dropColumn('pertemuan');
        });
    }
};
