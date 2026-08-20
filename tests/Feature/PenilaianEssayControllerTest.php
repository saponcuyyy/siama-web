<?php

namespace Tests\Feature;

use App\Models\JawabanSiswa;
use App\Models\PesertaUjian;
use App\Models\Soal;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PenilaianEssayControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    #[Test]
    public function guests_cannot_access_the_grading_index()
    {
        $response = $this->get(route('admin.ujian.penilaian.index'));

        // Fortify redirect ke halaman login
        $response->assertRedirect();
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    #[Test]
    public function users_without_correct_permission_cannot_access_the_grading_index()
    {
        $user = User::factory()->create();
        // Tidak diberikan role apapun

        $response = $this->actingAs($user)->get(route('admin.ujian.penilaian.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function authorized_user_can_access_the_grading_index()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        $response = $this->actingAs($user)->get(route('admin.ujian.penilaian.index'));

        $response->assertStatus(200);
        // Cek bahwa response adalah Inertia — ada data JSON embedded
        $response->assertSee('jawabanList', false);
    }

    #[Test]
    public function grading_essay_with_valid_score_updates_database_successfully()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        $peserta = PesertaUjian::factory()->create([
            'nilai_pg' => 40,
            'nilai_bs' => 10,
            'nilai_menjodohkan' => 10,
            'nilai_essay' => 0,
            'nilai_akhir' => 60,
        ]);

        $soal = Soal::factory()->create([
            'tipe' => 'essay',
            'bobot' => 20,
        ]);

        $jawaban = JawabanSiswa::factory()->create([
            'peserta_ujian_id' => $peserta->id,
            'soal_id' => $soal->id,
            'jawaban' => 'This is student answer text.',
            'skor' => null,
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.ujian.penilaian.nilai', $jawaban->hashid),
            ['skor' => 15]
        );

        $response->assertRedirect();
        $this->assertEquals(15, $jawaban->fresh()->skor);

        $peserta->refresh();
        $this->assertEquals(15, $peserta->nilai_essay);
        $this->assertEquals(75, $peserta->nilai_akhir); // 40 + 10 + 10 + 15
        $this->assertTrue($peserta->essay_sudah_dinilai);
    }

    #[Test]
    public function grading_essay_with_invalid_score_fails_validation()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        $soal = Soal::factory()->create([
            'tipe' => 'essay',
            'bobot' => 10,
        ]);

        $jawaban = JawabanSiswa::factory()->create([
            'soal_id' => $soal->id,
            'skor' => null,
        ]);

        // Skor 12 melebihi bobot 10 → harus gagal validasi
        $response = $this->actingAs($user)->post(
            route('admin.ujian.penilaian.nilai', $jawaban->hashid),
            ['skor' => 12]
        );

        $response->assertSessionHasErrors(['skor']);
        $this->assertNull($jawaban->fresh()->skor);
    }
}
