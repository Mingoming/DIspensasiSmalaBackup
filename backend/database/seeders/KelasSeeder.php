<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $kelasData = [
            // Kelas 10
            ['nama_kelas' => '10A', 'tingkat' => '10', 'jurusan' => null],
            ['nama_kelas' => '10B', 'tingkat' => '10', 'jurusan' => null],
            ['nama_kelas' => '10C', 'tingkat' => '10', 'jurusan' => null],

            // Kelas 11
            ['nama_kelas' => '11 IPA 1', 'tingkat' => '11', 'jurusan' => 'IPA'],
            ['nama_kelas' => '11 IPA 2', 'tingkat' => '11', 'jurusan' => 'IPA'],
            ['nama_kelas' => '11 IPS 1', 'tingkat' => '11', 'jurusan' => 'IPS'],

            // Kelas 12
            ['nama_kelas' => '12 IPA 1', 'tingkat' => '12', 'jurusan' => 'IPA'],
            ['nama_kelas' => '12 IPS 1', 'tingkat' => '12', 'jurusan' => 'IPS'],
        ];

        foreach ($kelasData as $kelas) {
            Kelas::create($kelas);
        }
    }
}
