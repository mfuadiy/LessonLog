<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateSystemDate extends Command
{
    protected $signature = 'systemdate:update';
    protected $description = 'Menambahkan 1 hari ke tanggal_sistem di tabel settings';

    public function handle()
    {
        $currentDate = DB::table('settings')->where('key', 'tanggal_sistem')->value('value');

        if ($currentDate) {
            $newDate = Carbon::parse($currentDate)->addDay()->format('Y-m-d');

            DB::table('settings')
                ->where('key', 'tanggal_sistem')
                ->update(['value' => $newDate]);

            $this->info("Tanggal sistem diupdate menjadi {$newDate}");
        } else {
            $this->error('Key tanggal_sistem tidak ditemukan di tabel settings.');
        }
    }
}
