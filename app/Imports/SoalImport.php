<?php

namespace App\Imports;

use App\Models\PilihanJawaban;
use App\Models\Soal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SoalImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $bankSoalId;

    public array $errors = [];

    public int $imported = 0;

    protected array $buffer = [];

    protected int $batchSize = 50;

    public function __construct(int $bankSoalId)
    {
        $this->bankSoalId = $bankSoalId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            try {
                $tipe = strtolower(trim($row['tipe'] ?? ''));
                $pertanyaan = trim($row['pertanyaan'] ?? '');
                $bobot = is_numeric($row['bobot'] ?? '') ? (int) $row['bobot'] : 1;
                $tingkat = strtolower(trim($row['tingkat_kesulitan'] ?? 'sedang'));
                $kunci = trim($row['kunci_jawaban'] ?? '');
                $pembahasan = trim($row['pembahasan'] ?? '');
                $bab = trim($row['bab'] ?? '');

                if (empty($tipe) || empty($pertanyaan)) {
                    $this->errors[] = "Baris {$rowNum}: Kolom 'tipe' dan 'pertanyaan' wajib diisi.";

                    continue;
                }

                if (! in_array($tipe, ['pg', 'essay', 'benar_salah', 'menjodohkan'])) {
                    $this->errors[] = "Baris {$rowNum}: Tipe '{$tipe}' tidak valid.";

                    continue;
                }

                if (! in_array($tingkat, ['mudah', 'sedang', 'sulit'])) {
                    $tingkat = 'sedang';
                }

                $pilihanData = [];
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
                        if (empty($teks)) {
                            continue;
                        }
                        $pilihanData[] = [
                            'kode' => $kode,
                            'teks' => $teks,
                            'is_benar' => (strtoupper($kunci) === $kode),
                            'urutan' => $urutan++,
                        ];
                    }
                    if (empty($kunci)) {
                        $this->errors[] = "Baris {$rowNum}: Kunci jawaban PG kosong.";
                    }
                }

                if ($tipe === 'benar_salah') {
                    $kunciNorm = strtolower($kunci);
                    $pilihanData = [
                        ['kode' => 'Benar', 'teks' => 'Benar', 'is_benar' => in_array($kunciNorm, ['benar', 'true', '1']), 'urutan' => 1],
                        ['kode' => 'Salah', 'teks' => 'Salah', 'is_benar' => ! in_array($kunciNorm, ['benar', 'true', '1']), 'urutan' => 2],
                    ];
                }

                $this->buffer[] = compact('tipe', 'pertanyaan', 'bobot', 'tingkat', 'kunci', 'pembahasan', 'bab', 'pilihanData', 'rowNum');

                if (count($this->buffer) >= $this->batchSize) {
                    $this->flushBuffer();
                }
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNum}: Gagal diproses — ".$e->getMessage();
            }
        }

        $this->flushBuffer();
    }

    protected function flushBuffer(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->buffer as $data) {
                $soal = Soal::create([
                    'bank_soal_id' => $this->bankSoalId,
                    'tipe' => $data['tipe'],
                    'pertanyaan' => $data['pertanyaan'],
                    'bobot' => max(1, $data['bobot']),
                    'tingkat_kesulitan' => $data['tingkat'],
                    'kunci_jawaban' => strtoupper($data['kunci']),
                    'pembahasan' => $data['pembahasan'] ?: null,
                    'bab' => $data['bab'] ?: null,
                ]);

                if (! empty($data['pilihanData'])) {
                    $pilihanInsert = [];
                    foreach ($data['pilihanData'] as $p) {
                        $pilihanInsert[] = [
                            'soal_id' => $soal->id,
                            'kode' => $p['kode'],
                            'teks' => $p['teks'],
                            'is_benar' => $p['is_benar'],
                            'urutan' => $p['urutan'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    PilihanJawaban::insert($pilihanInsert);
                }

                $this->imported++;
            }
        });

        $this->buffer = [];
    }
}
