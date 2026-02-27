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
        'catatan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_pelajaran_mulai' => 'integer',
        'jam_pelajaran_selesai' => 'integer',
    ];

    // Date format accessors for dd/mm/yy format
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('d/m/y');
    }

    // Accessor for formatted tanggal
    public function getTanggalFormattedAttribute(): string
    {
        return $this->tanggal ? $this->tanggal->format('d/m/y') : '';
    }

    // Accessor for full date format (dd/mm/yyyy)
    public function getTanggalFullAttribute(): string
    {
        return $this->tanggal ? $this->tanggal->format('d/m/Y') : '';
    }

    public function getJamMulaiAttribute()
    {
        return $this->convertJamPelajaranToTime($this->jam_pelajaran_mulai);
    }

    public function getJamSelesaiAttribute()
    {
        return $this->convertJamPelajaranToTime($this->jam_pelajaran_selesai);
    }

    private function convertJamPelajaranToTime($jamPelajaran)
    {
        $jamMap = [
            1 => '07:00',
            2 => '07:45',
            3 => '08:30',
            4 => '09:15',
            5 => '10:15',
            6 => '11:00',
            7 => '11:45',
            8 => '12:30',
        ];

        return $jamMap[$jamPelajaran] ?? '-';
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
