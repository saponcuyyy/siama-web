<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAkademikPermissionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guru_cannot_access_master_akademik_menus(): void
    {
        $guru = $this->userWithRole('guru');

        $routes = [
            'admin.web.rombel.index',
            'admin.web.kartu-ujian.index',
            'admin.web.tahun-ajaran.index',
            'admin.web.semester.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($guru)->get(route($route));
            $this->assertSame(
                403,
                $response->status(),
                "Guru seharusnya TIDAK bisa akses {$route}, dapat HTTP ".$response->status()
            );
        }
    }

    public function test_guru_can_still_view_siswa(): void
    {
        $guru = $this->userWithRole('guru');

        $this->actingAs($guru)->get(route('admin.web.siswa.index'))->assertOk();
    }

    public function test_tata_usaha_can_access_master_akademik_menus(): void
    {
        $tu = $this->userWithRole('tata_usaha');

        $routes = [
            'admin.web.rombel.index',
            'admin.web.kartu-ujian.index',
            'admin.web.tahun-ajaran.index',
            'admin.web.semester.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($tu)->get(route($route));
            $this->assertSame(200, $response->status(), "TU seharusnya bisa akses {$route}");
        }
    }

    public function test_wali_kelas_read_only_access(): void
    {
        $wk = $this->userWithRole('wali_kelas');

        $this->actingAs($wk)->get(route('admin.web.rombel.index'))->assertOk();
        $this->actingAs($wk)->get(route('admin.web.kartu-ujian.index'))->assertOk();

        // CUD harus ditolak (hanya punya rombel.view)
        $this->actingAs($wk)->post(route('admin.web.rombel.store'), [
            'nama' => 'Rombel Baru',
            'tingkat' => 'X',
            'tahun_ajaran_id' => 1,
        ])->assertForbidden();
    }

    public function test_guru_has_no_master_akademik_permissions_in_db(): void
    {
        $guru = $this->userWithRole('guru');

        $perms = $guru->getAllPermissions()->pluck('name')->toArray();

        foreach (['rombel.view', 'rombel.manage', 'kartu-ujian.view', 'tahun-ajaran.view', 'semester.view'] as $p) {
            $this->assertNotContains($p, $perms, "Guru tidak boleh punya permission {$p}");
        }

        $this->assertContains('siswa.view', $perms);
    }
}
