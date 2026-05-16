<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalMengajar extends Model
{
    use HasFactory;

    protected $table = 'jadwal_mengajar';

    protected $fillable = [
        'user_id',
        'kelas_id',
        'mata_pelajaran_id',
        'hari',
        'jam_pelajaran_mulai',
        'jam_pelajaran_selesai',
    ];

    protected $casts = [
        'jam_pelajaran_mulai' => 'integer',
        'jam_pelajaran_selesai' => 'integer',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }
}
