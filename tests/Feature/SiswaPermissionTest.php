<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiswaPermissionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guru_can_view_siswa_index(): void
    {
        $guru = $this->userWithRole('guru');

        $response = $this->actingAs($guru)->get(route('admin.web.siswa.index'));

        $response->assertOk();
    }

    public function test_guru_cannot_create_siswa(): void
    {
        $guru = $this->userWithRole('guru');

        $response = $this->actingAs($guru)->post(route('admin.web.siswa.store'), [
            'nama' => 'Siswa Baru',
            'nisn' => '0099990001',
            'tanggal_lahir' => '2010-01-01',
            'rombel_id' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('siswa', ['nisn' => '0099990001']);
    }

    public function test_guru_cannot_update_siswa(): void
    {
        $guru = $this->userWithRole('guru');
        $siswa = Siswa::factory()->create();

        $response = $this->actingAs($guru)->put(route('admin.web.siswa.update', $siswa->hashid), [
            'nama' => 'Nama Diubah',
            'nisn' => $siswa->nisn,
            'tanggal_lahir' => '2010-01-01',
            'rombel_id' => $siswa->rombel_id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('siswa', ['nama' => 'Nama Diubah']);
    }

    public function test_guru_cannot_delete_siswa(): void
    {
        $guru = $this->userWithRole('guru');
        $siswa = Siswa::factory()->create();

        $response = $this->actingAs($guru)->delete(route('admin.web.siswa.destroy', $siswa->hashid));

        $response->assertForbidden();
        $this->assertDatabaseHas('siswa', ['id' => $siswa->id, 'deleted_at' => null]);
    }

    public function test_super_admin_still_can_manage_siswa(): void
    {
        $admin = $this->userWithRole('super_admin');

        $response = $this->actingAs($admin)->get(route('admin.web.siswa.index'));
        $response->assertOk();
    }
}
