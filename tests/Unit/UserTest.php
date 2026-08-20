<?php

namespace Tests\Unit;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_siswa_relationship()
    {
        $user = User::factory()->create();
        $siswa = Siswa::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->siswa->is($siswa));
    }

    #[Test]
    public function it_has_guru_relationship()
    {
        $user = User::factory()->create();
        $guru = Guru::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->guru->is($guru));
    }
}
