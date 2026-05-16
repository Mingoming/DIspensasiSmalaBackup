<?php

namespace Tests\Feature\Guru;

use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\JadwalMengajar;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UjiGuruMapelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_mapel_bisa_login(): void
    {
        $guru = $this->createGuru('guru@example.com');

        $this->postJson('/api/login', [
            'email' => $guru->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', $guru->email)
            ->assertJsonPath('user.roles.0.name', 'guru_mapel')
            ->assertJsonStructure(['token']);
    }

    public function test_guru_mapel_melihat_jadwal_sendiri(): void
    {
        $guru = $this->createGuru('guru@example.com');
        $otherGuru = $this->createGuru('other-guru@example.com');
        $kelas = $this->createKelas();
        $mapel = $this->createMapel('Matematika');

        $ownSchedule = $this->createSchedule($guru, $kelas, $mapel);
        $this->createSchedule($otherGuru, $kelas, $mapel, ['hari' => 'selasa']);

        Sanctum::actingAs($guru);

        $this->getJson('/api/jadwal-mengajar')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownSchedule->id)
            ->assertJsonPath('data.0.guru.name', $guru->name);
    }

    public function test_guru_mapel_melihat_dispensasi_relevan(): void
    {
        [$guru, $kelas, $mapel] = $this->createTeacherScheduleFixture();
        $otherKelas = $this->createKelas('12 IPS 2', '12', 'IPS');

        $relevant = $this->createDispensasi($this->createSiswa($kelas, 'siswa-a@example.com'), $kelas, [
            'tanggal' => '2026-05-18',
            'jam_pelajaran_mulai' => 2,
            'jam_pelajaran_selesai' => 2,
            'mata_pelajaran' => 'Matematika, Fisika',
            'status' => 'approved',
        ]);

        $this->createDispensasi($this->createSiswa($otherKelas, 'siswa-b@example.com'), $otherKelas, [
            'tanggal' => '2026-05-18',
            'mata_pelajaran' => $mapel->nama,
        ]);
        $this->createDispensasi($this->createSiswa($kelas, 'siswa-c@example.com'), $kelas, [
            'tanggal' => '2026-05-19',
            'mata_pelajaran' => $mapel->nama,
        ]);
        $this->createDispensasi($this->createSiswa($kelas, 'siswa-d@example.com'), $kelas, [
            'tanggal' => '2026-05-18',
            'jam_pelajaran_mulai' => 5,
            'jam_pelajaran_selesai' => 6,
            'mata_pelajaran' => $mapel->nama,
        ]);
        $this->createDispensasi($this->createSiswa($kelas, 'siswa-e@example.com'), $kelas, [
            'tanggal' => '2026-05-18',
            'mata_pelajaran' => 'Biologi',
        ]);

        Sanctum::actingAs($guru);

        $this->getJson('/api/dispensasi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $relevant->id)
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('data.0.jadwal_mengajar_guru.0.mata_pelajaran.nama', 'Matematika');
    }

    public function test_guru_mapel_melihat_detail_relevan_saja(): void
    {
        [$guru, $kelas, $mapel] = $this->createTeacherScheduleFixture();
        $relevant = $this->createDispensasi($this->createSiswa($kelas, 'siswa-a@example.com'), $kelas, [
            'tanggal' => '2026-05-18',
            'mata_pelajaran' => $mapel->nama,
            'status' => 'approved',
        ]);
        $unrelated = $this->createDispensasi($this->createSiswa($kelas, 'siswa-b@example.com'), $kelas, [
            'tanggal' => '2026-05-18',
            'mata_pelajaran' => 'Biologi',
        ]);

        Sanctum::actingAs($guru);

        $this->getJson("/api/dispensasi/{$relevant->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $relevant->id)
            ->assertJsonPath('data.siswa.email', 'siswa-a@example.com')
            ->assertJsonPath('data.kelas.nama_kelas', $kelas->nama_kelas)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.jadwal_mengajar_guru.0.mata_pelajaran.nama', $mapel->nama);

        $this->getJson("/api/dispensasi/{$unrelated->id}")
            ->assertForbidden();
    }

    public function test_guru_mapel_tidak_bisa_approve_reject(): void
    {
        [$guru, $kelas, $mapel] = $this->createTeacherScheduleFixture();
        $pending = $this->createDispensasi($this->createSiswa($kelas, 'pending@example.com'), $kelas, [
            'tanggal' => '2026-05-18',
            'mata_pelajaran' => $mapel->nama,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($guru);

        $this->putJson("/api/dispensasi/{$pending->id}/status", [
            'status' => 'approved',
        ])->assertForbidden();

        $this->assertDatabaseHas('dispensasi', [
            'id' => $pending->id,
            'status' => 'pending',
            'approved_by' => null,
        ]);
    }

    /**
     * @return array{User, Kelas, MataPelajaran, JadwalMengajar}
     */
    private function createTeacherScheduleFixture(): array
    {
        $guru = $this->createGuru('guru@example.com');
        $kelas = $this->createKelas();
        $mapel = $this->createMapel('Matematika');
        $jadwal = $this->createSchedule($guru, $kelas, $mapel);

        return [$guru, $kelas, $mapel, $jadwal];
    }

    private function createGuru(string $email): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'guru_mapel'],
            ['display_name' => 'Guru Mata Pelajaran', 'description' => 'Guru mata pelajaran']
        );

        $guru = User::factory()->create([
            'name' => 'Guru Mapel',
            'email' => $email,
            'role' => 'guru',
            'nip' => fake()->unique()->numerify('##################'),
        ]);

        $guru->roles()->attach($role);

        return $guru;
    }

    private function createSiswa(Kelas $kelas, string $email = 'siswa@example.com'): User
    {
        return User::factory()->create([
            'email' => $email,
            'role' => 'siswa',
            'nisn' => fake()->unique()->numerify('##########'),
            'kelas_id' => $kelas->id,
        ]);
    }

    private function createKelas(
        string $namaKelas = '11 IPA 1',
        string $tingkat = '11',
        ?string $jurusan = 'IPA'
    ): Kelas {
        return Kelas::create([
            'nama_kelas' => $namaKelas,
            'tingkat' => $tingkat,
            'jurusan' => $jurusan,
        ]);
    }

    private function createMapel(string $nama): MataPelajaran
    {
        return MataPelajaran::create([
            'nama' => $nama,
            'aktif' => true,
        ]);
    }

    private function createSchedule(
        User $guru,
        Kelas $kelas,
        MataPelajaran $mapel,
        array $overrides = []
    ): JadwalMengajar {
        return JadwalMengajar::create(array_merge([
            'user_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'senin',
            'jam_pelajaran_mulai' => 2,
            'jam_pelajaran_selesai' => 3,
        ], $overrides));
    }

    private function createDispensasi(User $siswa, Kelas $kelas, array $overrides = []): Dispensasi
    {
        return Dispensasi::create(array_merge([
            'user_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-05-18',
            'jam_pelajaran_mulai' => 2,
            'jam_pelajaran_selesai' => 3,
            'mata_pelajaran' => 'Matematika',
            'keperluan' => 'Mengikuti kegiatan sekolah',
            'status' => 'pending',
        ], $overrides));
    }
}
