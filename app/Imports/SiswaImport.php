<?php

namespace App\Imports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Carbon\Carbon;

class SiswaImport implements ToModel, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    public function model(array $row): ?Siswa
    {
        if (empty($row['nisn']) || empty($row['nama']) || empty($row['tanggal_lahir'])) {
            return null;
        }

        // Parse tanggal — support format: 2006-05-15, 15/05/2006, 15-05-2006
        try {
            $tgl = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }

        $status = strtolower(trim($row['status_lulus'] ?? 'lulus'));
        if (!in_array($status, ['lulus', 'tidak_lulus', 'ditunda'])) {
            $status = 'lulus';
        }

        return new Siswa([
            'nisn'          => trim($row['nisn']),
            'nama'          => trim($row['nama']),
            'tanggal_lahir' => $tgl,
            'status_lulus'  => $status,
            'keterangan'    => $row['keterangan'] ?? null,
        ]);
    }
}
