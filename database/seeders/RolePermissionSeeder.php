<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
            'settings.view', 'settings.manage',
            'audit.view'
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Create Roles and Assign Permissions
        $roles = [
            'super_admin' => $permissions,
            'kepala_sekolah' => ['dashboard.view', 'nilai.view', 'siswa.view', 'guru.view', 'audit.view'],
            'wakil_kepala' => ['dashboard.view', 'jadwal.manage', 'nilai.view', 'siswa.view', 'guru.view'],
            'tata_usaha' => ['dashboard.view', 'siswa.manage', 'guru.manage', 'settings.view'],
            'wali_kelas' => ['dashboard.view', 'nilai.create', 'nilai.edit', 'siswa.view'],
            'guru' => ['dashboard.view', 'nilai.create', 'nilai.edit', 'jadwal.view'],
            'siswa' => ['dashboard.view', 'nilai.view', 'jadwal.view', 'ujian.participate', 'perpustakaan.view'],
            'pustakawan' => ['dashboard.view', 'perpustakaan.manage']
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}
