<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        $dataGuru = [
            ['nama' => 'Drs. H. Ahmad Fauzi, M.Pd',               'nip' => '197508122000031001', 'email' => 'ahmad.fauzi@siama.sch.id',          'tanggal_lahir' => '1975-08-12'],
            ['nama' => 'Rina Wijayanti, S.Pd',                     'nip' => '198504232009042002', 'email' => 'rina.wijayanti@siama.sch.id',       'tanggal_lahir' => '1985-04-23'],
            ['nama' => 'Eko Prasetyo, S.Kom',                      'nip' => '199011052015021003', 'email' => 'eko.prasetyo@siama.sch.id',         'tanggal_lahir' => '1990-11-05'],
            ['nama' => 'Siti Rahmawati, S.Si',                     'nip' => '198802152010122001', 'email' => 'siti.rahmawati@siama.sch.id',       'tanggal_lahir' => '1988-02-15'],
            ['nama' => 'Dedi Iskandar, M.Si',                      'nip' => '198207192008011002', 'email' => 'dedi.iskandar@siama.sch.id',         'tanggal_lahir' => '1982-07-19'],
            ['nama' => 'Dra. Hj. Nurhayati, M.Pd',                 'nip' => '197003122005012001', 'email' => 'nurhayati@siama.sch.id',             'tanggal_lahir' => '1970-03-12'],
            ['nama' => 'Dr. Hendra Gunawan, S.Pd, M.Pd',           'nip' => '197806252006041002', 'email' => 'hendra.gunawan@siama.sch.id',        'tanggal_lahir' => '1978-06-25'],
            ['nama' => 'Yuni Astuti, S.Pd',                        'nip' => '199208142019032001', 'email' => 'yuni.astuti@siama.sch.id',            'tanggal_lahir' => '1992-08-14'],
            ['nama' => 'Agus Salim, S.Ag',                         'nip' => '197512102005011003', 'email' => 'agus.salim@siama.sch.id',            'tanggal_lahir' => '1975-12-10'],
            ['nama' => 'Fitri Handayani, S.Pd, M.Pd',              'nip' => '198612242009022002', 'email' => 'fitri.handayani@siama.sch.id',       'tanggal_lahir' => '1986-12-24'],
            ['nama' => 'Ir. Bambang Wijaya, M.T',                  'nip' => '197901152006031004', 'email' => 'bambang.wijaya@siama.sch.id',         'tanggal_lahir' => '1979-01-15'],
            ['nama' => 'Dewi Sartika, S.Pd',                       'nip' => '199103052018012001', 'email' => 'dewi.sartika@siama.sch.id',           'tanggal_lahir' => '1991-03-05'],
            ['nama' => 'Dr. Muhammad Ridwan, M.Si',                'nip' => '197408152000121001', 'email' => 'm.ridwan@siama.sch.id',               'tanggal_lahir' => '1974-08-15'],
            ['nama' => 'Nina Marlina, S.Si, M.Si',                 'nip' => '198710172011012002', 'email' => 'nina.marlina@siama.sch.id',           'tanggal_lahir' => '1987-10-17'],
            ['nama' => 'Rudi Hartono, S.Pd',                       'nip' => '199505062020011001', 'email' => 'rudi.hartono@siama.sch.id',           'tanggal_lahir' => '1995-05-06'],
            ['nama' => 'Lilis Suryani, S.Pd, M.Pd',                'nip' => '198308192007012003', 'email' => 'lilis.suryani@siama.sch.id',          'tanggal_lahir' => '1983-08-19'],
            ['nama' => 'H. Syamsul Arifin, S.Pd.I',                'nip' => '197612202006041002', 'email' => 'syamsul.arifin@siama.sch.id',         'tanggal_lahir' => '1976-12-20'],
            ['nama' => 'Maria Ulfah, S.T',                         'nip' => '199009112013092001', 'email' => 'maria.ulfah@siama.sch.id',            'tanggal_lahir' => '1990-09-11'],
            ['nama' => 'Dr. Antonius Wibowo, M.Kom',               'nip' => '198106232005021003', 'email' => 'antonius.wibowo@siama.sch.id',        'tanggal_lahir' => '1981-06-23'],
            ['nama' => 'Ratna Dewi, S.Pd',                         'nip' => '199312172020022001', 'email' => 'ratna.dewi@siama.sch.id',             'tanggal_lahir' => '1993-12-17'],
        ];

        foreach ($dataGuru as $g) {
            $user = User::where('email', $g['email'])->first();
            if (! $user) {
                $user = User::create([
                    'name' => $g['nama'],
                    'email' => $g['email'],
                    'password' => Hash::make('guru123'),
                ]);
                $user->assignRole('guru');
            }

            Guru::withoutEvents(function () use ($g, $user) {
                Guru::firstOrCreate(
                    ['nip' => $g['nip']],
                    [
                        'user_id' => $user->id,
                        'nama' => $g['nama'],
                        'tanggal_lahir' => $g['tanggal_lahir'],
                    ]
                );
            });
        }

        $this->command->info('20 data guru dummy berhasil dibuat.');
    }
}
