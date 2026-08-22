<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_password_via_edit_user(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $target = User::factory()->create([
            'password' => Hash::make('password-lama'),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'rahasia-baru',
            'role' => 'super_admin',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $target->refresh();
        $this->assertTrue(Hash::check('rahasia-baru', $target->password), 'Password baru TIDAK tersimpan di DB');
    }

    public function test_update_password_by_tata_usaha(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $tu = User::factory()->create();
        $tu->assignRole('tata_usaha');

        $target = User::factory()->create([
            'password' => Hash::make('password-lama'),
        ]);

        $response = $this->actingAs($tu)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'password-tu',
            'role' => 'guru',
        ]);

        $response->assertStatus(302);
        $target->refresh();
        $this->assertTrue(Hash::check('password-tu', $target->password));
    }

    public function test_update_without_password_keeps_old_password(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $target = User::factory()->create([
            'password' => Hash::make('password-lama'),
        ]);
        $oldHash = $target->password;

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Nama Baru',
            'email' => $target->email,
            'password' => '',
            'role' => 'guru',
        ]);

        $response->assertStatus(302);
        $target->refresh();
        $this->assertSame($oldHash, $target->password);
        $this->assertTrue(Hash::check('password-lama', $target->password));
    }
}
