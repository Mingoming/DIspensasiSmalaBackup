<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('dispensasi.mata_pelajaran', []) as $nama) {
            MataPelajaran::updateOrCreate(
                ['nama' => $nama],
                ['aktif' => true]
            );
        }
    }
}
