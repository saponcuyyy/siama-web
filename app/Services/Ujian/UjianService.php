<?php

namespace App\Services\Ujian;

use App\Enums\PesertaStatus;
use App\Enums\SoalType;
use App\Models\JawabanSiswa;
use App\Models\LogUjian;
use App\Models\PesertaUjian;
use App\Models\Soal;
use Illuminate\Support\Facades\DB;

class UjianService
{
    /**
     * Memulai ujian untuk siswa.
     */
    public function mulaiUjian(PesertaUjian $peserta, string $deviceToken, string $ip, ?string $userAgent): void
    {
        $peserta->loadMissing('sesiUjian.paketUjian');

        DB::transaction(function () use ($peserta, $deviceToken, $ip, $userAgent) {
            $paket = $peserta->sesiUjian->paketUjian;

            // Siapkan urutan soal
            $urutanSoal = $peserta->urutan_soal;
            $urutanJawaban = $peserta->urutan_jawaban;

            if (empty($urutanSoal)) {
                $soalQuery = $paket->soal();
                if ($paket->acak_soal) {
                    $soalQuery->inRandomOrder();
                } else {
                    $soalQuery->orderBy('paket_soal.urutan');
                }
                $urutanSoal = $soalQuery->pluck('soal.id')->toArray();

                // Siapkan acakan pilihan jawaban jika diaktifkan
                $urutanJawaban = [];
                if ($paket->acak_jawaban) {
                    $semuaSoal = Soal::with('pilihanJawaban')->whereIn('id', $urutanSoal)->get();
                    foreach ($semuaSoal as $s) {
                        if ($s->tipe === SoalType::PG->value) {
                            $urutanJawaban[$s->id] = $s->pilihanJawaban->shuffle()->pluck('kode')->toArray();
                        }
                    }
                }
            }

            // Update status peserta
            $peserta->update([
                'status' => PesertaStatus::MENGERJAKAN->value,
                'mulai_at' => $peserta->mulai_at ?? now(),
                'device_token' => $deviceToken,
                'ip_address' => $ip,
                'browser' => $userAgent,
                'urutan_soal' => $urutanSoal,
                'urutan_jawaban' => $urutanJawaban,
            ]);

            // Catat log
            LogUjian::create([
                'peserta_ujian_id' => $peserta->id,
                'tipe_event' => 'mulai_ujian',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'terjadi_at' => now(),
            ]);
        });
    }

    /**
     * Catat pelanggaran siswa (seperti pindah tab).
     */
    public function catatPelanggaran(PesertaUjian $peserta, string $tipe, string $ip, ?string $userAgent): void
    {
        $peserta->loadMissing('sesiUjian');

        DB::transaction(function () use ($peserta, $tipe, $ip, $userAgent) {
            $fresh = PesertaUjian::lockForUpdate()->find($peserta->id);

            if (! $fresh || in_array($fresh->status, [PesertaStatus::SELESAI->value, PesertaStatus::DIDISKUALIFIKASI->value])) {
                return;
            }

            $fresh->increment('jumlah_pelanggaran');
            $fresh->refresh();

            LogUjian::create([
                'peserta_ujian_id' => $fresh->id,
                'tipe_event' => $tipe,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'terjadi_at' => now(),
            ]);

            if ($fresh->jumlah_pelanggaran >= ($fresh->sesiUjian?->max_pelanggaran ?? PHP_INT_MAX)) {
                $fresh->update(['status' => PesertaStatus::DIDISKUALIFIKASI->value, 'selesai_at' => now()]);

                LogUjian::create([
                    'peserta_ujian_id' => $fresh->id,
                    'tipe_event' => 'diskualifikasi',
                    'keterangan' => 'Mencapai batas maksimal pelanggaran',
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'terjadi_at' => now(),
                ]);
            }
        });
    }

    /**
     * Dapatkan daftar soal beserta opsi (yang sudah diacak jika diaktifkan).
     */
    public function getSoalUntukSiswa(PesertaUjian $peserta): array
    {
        $urutanSoal = $peserta->urutan_soal ?? [];
        $urutanJaw = $peserta->urutan_jawaban ?? [];
        $jawabanTersimpan = $peserta->jawabanSiswa->keyBy('soal_id');

        if (empty($urutanSoal)) {
            return [];
        }

        $soalList = Soal::with(['pilihanJawaban', 'pasanganMenjodohkan'])
            ->whereIn('id', $urutanSoal)
            ->get()
            ->keyBy('id');

        $result = [];
        $nomor = 1;

        foreach ($urutanSoal as $soalId) {
            $soal = $soalList[$soalId] ?? null;
            if (! $soal) {
                continue;
            }

            // Jawaban yang sudah disimpan sebelumnya
            $jawabanLog = $jawabanTersimpan[$soalId] ?? null;

            $soalData = [
                'id' => $soal->id,
                'nomor' => $nomor++,
                'tipe' => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'gambar_url' => $soal->gambar_url,
                'ragu' => $jawabanLog->is_ragu ?? false,
            ];

            // Acak urutan pilihan jawaban (PG)
            if ($soal->tipe === SoalType::PG->value) {
                $urutanOpsi = $urutanJaw[$soalId] ?? null;
                $opsi = $soal->pilihanJawaban->map(fn ($p) => [
                    'kode' => $p->kode,
                    'teks' => $p->teks,
                    'gambar_url' => $p->gambar_url,
                ]);

                if ($urutanOpsi) {
                    $opsi = collect($urutanOpsi)->map(
                        fn ($kode) => $opsi->firstWhere('kode', $kode)
                    )->filter(); // filter to avoid nulls if any options were deleted or mismatched
                }

                $soalData['pilihan'] = $opsi->values();
                $soalData['jawaban_siswa'] = $jawabanLog->jawaban ?? null;
            } elseif ($soal->tipe === SoalType::BENAR_SALAH->value) {
                $soalData['jawaban_siswa'] = $jawabanLog->jawaban ?? null;
            } elseif ($soal->tipe === SoalType::ESSAY->value) {
                $soalData['jawaban_siswa'] = $jawabanLog->jawaban ?? '';
            } elseif ($soal->tipe === SoalType::MENJODOHKAN->value) {
                $kiri = $soal->pasanganMenjodohkan->pluck('kiri')->shuffle();
                $kanan = $soal->pasanganMenjodohkan->pluck('kanan')->shuffle();

                $soalData['pilihan_kiri'] = $kiri;
                $soalData['pilihan_kanan'] = $kanan;
                $soalData['jawaban_siswa'] = $jawabanLog->jawaban_menjodohkan ?? [];
            }

            $result[] = $soalData;
        }

        return $result;
    }

    /**
     * Selesaikan ujian dan hitung nilai otomatis.
     */
    public function akhiriUjian(PesertaUjian $peserta, string $ip, ?string $userAgent): void
    {
        DB::transaction(function () use ($peserta, $ip, $userAgent) {
            $peserta->update([
                'status' => PesertaStatus::SELESAI->value,
                'selesai_at' => now(),
            ]);

            LogUjian::create([
                'peserta_ujian_id' => $peserta->id,
                'tipe_event' => 'submit_ujian',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'terjadi_at' => now(),
            ]);

            $this->koreksiOtomatis($peserta);
        });
    }

    /**
     * Koreksi otomatis untuk pilihan ganda, benar salah, dan menjodohkan.
     */
    public function koreksiOtomatis(PesertaUjian $peserta): void
    {
        $jawabanSiswa = $peserta->jawabanSiswa()->with('soal.pasanganMenjodohkan')->get();

        $nilaiPg = 0;
        $nilaiBs = 0;
        $nilaiMenjodohkan = 0;
        $adaEssay = false;

        $batchUpdates = [];

        foreach ($jawabanSiswa as $j) {
            $soal = $j->soal;
            if (! $soal) {
                continue;
            }

            switch ($soal->tipe) {
                case SoalType::PG->value:
                case SoalType::BENAR_SALAH->value:
                    $jawaban = is_string($j->jawaban) ? trim($j->jawaban) : '';
                    $isBenar = (strtolower($jawaban) === strtolower(trim($soal->kunci_jawaban)));
                    $nilai = $isBenar ? $soal->bobot : 0;
                    $batchUpdates[] = [
                        'id' => $j->id,
                        'peserta_ujian_id' => $j->peserta_ujian_id,
                        'soal_id' => $j->soal_id,
                        'is_benar' => $isBenar,
                        'nilai' => $nilai,
                    ];

                    if ($soal->tipe === SoalType::PG->value) {
                        $nilaiPg += $nilai;
                    } else {
                        $nilaiBs += $nilai;
                    }
                    break;

                case SoalType::MENJODOHKAN->value:
                    $pasangan = $soal->pasanganMenjodohkan->pluck('kanan', 'kiri');
                    $jawabanSiswaData = $j->jawaban_menjodohkan ?? [];
                    $benarCount = 0;
                    foreach ($jawabanSiswaData as $kiri => $kanan) {
                        if (isset($pasangan[$kiri]) && $pasangan[$kiri] === $kanan) {
                            $benarCount++;
                        }
                    }
                    $totalPasangan = $pasangan->count();
                    $nilai = $totalPasangan > 0
                        ? round(($benarCount / $totalPasangan) * $soal->bobot, 2)
                        : 0;

                    $batchUpdates[] = [
                        'id' => $j->id,
                        'peserta_ujian_id' => $j->peserta_ujian_id,
                        'soal_id' => $j->soal_id,
                        'is_benar' => $benarCount === $totalPasangan,
                        'nilai' => $nilai,
                    ];
                    $nilaiMenjodohkan += $nilai;
                    break;

                case SoalType::ESSAY->value:
                    $adaEssay = true;
                    // Nilai essay akan diisi manual oleh guru
                    break;
            }
        }

        if (! empty($batchUpdates)) {
            JawabanSiswa::upsert($batchUpdates, ['id'], ['is_benar', 'nilai']);
        }

        $nilaiAkhir = $nilaiPg + $nilaiBs + $nilaiMenjodohkan;

        if ($adaEssay) {
            $jumlahNilaiEssay = $jawabanSiswa
                ->filter(fn ($j) => $j->soal?->tipe === SoalType::ESSAY->value)
                ->sum('skor');

            if ($jumlahNilaiEssay > 0) {
                $nilaiAkhir += $jumlahNilaiEssay;
            }
        }

        $peserta->update([
            'nilai_pg' => $nilaiPg,
            'nilai_bs' => $nilaiBs,
            'nilai_menjodohkan' => $nilaiMenjodohkan,
            'nilai_essay' => $adaEssay ? ($jumlahNilaiEssay ?? null) : null,
            'nilai_akhir' => $nilaiAkhir,
            'sudah_dikoreksi' => true,
            'essay_sudah_dinilai' => ! $adaEssay || ($jumlahNilaiEssay ?? 0) === ($peserta->nilai_essay ?? 0),
        ]);
    }
}
