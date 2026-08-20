<?php

namespace Tests\Unit;

use App\Enums\PesertaStatus;
use App\Models\BankSoal;
use App\Models\JawabanSiswa;
use App\Models\PaketUjian;
use App\Models\PesertaUjian;
use App\Models\SesiUjian;
use App\Models\Soal;
use App\Services\Ujian\UjianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UjianServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UjianService $ujianService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ujianService = new UjianService();
    }

    #[Test]
    public function it_can_start_an_exam_for_student()
    {
        $paket = PaketUjian::factory()->create();
        $bankSoal = BankSoal::factory()->create([
            'mata_pelajaran_id' => $paket->mata_pelajaran_id,
            'guru_id' => $paket->guru_id,
            'tahun_ajaran_id' => $paket->tahun_ajaran_id,
        ]);
        $sesi = SesiUjian::factory()->create(['paket_ujian_id' => $paket->id]);
        $peserta = PesertaUjian::factory()->create([
            'sesi_ujian_id' => $sesi->id,
            'status' => PesertaStatus::BELUM_MULAI->value,
        ]);

        $soal = Soal::factory()->count(3)->create(['bank_soal_id' => $bankSoal->id]);
        $paket->soal()->attach($soal->pluck('id'));

        $this->ujianService->mulaiUjian($peserta, 'device-token-123', '127.0.0.1', 'Mozilla/5.0');

        $peserta->refresh();

        $this->assertEquals(PesertaStatus::MENGERJAKAN->value, $peserta->status);
        $this->assertEquals('device-token-123', $peserta->device_token);
        $this->assertCount(3, $peserta->urutan_soal);
        $this->assertDatabaseHas('log_ujian', [
            'peserta_ujian_id' => $peserta->id,
            'tipe_event' => 'mulai_ujian',
        ]);
    }

    #[Test]
    public function it_can_log_infractions_and_disqualify_student_if_max_reached()
    {
        $sesi = SesiUjian::factory()->create(['max_pelanggaran' => 2]);
        $peserta = PesertaUjian::factory()->create([
            'sesi_ujian_id' => $sesi->id,
            'status' => PesertaStatus::MENGERJAKAN->value,
            'jumlah_pelanggaran' => 0,
        ]);

        // First infraction — masih aktif
        $this->ujianService->catatPelanggaran($peserta, 'pindah_tab', '127.0.0.1', 'Mozilla');
        $peserta->refresh();
        $this->assertEquals(1, $peserta->jumlah_pelanggaran);
        $this->assertEquals(PesertaStatus::MENGERJAKAN->value, $peserta->status);

        // Second infraction — harus diskualifikasi
        $this->ujianService->catatPelanggaran($peserta, 'pindah_tab', '127.0.0.1', 'Mozilla');
        $peserta->refresh();
        $this->assertEquals(2, $peserta->jumlah_pelanggaran);
        $this->assertEquals(PesertaStatus::DIDISKUALIFIKASI->value, $peserta->status);
        $this->assertDatabaseHas('log_ujian', [
            'peserta_ujian_id' => $peserta->id,
            'tipe_event' => 'diskualifikasi',
        ]);
    }

    #[Test]
    public function it_can_auto_grade_multiple_choice_and_true_false_questions()
    {
        $peserta = PesertaUjian::factory()->create([
            'status' => PesertaStatus::MENGERJAKAN->value,
        ]);

        $soalPg = Soal::factory()->create(['tipe' => 'pg', 'kunci_jawaban' => 'A', 'bobot' => 10]);
        $soalBs = Soal::factory()->create(['tipe' => 'benar_salah', 'kunci_jawaban' => 'Benar', 'bobot' => 5]);

        JawabanSiswa::factory()->create([
            'peserta_ujian_id' => $peserta->id,
            'soal_id' => $soalPg->id,
            'jawaban' => 'A', // benar
        ]);

        JawabanSiswa::factory()->create([
            'peserta_ujian_id' => $peserta->id,
            'soal_id' => $soalBs->id,
            'jawaban' => 'Salah', // salah
        ]);

        $this->ujianService->koreksiOtomatis($peserta);
        $peserta->refresh();

        $this->assertEquals(10, $peserta->nilai_pg);
        $this->assertEquals(0, $peserta->nilai_bs);
        $this->assertEquals(10, $peserta->nilai_akhir);
        $this->assertTrue($peserta->sudah_dikoreksi);
    }
}
