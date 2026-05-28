<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SiswaRombelImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected int $rombelId;
    public array $createdAccounts = [];
    protected array $buffer = [];
    protected int $batchSize = 50;

    public function __construct(int $rombelId)
    {
        $this->rombelId = $rombelId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['nisn']) || empty($row['nama']) || empty($row['tanggal_lahir'])) {
                continue;
            }

            try {
                $nisn = trim($row['nisn']);
                $nama = trim($row['nama']);
                $tgl = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                $agama = !empty($row['agama']) ? trim($row['agama']) : null;
                $password = Str::password(8);

                $this->buffer[] = compact('nisn', 'nama', 'tgl', 'agama', 'password');

                if (count($this->buffer) >= $this->batchSize) {
                    $this->flushBuffer();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->flushBuffer();
    }

    protected function flushBuffer(): void
    {
        if (empty($this->buffer)) return;

        DB::transaction(function () {
            foreach ($this->buffer as $data) {
                $user = User::create([
                    'name'     => $data['nama'],
                    'email'    => $data['nisn'],
                    'password' => Hash::make($data['password']),
                ]);

                $user->assignRole('siswa');

                $siswa = Siswa::create([
                    'user_id'       => $user->id,
                    'rombel_id'     => $this->rombelId,
                    'nisn'          => $data['nisn'],
                    'nama'          => $data['nama'],
                    'tanggal_lahir' => $data['tgl'],
                    'agama'         => $data['agama'],
                ]);

                $this->createdAccounts[] = [
                    'nisn'     => $data['nisn'],
                    'password' => $data['password'],
                ];
            }
        });

        $this->buffer = [];
    }
}
