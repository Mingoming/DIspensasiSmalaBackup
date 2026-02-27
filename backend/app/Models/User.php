<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Keep untuk backward compatibility
        'nisn',
        'nip',
        'mata_pelajaran',
        'kelas_id',
        'no_telepon',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    // Relasi many-to-many ke Role
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    // Helper method: Cek apakah user punya role tertentu
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // Helper method: Cek apakah user punya salah satu dari roles
    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    // Helper method: Get semua nama roles
    public function getRoleNames()
    {
        return $this->roles->pluck('name')->toArray();
    }

    // Helper method: Get display names
    public function getRoleDisplayNames()
    {
        return $this->roles->pluck('display_name')->toArray();
    }

    // Helper method: Cek apakah user adalah admin
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    // Helper method: Cek apakah user adalah kesiswaan
    public function isKesiswaan()
    {
        return $this->hasRole('kesiswaan');
    }

    // Helper method: Cek apakah user adalah guru
    public function isGuru()
    {
        return $this->hasRole('guru_mapel');
    }

    // Helper method: Cek apakah user adalah siswa
    public function isSiswa()
    {
        return $this->role === 'siswa'; // Siswa tetap pakai kolom role
    }

    // Helper method: Cek apakah bisa approve dispensasi
    public function canApproveDispensasi()
    {
        return $this->hasAnyRole(['admin', 'kesiswaan']);
    }

    // Relasi lainnya (existing)
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function dispensasi()
    {
        return $this->hasMany(Dispensasi::class);
    }

    public function dispensasiApproved()
    {
        return $this->hasMany(Dispensasi::class, 'approved_by');
    }
}
