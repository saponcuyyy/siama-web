<?php
// Run: php tests/performance/seed-test-users.php [jumlah] [password] [sesi_id]
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Siswa;
use App\Models\Rombel;
use App\Models\PesertaUjian;
use App\Models\SesiUjian;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

$count = (int)($argv[1] ?? 600);
$password = $argv[2] ?? 'password';
$sesiId = (int)($argv[3] ?? 4);
$csvPath = __DIR__ . "/users-{$count}.csv";

$sesi = SesiUjian::find($sesiId);
if (!$sesi) {
    echo "ERROR: Sesi ID {$sesiId} tidak ditemukan.\n";
    exit(1);
}

$csv = fopen($csvPath, 'w');
fputcsv($csv, ['email', 'password', 'nisn', 'nama']);

$rombel = Rombel::first();
if (!$rombel) {
    echo "ERROR: Tidak ada rombel. Buat rombel dulu.\n";
    exit(1);
}

$siswaRole = Role::findByName('siswa');
$existingLoadTest = User::where('email', 'like', 'loadtest%@example.com')->count();
echo "Existing load test users: {$existingLoadTest}\n";
echo "Creating {$count} test users for sesi ID {$sesiId}...\n";
echo "Using rombel: {$rombel->id} - {$rombel->nama}\n\n";

$start = microtime(true);

for ($i = 1; $i <= $count; $i++) {
    $num = $existingLoadTest + $i;
    $email = "loadtest{$num}@example.com";
    $nisn = str_pad((string)(1000000000 + $num), 10, '0', STR_PAD_LEFT);
    $name = "Load Test User {$num}";

    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
    ]);
    $user->assignRole($siswaRole);

    $siswa = Siswa::create([
        'user_id' => $user->id,
        'rombel_id' => $rombel->id,
        'nisn' => $nisn,
        'nama' => $name,
        'tanggal_lahir' => '2000-01-01',
        'agama' => 'Islam',
    ]);

    // Daftarkan sebagai peserta ujian
    PesertaUjian::create([
        'sesi_ujian_id' => $sesiId,
        'siswa_id' => $siswa->id,
        'status' => 'belum_mulai',
    ]);

    fputcsv($csv, [$email, $password, $nisn, $name]);

    if ($i % 50 === 0) {
        $elapsed = round(microtime(true) - $start, 1);
        echo "  Created {$i}/{$count} users (${elapsed}s)...\n";
    }
}

fclose($csv);
$total = round(microtime(true) - $start, 1);
echo "\nDone! Created {$count} users in {$total}s.\n";
echo "CSV: {$csvPath}\n";
echo "Total peserta sesi {$sesiId}: " . PesertaUjian::where('sesi_ujian_id', $sesiId)->count() . "\n";
