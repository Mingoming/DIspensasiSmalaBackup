<?php

namespace Tests\Feature\Kesiswaan;

use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UjiKesiswaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kesiswaan_melihat_semua_pengajuan(): void
    {
        [$kelasA, $kelasB] = $this->createKelasPair();
        $siswaA = $this->createSiswa($kelasA, 'Siswa A', 'siswa-a@example.com');
        $siswaB = $this->createSiswa($kelasB, 'Siswa B', 'siswa-b@example.com');
        $kesiswaan = $this->createKesiswaan();

        $dispensasiA = $this->createDispensasi($siswaA, $kelasA, ['tanggal' => '2026-05-20']);
        $dispensasiB = $this->createDispensasi($siswaB, $kelasB, ['tanggal' => '2026-05-21']);

        Sanctum::actingAs($kesiswaan);

        $response = $this->getJson('/api/dispensasi')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $data = collect($response->json('data'));

        $this->assertEqualsCanonicalizing(
            [$dispensasiA->id, $dispensasiB->id],
            $data->pluck('id')->all()
        );
        $this->assertEqualsCanonicalizing(
            ['Siswa A', 'Siswa B'],
            $data->pluck('siswa.name')->all()
        );
    }

    public function test_kesiswaan_melihat_detail_dan_bukti(): void
    {
        Storage::fake('public');

        $kelas = $this->createKelas('11 IPA 1');
        $siswa = $this->createSiswa($kelas);
        $kesiswaan = $this->createKesiswaan();

        Storage::disk('public')->put('dispensasi/bukti.png', 'fake-proof');

        $dispensasi = $this->createDispensasi($siswa, $kelas, [
            'surat_dispensasi' => 'dispensasi/bukti.png',
        ]);

        Sanctum::actingAs($kesiswaan);

        $this->getJson("/api/dispensasi/{$dispensasi->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $dispensasi->id)
            ->assertJsonPath('data.siswa.name', $siswa->name)
            ->assertJsonPath('data.kelas.nama_kelas', $kelas->nama_kelas)
            ->assertJsonPath('data.surat_dispensasi', 'dispensasi/bukti.png')
            ->assertJsonPath('data.jam_mulai', '07:45')
            ->assertJsonPath('data.jam_selesai', '10:00');

        Storage::disk('public')->assertExists('dispensasi/bukti.png');
    }

    public function test_kesiswaan_menyetujui_dengan_catatan(): void
    {
        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);
        $kesiswaan = $this->createKesiswaan();
        $dispensasi = $this->createDispensasi($siswa, $kelas);

        Sanctum::actingAs($kesiswaan);

        $this->putJson("/api/dispensasi/{$dispensasi->id}/status", [
            'status' => 'approved',
            'catatan' => 'Disetujui untuk lomba sekolah.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.catatan', 'Disetujui untuk lomba sekolah.')
            ->assertJsonPath('data.approved_by', $kesiswaan->id)
            ->assertJsonPath('data.approver.name', $kesiswaan->name);

        $this->assertDatabaseHas('dispensasi', [
            'id' => $dispensasi->id,
            'status' => 'approved',
            'approved_by' => $kesiswaan->id,
            'catatan' => 'Disetujui untuk lomba sekolah.',
        ]);
    }

    public function test_kesiswaan_menolak_dengan_catatan(): void
    {
        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);
        $kesiswaan = $this->createKesiswaan();
        $dispensasi = $this->createDispensasi($siswa, $kelas);

        Sanctum::actingAs($kesiswaan);

        $this->putJson("/api/dispensasi/{$dispensasi->id}/status", [
            'status' => 'rejected',
            'catatan' => 'Bukti pendukung belum sesuai.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.catatan', 'Bukti pendukung belum sesuai.')
            ->assertJsonPath('data.approved_by', $kesiswaan->id);

        $this->assertDatabaseHas('dispensasi', [
            'id' => $dispensasi->id,
            'status' => 'rejected',
            'approved_by' => $kesiswaan->id,
            'catatan' => 'Bukti pendukung belum sesuai.',
        ]);
    }

    public function test_non_kesiswaan_tidak_bisa_approve_reject(): void
    {
        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);
        $dispensasi = $this->createDispensasi($siswa, $kelas);

        Sanctum::actingAs($siswa);

        $this->putJson("/api/dispensasi/{$dispensasi->id}/status", [
            'status' => 'approved',
            'catatan' => 'Tidak boleh diproses siswa.',
        ])->assertForbidden();

        $this->assertDatabaseHas('dispensasi', [
            'id' => $dispensasi->id,
            'status' => 'pending',
            'approved_by' => null,
            'catatan' => null,
        ]);
    }

    public function test_kesiswaan_melihat_analytics_mapel(): void
    {
        $kelas = $this->createKelas();
        $siswa = $this->createSiswa($kelas);
        $kesiswaan = $this->createKesiswaan();

        $this->createDispensasi($siswa, $kelas, [
            'mata_pelajaran' => 'Bahasa Indonesia, Bahasa Inggris, Biologi, Ekonomi',
        ]);
        $this->createDispensasi($siswa, $kelas, [
            'mata_pelajaran' => 'Bahasa Indonesia',
        ]);
        $this->createDispensasi($siswa, $kelas, [
            'mata_pelajaran' => 'Biologi, Bahasa Inggris',
        ]);

        Sanctum::actingAs($kesiswaan);

        $this->getJson('/api/analytics/dispensasi-by-mapel?limit=4')
            ->assertOk()
            ->assertJsonPath('data.0.mata_pelajaran', 'Bahasa Indonesia')
            ->assertJsonPath('data.0.total', 2)
            ->assertJsonPath('data.1.mata_pelajaran', 'Bahasa Inggris')
            ->assertJsonPath('data.1.total', 2)
            ->assertJsonPath('data.2.mata_pelajaran', 'Biologi')
            ->assertJsonPath('data.2.total', 2)
            ->assertJsonPath('data.3.mata_pelajaran', 'Ekonomi')
            ->assertJsonPath('data.3.total', 1);
    }

    /**
     * @return array{Kelas, Kelas}
     */
    private function createKelasPair(): array
    {
        return [
            $this->createKelas('11 IPA 1', '11', 'IPA'),
            $this->createKelas('12 IPS 2', '12', 'IPS'),
        ];
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

    private function createSiswa(
        Kelas $kelas,
        string $name = 'Siswa Test',
        string $email = 'siswa@example.com'
    ): User {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => 'siswa',
            'nisn' => fake()->unique()->numerify('##########'),
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

    private function createDispensasi(User $siswa, Kelas $kelas, array $overrides = []): Dispensasi
    {
        return Dispensasi::create(array_merge([
            'user_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-05-20',
            'jam_pelajaran_mulai' => 2,
            'jam_pelajaran_selesai' => 4,
            'mata_pelajaran' => 'Matematika',
            'keperluan' => 'Mengikuti kegiatan sekolah',
            'status' => 'pending',
        ], $overrides));
    }
}
