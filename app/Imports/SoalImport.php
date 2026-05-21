<?php

namespace App\Imports;

use App\Models\Soal;
use App\Models\PilihanJawaban;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SoalImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected int $bankSoalId;
    public array $errors = [];
    public int $imported = 0;

    public function __construct(int $bankSoalId)
    {
        $this->bankSoalId = $bankSoalId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 karena heading di baris 1

            try {
                $tipe = strtolower(trim($row['tipe'] ?? ''));
                $pertanyaan = trim($row['pertanyaan'] ?? '');
                $bobot = is_numeric($row['bobot'] ?? '') ? (int)$row['bobot'] : 1;
                $tingkat = strtolower(trim($row['tingkat_kesulitan'] ?? 'sedang'));
                $kunci = trim($row['kunci_jawaban'] ?? '');
                $pembahasan = trim($row['pembahasan'] ?? '');
                $bab = trim($row['bab'] ?? '');

                // Validasi dasar
                if (empty($tipe) || empty($pertanyaan)) {
                    $this->errors[] = "Baris {$rowNum}: Kolom 'tipe' dan 'pertanyaan' wajib diisi.";
                    continue;
                }

                if (!in_array($tipe, ['pg', 'essay', 'benar_salah', 'menjodohkan'])) {
                    $this->errors[] = "Baris {$rowNum}: Tipe '{$tipe}' tidak valid. Gunakan: pg, essay, benar_salah, menjodohkan.";
                    continue;
                }

                if (!in_array($tingkat, ['mudah', 'sedang', 'sulit'])) {
                    $tingkat = 'sedang';
                }

                DB::transaction(function () use ($tipe, $pertanyaan, $bobot, $tingkat, $kunci, $pembahasan, $bab, $row, $rowNum) {
                    $soal = Soal::create([
                        'bank_soal_id'      => $this->bankSoalId,
                        'tipe'              => $tipe,
                        'pertanyaan'        => $pertanyaan,
                        'bobot'             => max(1, $bobot),
                        'tingkat_kesulitan' => $tingkat,
                        'kunci_jawaban'     => strtoupper($kunci),
                        'pembahasan'        => $pembahasan ?: null,
                        'bab'               => $bab ?: null,
                    ]);

                    // Pilihan ganda → buat pilihan A–E
                    if ($tipe === 'pg') {
                        $pilihan = [
                            'A' => trim($row['pilihan_a'] ?? $row['a'] ?? ''),
                            'B' => trim($row['pilihan_b'] ?? $row['b'] ?? ''),
                            'C' => trim($row['pilihan_c'] ?? $row['c'] ?? ''),
                            'D' => trim($row['pilihan_d'] ?? $row['d'] ?? ''),
                            'E' => trim($row['pilihan_e'] ?? $row['e'] ?? ''),
                        ];

                        $urutan = 1;
                        foreach ($pilihan as $kode => $teks) {
                            if (empty($teks)) continue;

                            PilihanJawaban::create([
                                'soal_id'  => $soal->id,
                                'kode'     => $kode,
                                'teks'     => $teks,
                                'is_benar' => (strtoupper($kunci) === $kode),
                                'urutan'   => $urutan++,
                            ]);
                        }

                        // Jika kunci tidak di-set, ambil dari is_benar
                        if (empty($kunci)) {
                            $this->errors[] = "Baris {$rowNum}: Kunci jawaban PG kosong.";
                        }
                    }

                    // Benar/Salah → kunci harus "Benar" atau "Salah"
                    if ($tipe === 'benar_salah') {
                        $kunciNorm = strtolower($kunci);
                        if (!in_array($kunciNorm, ['benar', 'salah', 'true', 'false', '1', '0'])) {
                            // Biarkan, validasi sudah di error array
                        }
                        foreach (['Benar', 'Salah'] as $i => $opt) {
                            PilihanJawaban::create([
                                'soal_id'  => $soal->id,
                                'kode'     => $opt,
                                'teks'     => $opt,
                                'is_benar' => in_array($kunciNorm, ['benar', 'true', '1']) ? ($opt === 'Benar') : ($opt === 'Salah'),
                                'urutan'   => $i + 1,
                            ]);
                        }
                    }
                });

                $this->imported++;

            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNum}: Gagal diproses — " . $e->getMessage();
            }
        }
    }
}
