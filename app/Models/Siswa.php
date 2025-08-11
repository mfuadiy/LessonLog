<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = ['nama', 'kelas', 'level', 'jadwal_les'];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
