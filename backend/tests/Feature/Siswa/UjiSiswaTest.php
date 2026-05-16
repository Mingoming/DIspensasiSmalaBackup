<?php

namespace Tests\Feature\Siswa;

use App\Models\Kelas;
use App\Models\JadwalMengajar;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UjiSiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_mengajukan_dispensasi_tanpa_bukti(): void
    {
        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);
        Sanctum::actingAs($siswa);

        $response = $this->postJson('/api/dispensasi', $this->dispensasiPayload([
            'keperluan' => 'Mengikuti lomba debat',
        ]));

        $response
            ->assertCreated()
            ->assertJsonPath('data.siswa.name', $siswa->name)
            ->assertJsonPath('data.kelas.nama_kelas', $kelas->nama_kelas)
            ->assertJsonPath('data.tanggal', '2026-05-20')
            ->assertJsonPath('data.mata_pelajaran', 'Matematika, Fisika')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.surat_dispensasi', null);

        $this->assertDatabaseHas('dispensasi', [
            'user_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'jam_pelajaran_mulai' => 2,
            'jam_pelajaran_selesai' => 4,
            'mata_pelajaran' => 'Matematika, Fisika',
            'keperluan' => 'Mengikuti lomba debat',
            'status' => 'pending',
            'surat_dispensasi' => null,
        ]);

        $this->getJson('/api/dispensasi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_siswa_mengajukan_dispensasi_dengan_bukti(): void
    {
        Storage::fake('public');

        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);
        Sanctum::actingAs($siswa);

        $response = $this->post('/api/dispensasi', $this->dispensasiPayload([
            'surat_dispensasi' => UploadedFile::fake()->image('bukti.png'),
        ]));

        $path = $response->json('data.surat_dispensasi');

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_siswa_melihat_mapel_terdampak(): void
    {
        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);

        $this->createScheduleBlock($kelas, 'Bahasa Indonesia', 1, 2);
        $this->createScheduleBlock($kelas, 'Matematika', 3, 4);
        $this->createScheduleBlock($kelas, 'Fisika', 5, 6);
        $this->createScheduleBlock($kelas, 'Bahasa Inggris', 7, 8);

        Sanctum::actingAs($siswa);

        $this->getJson('/api/jadwal-terdampak?tanggal=2026-05-18&jam_pelajaran_mulai=1&jam_pelajaran_selesai=8')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('mata_pelajaran.0', 'Bahasa Indonesia')
            ->assertJsonPath('mata_pelajaran.1', 'Matematika')
            ->assertJsonPath('mata_pelajaran.2', 'Fisika')
            ->assertJsonPath('mata_pelajaran.3', 'Bahasa Inggris');

        $this->getJson('/api/jadwal-terdampak?tanggal=2026-05-18&jam_pelajaran_mulai=5&jam_pelajaran_selesai=8')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('mata_pelajaran.0', 'Fisika')
            ->assertJsonPath('mata_pelajaran.1', 'Bahasa Inggris');
    }

    public function test_siswa_melihat_status_dan_detail_disetujui(): void
    {
        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);
        $kesiswaan = $this->createKesiswaan();

        Sanctum::actingAs($siswa);

        $dispensasiId = $this->postJson('/api/dispensasi', $this->dispensasiPayload())
            ->assertCreated()
            ->json('data.id');

        Sanctum::actingAs($kesiswaan);

        $this->putJson("/api/dispensasi/{$dispensasiId}/status", [
            'status' => 'approved',
            'catatan' => 'Disetujui untuk kegiatan sekolah',
        ])->assertOk();

        Sanctum::actingAs($siswa);

        $this->getJson('/api/dispensasi')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'approved');

        $this->getJson("/api/dispensasi/{$dispensasiId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.catatan', 'Disetujui untuk kegiatan sekolah')
            ->assertJsonPath('data.approver.name', $kesiswaan->name)
            ->assertJsonPath('data.jam_mulai', '07:45')
            ->assertJsonPath('data.jam_selesai', '10:00');
    }

    public function test_non_siswa_tidak_bisa_mengajukan_dispensasi(): void
    {
        $kesiswaan = $this->createKesiswaan();
        Sanctum::actingAs($kesiswaan);

        $this->postJson('/api/dispensasi', $this->dispensasiPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('dispensasi', 0);
    }

    private function createKelas(): Kelas
    {
        return Kelas::create([
            'nama_kelas' => '11 IPA 1',
            'tingkat' => '11',
            'jurusan' => 'IPA',
        ]);
    }

    private function createSiswa(Kelas $kelas): User
    {
        return User::factory()->create([
            'name' => 'Siswa Test',
            'email' => 'siswa@example.com',
            'role' => 'siswa',
            'nisn' => '1234567890',
            'kelas_id' => $kelas->id,
        ]);
    }

    private function createKesiswaan(): User
    {
        $role = Role::create([
            'name' => 'kesiswaan',
            'display_name' => 'Kesiswaan',
            'description' => 'Mengelola data siswa dan dispensasi',
        ]);

        $user = User::factory()->create([
            'name' => 'Petugas Kesiswaan',
            'email' => 'kesiswaan@example.com',
            'role' => 'guru',
        ]);

        $user->roles()->attach($role);

        return $user;
    }

    private function createScheduleBlock(Kelas $kelas, string $mapelName, int $mulai, int $selesai): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'guru_mapel'],
            ['display_name' => 'Guru Mata Pelajaran', 'description' => 'Mengajar mata pelajaran tertentu']
        );

        $guru = User::factory()->create([
            'role' => 'guru',
            'nip' => fake()->unique()->numerify('##################'),
            'mata_pelajaran' => $mapelName,
        ]);
        $guru->roles()->attach($role);

        $mapel = MataPelajaran::create([
            'nama' => $mapelName,
            'aktif' => true,
        ]);

        JadwalMengajar::create([
            'user_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'senin',
            'jam_pelajaran_mulai' => $mulai,
            'jam_pelajaran_selesai' => $selesai,
        ]);
    }

    private function dispensasiPayload(array $overrides = []): array
    {
        return array_merge([
            'tanggal' => '2026-05-20',
            'jam_pelajaran_mulai' => 2,
            'jam_pelajaran_selesai' => 4,
            'mata_pelajaran' => ['Matematika', 'Fisika'],
            'keperluan' => 'Mengikuti kegiatan sekolah',
        ], $overrides);
    }
}
