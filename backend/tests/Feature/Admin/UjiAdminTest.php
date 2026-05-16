<?php

namespace Tests\Feature\Admin;

use App\Exports\DispensasiExport;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UjiAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_mengelola_user_dan_role_ganda(): void
    {
        $admin = $this->createUserWithRole('admin', 'admin@example.com');
        $this->role('guru_mapel', 'Guru Mata Pelajaran');
        $this->role('kesiswaan', 'Kesiswaan');
        $kelas = $this->createKelas();

        Sanctum::actingAs($admin);

        $created = $this->postJson('/api/users', [
            'name' => 'Staff Kesiswaan',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'role' => 'guru',
            'roles' => ['admin', 'kesiswaan'],
            'nip' => '198601012011011001',
            'mata_pelajaran' => 'Matematika',
            'no_telepon' => '081234567890',
        ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'guru')
            ->json('data');

        $roleNames = collect($created['roles'])->pluck('name')->all();
        $this->assertEqualsCanonicalizing(['admin', 'guru_mapel', 'kesiswaan'], $roleNames);

        $this->putJson("/api/users/{$created['id']}", [
            'name' => 'Siswa Mutasi',
            'email' => 'staff@example.com',
            'role' => 'siswa',
            'nisn' => '0051234567',
            'kelas_id' => $kelas->id,
            'no_telepon' => '081234567890',
        ])
            ->assertOk()
            ->assertJsonPath('data.role', 'siswa')
            ->assertJsonPath('data.nip', null)
            ->assertJsonPath('data.mata_pelajaran', null)
            ->assertJsonCount(0, 'data.roles');

        $this->assertDatabaseHas('users', [
            'id' => $created['id'],
            'role' => 'siswa',
            'nisn' => '0051234567',
            'nip' => null,
            'kelas_id' => $kelas->id,
        ]);
        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $created['id'],
        ]);
    }

    public function test_admin_mengelola_kelas_dan_mapel(): void
    {
        Sanctum::actingAs($this->createUserWithRole('admin', 'admin@example.com'));

        $kelasId = $this->postJson('/api/kelas', [
            'nama_kelas' => '12 IPA 3',
            'tingkat' => '12',
            'jurusan' => 'IPA',
        ])
            ->assertCreated()
            ->assertJsonPath('data.nama_kelas', '12 IPA 3')
            ->json('data.id');

        $this->putJson("/api/kelas/{$kelasId}", [
            'nama_kelas' => '12 IPA 4',
            'tingkat' => '12',
            'jurusan' => 'IPA',
        ])
            ->assertOk()
            ->assertJsonPath('data.nama_kelas', '12 IPA 4');

        $mapelId = $this->postJson('/api/mata-pelajaran', [
            'nama' => 'Informatika',
            'aktif' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.nama', 'Informatika')
            ->json('data.id');

        $this->putJson("/api/mata-pelajaran/{$mapelId}", [
            'nama' => 'Informatika Lanjut',
            'aktif' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Informatika Lanjut')
            ->assertJsonPath('data.aktif', false);

        $this->deleteJson("/api/mata-pelajaran/{$mapelId}")->assertOk();
        $this->deleteJson("/api/kelas/{$kelasId}")->assertOk();
    }

    public function test_admin_mengelola_jadwal_mengajar(): void
    {
        Sanctum::actingAs($this->createUserWithRole('admin', 'admin@example.com'));

        $guru = $this->createUserWithRole('guru', 'guru@example.com');
        $kelas = $this->createKelas();
        $mapel = MataPelajaran::create(['nama' => 'Matematika', 'aktif' => true]);

        $jadwalId = $this->postJson('/api/jadwal-mengajar', [
            'user_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'senin',
            'jam_pelajaran_mulai' => 1,
            'jam_pelajaran_selesai' => 2,
        ])
            ->assertCreated()
            ->assertJsonPath('data.guru.name', $guru->name)
            ->assertJsonPath('data.kelas.nama_kelas', $kelas->nama_kelas)
            ->assertJsonPath('data.mata_pelajaran.nama', 'Matematika')
            ->json('data.id');

        $this->postJson('/api/jadwal-mengajar', [
            'user_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'senin',
            'jam_pelajaran_mulai' => 2,
            'jam_pelajaran_selesai' => 3,
        ])->assertUnprocessable();

        $this->putJson("/api/jadwal-mengajar/{$jadwalId}", [
            'user_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'selasa',
            'jam_pelajaran_mulai' => 3,
            'jam_pelajaran_selesai' => 4,
        ])
            ->assertOk()
            ->assertJsonPath('data.hari', 'selasa')
            ->assertJsonPath('data.jam_pelajaran_mulai', 3);

        $this->getJson('/api/jadwal-mengajar')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/jadwal-mengajar/{$jadwalId}")->assertOk();
    }

    public function test_admin_melihat_rekap_filter_dan_export(): void
    {
        $admin = $this->createUserWithRole('admin', 'admin@example.com');
        $kelasA = $this->createKelas('11 IPA 1');
        $kelasB = $this->createKelas('12 IPS 2', '12', 'IPS');
        $siswaA = $this->createSiswa($kelasA, 'siswa-a@example.com');
        $siswaB = $this->createSiswa($kelasB, 'siswa-b@example.com');

        $this->createDispensasi($siswaA, $kelasA, ['tanggal' => '2026-05-20']);
        $this->createDispensasi($siswaB, $kelasB, ['tanggal' => '2026-05-22']);

        Sanctum::actingAs($admin);

        $this->getJson('/api/dispensasi')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $filtered = (new DispensasiExport([
            'status' => 'all',
            'kelas_id' => $kelasA->id,
            'tanggal_mulai' => '2026-05-20',
            'tanggal_selesai' => '2026-05-20',
        ]))->collection();

        $this->assertCount(1, $filtered);
        $this->assertSame($kelasA->id, $filtered->first()->kelas_id);

        $this->getJson('/api/export/excel?kelas_id=' . $kelasA->id)
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->getJson('/api/export/csv?kelas_id=' . $kelasA->id)
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function createUserWithRole(string $role, string $email): User
    {
        $roleModel = match ($role) {
            'admin' => $this->role('admin', 'Administrator'),
            'kesiswaan' => $this->role('kesiswaan', 'Kesiswaan'),
            'guru' => $this->role('guru_mapel', 'Guru Mata Pelajaran'),
        };

        $user = User::factory()->create([
            'name' => ucfirst($role) . ' Test',
            'email' => $email,
            'role' => $role,
            'nip' => $role === 'siswa' ? null : fake()->unique()->numerify('##################'),
        ]);

        if ($role !== 'siswa') {
            $user->roles()->attach($roleModel);
        }

        return $user;
    }

    private function role(string $name, string $displayName): Role
    {
        return Role::firstOrCreate(
            ['name' => $name],
            ['display_name' => $displayName, 'description' => $displayName]
        );
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

    private function createSiswa(Kelas $kelas, string $email): User
    {
        return User::factory()->create([
            'email' => $email,
            'role' => 'siswa',
            'nisn' => fake()->unique()->numerify('##########'),
            'kelas_id' => $kelas->id,
        ]);
    }

    private function createDispensasi(User $siswa, Kelas $kelas, array $overrides = []): Dispensasi
    {
        return Dispensasi::create(array_merge([
            'user_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-05-20',
            'jam_pelajaran_mulai' => 1,
            'jam_pelajaran_selesai' => 2,
            'mata_pelajaran' => 'Matematika',
            'keperluan' => 'Kegiatan sekolah',
            'status' => 'pending',
        ], $overrides));
    }
}
