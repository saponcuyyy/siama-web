<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class GuruImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $createdAccounts = [];
    protected array $buffer = [];
    protected int $batchSize = 50;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['nip']) || empty($row['nama']) || empty($row['email'])) {
                continue;
            }

            try {
                $nip = trim($row['nip']);
                $nama = trim($row['nama']);
                $email = trim($row['email']);
                
                $jabatan = !empty($row['jabatan']) ? trim($row['jabatan']) : 'Guru';
                
                $tgl = null;
                if (!empty($row['tanggal_lahir'])) {
                    $tgl = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                }
                
                $password = Str::password(10);

                // Check if user already exists
                $userExists = User::where('email', $email)->exists();
                $guruExists = Guru::where('nip', $nip)->exists();

                if ($userExists || $guruExists) {
                    continue; // Skip existing
                }

                $this->buffer[] = compact('nip', 'nama', 'email', 'jabatan', 'tgl', 'password');

                if (count($this->buffer) >= $this->batchSize) {
                    $this->flushBuffer();
                }
            } catch (\Exception $e) {
                Log::warning('GuruImport: skip baris NIP ' . ($nip ?? 'unknown') . ' — ' . $e->getMessage());
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
                    'email'    => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);

                $user->assignRole('guru');

                Guru::create([
                    'user_id'       => $user->id,
                    'nip'           => $data['nip'],
                    'nama'          => $data['nama'],
                    'jabatan'       => $data['jabatan'],
                    'tanggal_lahir' => $data['tgl'],
                ]);

                $this->createdAccounts[] = [
                    'email'    => $data['email'],
                    'password' => $data['password'],
                    'nama'     => $data['nama']
                ];
            }
        });

        $this->buffer = [];
    }
}
