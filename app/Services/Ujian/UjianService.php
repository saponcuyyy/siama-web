<?php
namespace App\Services\Ujian;

use App\Models\{
    PesertaUjian, SesiUjian, Soal, JawabanSiswa, LogUjian
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UjianService
{
    /**
     * Memulai ujian untuk siswa.
     */
    public function mulaiUjian(PesertaUjian $peserta, string $deviceToken, string $ip, ?string $userAgent): void
    {
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
                        if ($s->tipe === 'pg') {
                            $urutanJawaban[$s->id] = $s->pilihanJawaban->shuffle()->pluck('kode')->toArray();
                        }
                    }
                }
            }

            // Update status peserta
            $peserta->update([
                'status'         => 'mengerjakan',
                'mulai_at'       => $peserta->mulai_at ?? now(),
                'device_token'   => $deviceToken,
                'ip_address'     => $ip,
                'browser'        => $userAgent,
                'urutan_soal'    => $urutanSoal,
                'urutan_jawaban' => $urutanJawaban,
            ]);

            // Catat log
            LogUjian::create([
                'peserta_ujian_id' => $peserta->id,
                'tipe_event'       => 'mulai_ujian',
                'ip_address'       => $ip,
                'user_agent'       => $userAgent,
                'terjadi_at'       => now(),
            ]);
        });
    }

    /**
     * Catat pelanggaran siswa (seperti pindah tab).
     */
    public function catatPelanggaran(PesertaUjian $peserta, string $tipe, string $ip, ?string $userAgent): void
    {
        DB::transaction(function () use ($peserta, $tipe, $ip, $userAgent) {
            $peserta->increment('jumlah_pelanggaran');

            LogUjian::create([
                'peserta_ujian_id' => $peserta->id,
                'tipe_event'       => $tipe, // e.g., 'pindah_tab'
                'ip_address'       => $ip,
                'user_agent'       => $userAgent,
                'terjadi_at'       => now(),
            ]);

            // Diskualifikasi otomatis jika melebihi max_pelanggaran
            if ($peserta->jumlah_pelanggaran >= $peserta->sesiUjian->max_pelanggaran) {
                $peserta->update(['status' => 'didiskualifikasi', 'selesai_at' => now()]);
                
                LogUjian::create([
                    'peserta_ujian_id' => $peserta->id,
                    'tipe_event'       => 'diskualifikasi',
                    'keterangan'       => 'Mencapai batas maksimal pelanggaran',
                    'ip_address'       => $ip,
                    'user_agent'       => $userAgent,
                    'terjadi_at'       => now(),
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

        if (empty($urutanSoal)) return [];

        $soalList = Soal::with(['pilihanJawaban', 'pasanganMenjodohkan'])
                        ->whereIn('id', $urutanSoal)
                        ->get()
                        ->keyBy('id');

        $result = [];
        $nomor = 1;

        foreach ($urutanSoal as $soalId) {
            $soal = $soalList[$soalId] ?? null;
            if (!$soal) continue;

            $soalData = [
                'id'         => $soal->id,
                'nomor'      => $nomor++,
                'tipe'       => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'gambar_url' => $soal->gambar_url,
                'ragu'       => false, // State lokal untuk fitur "Ragu-ragu"
            ];

            // Jawaban yang sudah disimpan sebelumnya
            $jawabanLog = $jawabanTersimpan[$soalId] ?? null;

            // Acak urutan pilihan jawaban (PG)
            if ($soal->tipe === 'pg') {
                $urutanOpsi = $urutanJaw[$soalId] ?? null;
                $opsi = $soal->pilihanJawaban->map(fn($p) => [
                    'kode' => $p->kode,
                    'teks' => $p->teks,
                    'gambar_url' => $p->gambar_url,
                ]);

                if ($urutanOpsi) {
                    $opsi = collect($urutanOpsi)->map(
                        fn($kode) => $opsi->firstWhere('kode', $kode)
                    )->filter(); // filter to avoid nulls if any options were deleted or mismatched
                }

                $soalData['pilihan'] = $opsi->values();
                $soalData['jawaban_siswa'] = $jawabanLog->jawaban ?? null;
            } 
            elseif ($soal->tipe === 'benar_salah') {
                $soalData['jawaban_siswa'] = $jawabanLog->jawaban ?? null;
            }
            elseif ($soal->tipe === 'essay') {
                $soalData['jawaban_siswa'] = $jawabanLog->jawaban ?? '';
            }
            elseif ($soal->tipe === 'menjodohkan') {
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
                'status'     => 'selesai',
                'selesai_at' => now(),
            ]);

            LogUjian::create([
                'peserta_ujian_id' => $peserta->id,
                'tipe_event'       => 'submit_ujian',
                'ip_address'       => $ip,
                'user_agent'       => $userAgent,
                'terjadi_at'       => now(),
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

        foreach ($jawabanSiswa as $j) {
            $soal = $j->soal;
            if (!$soal) continue;

            switch ($soal->tipe) {
                case 'pg':
                case 'benar_salah':
                    $isBenar = (strtolower(trim($j->jawaban)) === strtolower(trim($soal->kunci_jawaban)));
                    $nilai = $isBenar ? $soal->bobot : 0;
                    $j->update([
                        'is_benar' => $isBenar,
                        'nilai'    => $nilai,
                    ]);
                    
                    if ($soal->tipe === 'pg') $nilaiPg += $nilai;
                    else $nilaiBs += $nilai;
                    break;
                
                case 'menjodohkan':
                    $pasangan = $soal->pasanganMenjodohkan->pluck('kanan','kiri');
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
                    
                    $j->update([
                        'is_benar' => $benarCount === $totalPasangan,
                        'nilai'    => $nilai,
                    ]);
                    $nilaiMenjodohkan += $nilai;
                    break;
                
                case 'essay':
                    $adaEssay = true;
                    // Nilai essay akan diisi manual oleh guru
                    break;
            }
        }

        $nilaiAkhir = $adaEssay 
            ? null // Tunggu essay dinilai
            : ($nilaiPg + $nilaiBs + $nilaiMenjodohkan);

        $peserta->update([
            'nilai_pg'            => $nilaiPg,
            'nilai_bs'            => $nilaiBs,
            'nilai_menjodohkan'   => $nilaiMenjodohkan,
            'nilai_akhir'         => $nilaiAkhir,
            'sudah_dikoreksi'     => true,
            'essay_sudah_dinilai' => !$adaEssay,
        ]);
    }
}
