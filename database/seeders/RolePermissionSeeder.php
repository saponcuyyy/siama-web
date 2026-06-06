<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'dashboard.view',
            'nilai.view', 'nilai.create', 'nilai.edit', 'nilai.delete',
            'jadwal.view', 'jadwal.manage',
            'siswa.view', 'siswa.manage',
            'guru.view', 'guru.manage',
            'perpustakaan.view', 'perpustakaan.manage',
            'ujian.view', 'ujian.manage', 'ujian.participate',
            // CBT Permissions
            'ujian.bank-soal.manage', 'ujian.soal.manage', 'ujian.paket.manage',
            'ujian.sesi.manage', 'ujian.sesi.monitor', 'ujian.penilaian.essay',
            'ujian.laporan.view', 'ujian.laporan.export',
            'settings.view', 'settings.manage',
            'audit.view',
            // Web Management (CMS)
            'web.view', 'web.dashboard.view',
            'web.menu.manage', 'web.slider.manage', 'web.halaman.manage',
            'web.fasilitas.manage', 'web.kategori-berita.manage', 'web.berita.manage',
            'web.pengumuman.manage', 'web.album.manage', 'web.pesan.view',
            'web.kelulusan.manage', 'web.setting.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles and Assign Permissions
        $roles = [
            'super_admin' => $permissions,
            'kepala_sekolah' => ['dashboard.view', 'nilai.view', 'siswa.view', 'guru.view', 'audit.view', 'ujian.laporan.view', 'ujian.laporan.export'],
            'wakil_kepala' => ['dashboard.view', 'jadwal.manage', 'nilai.view', 'siswa.view', 'guru.view', 'ujian.bank-soal.manage', 'ujian.soal.manage', 'ujian.paket.manage', 'ujian.sesi.manage', 'ujian.sesi.monitor', 'ujian.penilaian.essay', 'ujian.laporan.view', 'ujian.laporan.export', 'ujian.view'],
            'tata_usaha' => ['dashboard.view', 'users.view', 'users.create', 'users.edit', 'users.delete', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'siswa.manage', 'guru.manage', 'settings.view'],
            'wali_kelas' => ['dashboard.view', 'nilai.create', 'nilai.edit', 'siswa.view'],
            'guru' => ['dashboard.view', 'nilai.create', 'nilai.edit', 'jadwal.view', 'ujian.bank-soal.manage', 'ujian.soal.manage', 'ujian.paket.manage', 'ujian.sesi.manage', 'ujian.penilaian.essay', 'ujian.laporan.view', 'ujian.laporan.export', 'ujian.view'],
            'siswa' => ['ujian.view', 'ujian.participate'],
            'pustakawan' => ['dashboard.view', 'perpustakaan.manage'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(Permission::whereIn('name', $rolePermissions)->where('guard_name', 'web')->get());
        }
    }
}
