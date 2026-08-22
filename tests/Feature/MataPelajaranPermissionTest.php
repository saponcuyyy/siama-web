<?php

namespace Tests\Feature;

use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MataPelajaranPermissionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guru_can_view_mata_pelajaran(): void
    {
        $guru = $this->userWithRole('guru');

        $this->actingAs($guru)
            ->get(route('admin.web.mata-pelajaran.index'))
            ->assertOk();
    }

    public function test_guru_cannot_create_mata_pelajaran(): void
    {
        $guru = $this->userWithRole('guru');

        $response = $this->actingAs($guru)->post(route('admin.web.mata-pelajaran.store'), [
            'nama' => 'Fisika Lanjut',
            'kode' => 'FIS999',
            'tingkat' => 'X',
            'jam_per_minggu' => 4,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('mata_pelajaran', ['kode' => 'FIS999']);
    }

    public function test_guru_cannot_update_mata_pelajaran(): void
    {
        $guru = $this->userWithRole('guru');
        $mapel = MataPelajaran::factory()->create();

        $response = $this->actingAs($guru)->put(route('admin.web.mata-pelajaran.update', $mapel->hashid), [
            'nama' => 'Nama Diubah',
            'kode' => $mapel->kode,
            'tingkat' => 'X',
            'jam_per_minggu' => 4,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('mata_pelajaran', ['nama' => 'Nama Diubah']);
    }

    public function test_guru_cannot_delete_mata_pelajaran(): void
    {
        $guru = $this->userWithRole('guru');
        $mapel = MataPelajaran::factory()->create();

        $response = $this->actingAs($guru)->delete(route('admin.web.mata-pelajaran.destroy', $mapel->hashid));

        $response->assertForbidden();
        $this->assertDatabaseHas('mata_pelajaran', ['id' => $mapel->id, 'deleted_at' => null]);
    }

    public function test_tata_usaha_can_manage_mata_pelajaran(): void
    {
        $tu = $this->userWithRole('tata_usaha');

        $this->actingAs($tu)
            ->get(route('admin.web.mata-pelajaran.index'))
            ->assertOk();

        $this->actingAs($tu)->post(route('admin.web.mata-pelajaran.store'), [
            'nama' => 'Matematika Lanjut',
            'kode' => 'MTK999',
            'tingkat' => 'XI',
            'jurusan' => 'IPA',
            'jam_per_minggu' => 5,
        ])->assertRedirect();

        $this->assertDatabaseHas('mata_pelajaran', ['kode' => 'MTK999']);
    }

    public function test_guru_permissions_matrix(): void
    {
        $guru = $this->userWithRole('guru');
        $perms = $guru->getAllPermissions()->pluck('name')->toArray();

        $this->assertContains('mapel.view', $perms);
        $this->assertNotContains('mapel.manage', $perms);
    }
}
