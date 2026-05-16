<?php

namespace Tests\Feature\NonFungsional;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UjiNonFungsionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_baru_hanya_dibuat_admin(): void
    {
        $this->createRoles();
        $admin = $this->createAdmin();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Guru Baru',
            'email' => 'guru-baru@example.com',
            'password' => 'password',
            'role' => 'guru',
            'roles' => ['guru_mapel'],
            'nip' => '198501012010011002',
            'mata_pelajaran' => 'Matematika',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'guru-baru@example.com')
            ->assertJsonPath('data.roles.0.name', 'guru_mapel');

        $user = User::with('roles')->find($response->json('data.id'));

        $this->assertEquals(['guru_mapel'], $user->roles->pluck('name')->all());
    }

    public function test_guru_tidak_bisa_akses_endpoint_privileged(): void
    {
        $guru = $this->createGuru();

        Sanctum::actingAs($guru);

        $this->getJson('/api/users')->assertForbidden();
        $this->getJson('/api/users/statistics')->assertForbidden();
        $this->getJson('/api/analytics/overview')->assertForbidden();
        $this->getJson('/api/export/csv')->assertForbidden();
        $this->getJson('/api/audit-logs')->assertForbidden();
        $this->getJson('/api/backups')->assertForbidden();
    }

    private function createGuru(): User
    {
        $guruRole = Role::firstOrCreate(
            ['name' => 'guru_mapel'],
            ['display_name' => 'Guru Mata Pelajaran', 'description' => 'Guru mata pelajaran']
        );

        $guru = User::factory()->create([
            'name' => 'Guru Mapel',
            'email' => 'guru@example.com',
            'role' => 'guru',
            'nip' => '198501012010011001',
        ]);

        $guru->roles()->attach($guruRole);
        $guru->load('roles');

        return $guru;
    }

    private function createAdmin(): User
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'Memiliki akses penuh']
        );

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'nip' => '198501012010011099',
        ]);

        $admin->roles()->attach($adminRole);
        $admin->load('roles');

        return $admin;
    }

    private function createRoles(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'Memiliki akses penuh']
        );
        Role::firstOrCreate(
            ['name' => 'kesiswaan'],
            ['display_name' => 'Kesiswaan', 'description' => 'Mengelola dispensasi']
        );
        Role::firstOrCreate(
            ['name' => 'guru_mapel'],
            ['display_name' => 'Guru Mata Pelajaran', 'description' => 'Guru mata pelajaran']
        );
    }
}
