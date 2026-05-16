<?php

namespace Tests\Feature\Autentikasi;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UjiAutentikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_bisa_login_dengan_data_valid(): void
    {
        $kelas = Kelas::create([
            'nama_kelas' => '11 IPA 1',
            'tingkat' => '11',
            'jurusan' => 'IPA',
        ]);

        $siswa = User::factory()->create([
            'name' => 'Siswa Test',
            'email' => 'siswa@example.com',
            'role' => 'siswa',
            'kelas_id' => $kelas->id,
        ]);

        $this->postJson('/api/login', [
            'email' => $siswa->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', $siswa->email)
            ->assertJsonPath('user.kelas.nama_kelas', $kelas->nama_kelas)
            ->assertJsonStructure(['token']);
    }

    public function test_user_tidak_bisa_login_dengan_password_salah(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'siswa',
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ])->assertUnprocessable();
    }

    public function test_user_bisa_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil');
    }

    public function test_endpoint_terlindungi_wajib_login(): void
    {
        $this->getJson('/api/dispensasi')->assertUnauthorized();
        $this->getJson('/api/jadwal-mengajar')->assertUnauthorized();
        $this->putJson('/api/dispensasi/1/status', [
            'status' => 'approved',
        ])->assertUnauthorized();
    }

    public function test_register_publik_tidak_tersedia(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->postJson('/api/register', [
            'name' => 'Guru Baru',
            'email' => 'guru-baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'guru',
            'roles' => ['guru_mapel'],
            'nip' => '198501012010011001',
            'mata_pelajaran' => 'Matematika',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', [
            'email' => 'guru-baru@example.com',
        ]);
    }
}
