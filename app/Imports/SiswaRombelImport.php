<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;

class SiswaRombelImport implements ToModel, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    protected int $rombelId;
    public array $createdAccounts = [];

    public function __construct(int $rombelId)
    {
        $this->rombelId = $rombelId;
    }

    public function model(array $row): ?Siswa
    {
        if (empty($row['nisn']) || empty($row['nama']) || empty($row['tanggal_lahir'])) {
            return null;
        }

        $nisn = trim($row['nisn']);
        $nama = trim($row['nama']);

        try {
            $tgl = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }

        $agama = null;
        if (!empty($row['agama'])) {
            $agama = trim($row['agama']);
        }

        $formattedPassword = date('dmY', strtotime($tgl)) . '*';

        try {
            DB::transaction(function () use ($nisn, $nama, $tgl, $agama, $formattedPassword, &$siswa) {
                $user = User::create([
                    'name'     => $nama,
                    'email'    => $nisn,
                    'password' => Hash::make($formattedPassword),
                ]);

                $user->assignRole('siswa');

                $siswa = Siswa::create([
                    'user_id'       => $user->id,
                    'rombel_id'     => $this->rombelId,
                    'nisn'          => $nisn,
                    'nama'          => $nama,
                    'tanggal_lahir' => $tgl,
                    'agama'         => $agama,
                ]);
            });

            $this->createdAccounts[] = [
                'nisn'     => $nisn,
                'password' => $formattedPassword,
            ];

            return $siswa ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
