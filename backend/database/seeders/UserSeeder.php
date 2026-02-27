<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Murni
        $admin = User::create([
            'name' => 'Admin System',
            'email' => 'admin@sma.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        // 2. Kesiswaan Murni
        $kesiswaan = User::create([
            'name' => 'Staff Kesiswaan',
            'email' => 'kesiswaan@sma.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '198601012011011001',
        ]);
        $kesiswaan->roles()->attach(Role::where('name', 'kesiswaan')->first());

        // 3. Guru Mapel + Admin
        $guruAdmin = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@sma.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '198501012010011001',
            'mata_pelajaran' => 'Matematika',
        ]);
        $guruAdmin->roles()->attach([
            Role::where('name', 'guru_mapel')->first()->id,
            Role::where('name', 'admin')->first()->id,
        ]);

        // 4. Guru Mapel + Kesiswaan
        $guruKesiswaan = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@sma.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '198702022012012001',
            'mata_pelajaran' => 'Bahasa Indonesia',
        ]);
        $guruKesiswaan->roles()->attach([
            Role::where('name', 'guru_mapel')->first()->id,
            Role::where('name', 'kesiswaan')->first()->id,
        ]);

        // 5. Guru Mapel Murni
        $guruMapel = User::create([
            'name' => 'Ahmad Hidayat',
            'email' => 'ahmad@sma.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '198803032013011002',
            'mata_pelajaran' => 'Fisika',
        ]);
        $guruMapel->roles()->attach(Role::where('name', 'guru_mapel')->first());

        // 6. Siswa (tetap menggunakan kolom role)
        User::create([
            'name' => 'Andi Pratama',
            'email' => 'siswa@sma.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nisn' => '0051234567',
            'kelas_id' => 1,
            'no_telepon' => '081234567890',
        ]);
    }
}
