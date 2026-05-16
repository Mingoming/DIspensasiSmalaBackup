<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispensasi extends Model
{
    use HasFactory;

    protected $table = 'dispensasi';

    protected $fillable = [
        'user_id',
        'kelas_id',
        'tanggal',
        'jam_pelajaran_mulai',
        'jam_pelajaran_selesai',
        'mata_pelajaran',
        'keperluan',
        'surat_dispensasi',
        'status',
        'approved_by',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_pelajaran_mulai' => 'integer',
        'jam_pelajaran_selesai' => 'integer',
    ];

    protected $appends = [
        'jam_mulai',
        'jam_selesai',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    public function getJamMulaiAttribute()
    {
        return $this->getJamPelajaranBoundary($this->jam_pelajaran_mulai, 0);
    }

    public function getJamSelesaiAttribute()
    {
        return $this->getJamPelajaranBoundary($this->jam_pelajaran_selesai, 1);
    }

    private function getJamPelajaranBoundary($jamPelajaran, int $index)
    {
        $range = config("dispensasi.jam_pelajaran.{$jamPelajaran}");

        if (!$range) {
            return '-';
        }

        $parts = array_map('trim', explode('-', $range, 2));

        return $parts[$index] ?? '-';
    }

    // Relasi ke User (siswa yang mengajukan)
    public function siswa()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi ke User (guru/admin yang approve)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}
