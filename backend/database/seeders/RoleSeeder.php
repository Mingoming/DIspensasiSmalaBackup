<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Memiliki akses penuh ke semua fitur sistem'
            ],
            [
                'name' => 'kesiswaan',
                'display_name' => 'Kesiswaan',
                'description' => 'Mengelola data siswa dan dispensasi'
            ],
            [
                'name' => 'guru_mapel',
                'display_name' => 'Guru Mata Pelajaran',
                'description' => 'Mengajar mata pelajaran tertentu'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
