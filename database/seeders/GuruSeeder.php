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
            // [nama, nip, slug-email, tanggal_lahir]
            ['Juliartono, S.Pd',              '197905052006011001', 'juliartono',          '1979-05-05'],
            ['Abdul Wahid, S.Pd',             '198203182008021002', 'abdul.wahid',         '1982-03-18'],
            ['Sasmitha Putri, S.Pd',          '199107252015042003', 'sasmitha.putri',      '1991-07-25'],
            ['Kiki Octania, S.Pd',            '198909122013012004', 'kiki.octania',        '1989-09-12'],
            ['Siti Khodijah Batu Bara,S.Pd',  '198604202010022005', 'siti.khodijah',       '1986-04-20'],
            ['Nuradliani, S.Pd',              '198501152009011006', 'nuradliani',          '1985-01-15'],
            ['Agustinawaty, S.Pd',            '198406232008042007', 'agustinawaty',        '1984-06-23'],
            ['Suningsih, S.Pd',               '198710302011012008', 'suningsih',           '1987-10-30'],
            ['Gunawan, S.Pd',                 '198302142007011009', 'gunawan',             '1983-02-14'],
            ['Sartika Panjaitan, S.Pd',       '199002262014022010', 'sartika.panjaitan',   '1990-02-26'],
            ['Lisna Sujati, S.Pd',            '198108172006012011', 'lisna.sujati',        '1981-08-17'],
            ['Cut Mutiara, S.Pd',             '198812052012012012', 'cut.mutiara',         '1988-12-05'],
            ['Yuanda Elsa Zahara,S.Pd',       '199306102018032013', 'yuanda.elsa',         '1993-06-10'],
            ['Syahriani Efendi, S.Pd',        '198708212011021014', 'syahriani.efendi',    '1987-08-21'],
            ['Setya Hadi Utomo,S.Pd',         '198211082007011015', 'setya.hadi',          '1982-11-08'],
            ['Maya Sari, S.Pd',               '199104182016022016', 'maya.sari',           '1991-04-18'],
            ['Helena CH J Pasaribu, S.Pd',    '198206272006021017', 'helena.pasaribu',     '1982-06-27'],
            ['Darmilawati Pohan,S.Pd',        '198903092013011018', 'darmilawati.pohan',   '1989-03-09'],
            ['Nurjanna Lubis, S.Pd',          '198507142009022019', 'nurjanna.lubis',      '1985-07-14'],
            ['Arbaiyah Batubara, S.Pd',       '198401292008012020', 'arbaiyah.batubara',   '1984-01-29'],
            ['Chusnul Khotimah, S.Pd',        '198808072012022021', 'chusnul.khotimah',    '1988-08-07'],
            ['Nong Suita, S.Pd',              '199002032014012022', 'nong.suita',          '1990-02-03'],
            ['Lasmauli Tampubolon, S.Pd',     '198310202008011023', 'lasmauli.tampubolon', '1983-10-20'],
            ['Nuramalina, S.Pd',              '198906162013022024', 'nuramalina',          '1989-06-16'],
            ['M. Irfan, S.Hi',                '199209242017012025', 'm.irfan',             '1992-09-24'],
            ['Fatimah, S.PdI',                '199305082018012026', 'fatimah',             '1993-05-08'],
            ['Gustina Gultom, S.Th',          '198404112008022027', 'gustina.gultom',      '1984-04-11'],
            ['Murnihayati Purba, S.Pd',       '198611222010012028', 'murnihayati.purba',   '1986-11-22'],
            ['Meylia Syahfitri, S.Pd',        '199208302017022029', 'meylia.syahfitri',    '1992-08-30'],
            ['Asna Susanti, S.Pd',            '199001192013012030', 'asna.susanti',        '1990-01-19'],
            ['Tukini,S.Pd',                   '198509282009022031', 'tukini',              '1985-09-28'],
            ['Jeniwati Br Tarigan, S.Sos',    '198707062011012032', 'jeniwati.tarigan',    '1987-07-06'],
            ['Apriani, S.Pd',                 '199211132016012033', 'apriani',             '1992-11-13'],
            ['Muhammad Andre,S.Pd',           '199304022017011034', 'muhammad.andre',      '1993-04-02'],
        ];

        foreach ($dataGuru as [$nama, $nip, $slug, $tgl]) {
            $email = $slug.'@siama.sch.id';

            $user = User::where('email', $email)->first();
            if (! $user) {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make(Guru::defaultPassword($tgl)),
                ]);
                $user->assignRole('guru');
            }

            Guru::withoutEvents(function () use ($nip, $user, $nama, $tgl) {
                Guru::firstOrCreate(
                    ['nip' => $nip],
                    [
                        'user_id' => $user->id,
                        'nama' => $nama,
                        'tanggal_lahir' => $tgl,
                    ]
                );
            });
        }

        $this->command->info(count($dataGuru).' data guru berhasil dibuat.');
    }
}
