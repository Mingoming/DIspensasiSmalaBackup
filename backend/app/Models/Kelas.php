<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan'
    ];

    // Relasi ke User (siswa)
    public function siswa()
    {
        return $this->hasMany(User::class);
    }

    // Relasi ke Dispensasi
    public function dispensasi()
    {
        return $this->hasMany(Dispensasi::class);
    }
}
