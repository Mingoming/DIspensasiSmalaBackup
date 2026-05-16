<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Murni
        $admin = User::updateOrCreate(['email' => 'admin@sma.com'], [
            'name' => 'Admin System',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $admin->roles()->sync([Role::where('name', 'admin')->first()->id]);

        // 2. Kesiswaan Murni
        $kesiswaan = User::updateOrCreate(['email' => 'kesiswaan@sma.com'], [
            'name' => 'Staff Kesiswaan',
            'password' => Hash::make('password'),
            'role' => 'kesiswaan',
            'nip' => '198601012011011001',
        ]);
        $kesiswaan->roles()->sync([Role::where('name', 'kesiswaan')->first()->id]);

        // 3. Guru Mapel + Admin
        $guruAdmin = User::updateOrCreate(['email' => 'budi@sma.com'], [
            'name' => 'Budi Santoso',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '198501012010011001',
            'mata_pelajaran' => 'Matematika',
        ]);
        $guruAdmin->roles()->sync([
            Role::where('name', 'guru_mapel')->first()->id,
            Role::where('name', 'admin')->first()->id,
        ]);

        // 4. Guru Mapel + Kesiswaan
        $guruKesiswaan = User::updateOrCreate(['email' => 'siti@sma.com'], [
            'name' => 'Siti Aminah',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '198702022012012001',
            'mata_pelajaran' => 'Bahasa Indonesia',
        ]);
        $guruKesiswaan->roles()->sync([
            Role::where('name', 'guru_mapel')->first()->id,
            Role::where('name', 'kesiswaan')->first()->id,
        ]);

        // 5. Guru Mapel Murni
        $guruMapel = User::updateOrCreate(['email' => 'ahmad@sma.com'], [
            'name' => 'Ahmad Hidayat',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '198803032013011002',
            'mata_pelajaran' => 'Fisika',
        ]);
        $guruMapel->roles()->sync([Role::where('name', 'guru_mapel')->first()->id]);

        // 6. Siswa (tetap menggunakan kolom role)
        User::updateOrCreate(['email' => 'siswa@sma.com'], [
            'name' => 'Andi Pratama',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nisn' => '0051234567',
            'kelas_id' => Kelas::where('nama_kelas', '10A')->value('id') ?? Kelas::first()?->id,
            'no_telepon' => '081234567890',
        ]);
    }
}
