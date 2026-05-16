<?php

namespace Database\Seeders;

use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JadwalMengajarSeeder extends Seeder
{
    public function run(): void
    {
        $kelas10A = Kelas::where('nama_kelas', '10A')->first();
        $roleGuru = Role::where('name', 'guru_mapel')->first();

        if (!$kelas10A || !$roleGuru) {
            return;
        }

        $template = [
            [
                'email' => 'siti@sma.com',
                'name' => 'Siti Aminah',
                'nip' => '198702022012012001',
                'mapel' => 'Bahasa Indonesia',
                'mulai' => 1,
                'selesai' => 2,
            ],
            [
                'email' => 'budi@sma.com',
                'name' => 'Budi Santoso',
                'nip' => '198501012010011001',
                'mapel' => 'Matematika',
                'mulai' => 3,
                'selesai' => 4,
            ],
            [
                'email' => 'ahmad@sma.com',
                'name' => 'Ahmad Hidayat',
                'nip' => '198803032013011002',
                'mapel' => 'Fisika',
                'mulai' => 5,
                'selesai' => 6,
            ],
            [
                'email' => 'dewi@sma.com',
                'name' => 'Dewi Lestari',
                'nip' => '198904042014012003',
                'mapel' => 'Bahasa Inggris',
                'mulai' => 7,
                'selesai' => 8,
            ],
        ];

        $teachers = collect($template)->mapWithKeys(function (array $item) use ($roleGuru) {
            $teacher = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make('password'),
                    'role' => 'guru',
                    'nip' => $item['nip'],
                    'mata_pelajaran' => $item['mapel'],
                ]
            );
            $teacher->roles()->syncWithoutDetaching([$roleGuru->id]);

            return [$item['email'] => $teacher];
        });

        $mapel = MataPelajaran::whereIn('nama', collect($template)->pluck('mapel'))->get()->keyBy('nama');

        if ($mapel->count() < count($template)) {
            return;
        }

        JadwalMengajar::where('kelas_id', $kelas10A->id)
            ->whereIn('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'])
            ->whereIn('user_id', $teachers->pluck('id')->values())
            ->delete();

        foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $hari) {
            foreach ($template as $item) {
                JadwalMengajar::create([
                    'user_id' => $teachers[$item['email']]->id,
                    'kelas_id' => $kelas10A->id,
                    'mata_pelajaran_id' => $mapel[$item['mapel']]->id,
                    'hari' => $hari,
                    'jam_pelajaran_mulai' => $item['mulai'],
                    'jam_pelajaran_selesai' => $item['selesai'],
                ]);
            }
        }
    }
}
