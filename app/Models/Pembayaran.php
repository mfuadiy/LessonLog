<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayaran';

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'metode',
        'periode',
    ];


    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
