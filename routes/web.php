<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardWebController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\KategoriBeritaController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\Admin\GaleriController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\FasilitasController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\PesanController;
use App\Http\Controllers\Admin\WebSettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SiswaKelulusanController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\RombelController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\LaporanUjianController;

// ─── WEB CRON (Hostinger Fix) ──────────────────────────────────────────────────
Route::get('/sys/cron', function () {
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    return 'Cron ran at ' . now();
});

// ─── PUBLIC ROUTES ───────────────────────────────────────────────────────────
Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('home');

// Halaman Pengumuman Kelulusan
Route::get('/kelulusan', [App\Http\Controllers\Public\KelulusanController::class, 'index'])->name('public.kelulusan');
Route::post('/kelulusan', [App\Http\Controllers\Public\KelulusanController::class, 'cek'])->name('public.kelulusan.cek');
Route::get('/kelulusan/hasil', [App\Http\Controllers\Public\KelulusanController::class, 'hasil'])->name('public.kelulusan.hasil');


// Halaman Publik - Connected to real controllers
Route::get('/berita', [App\Http\Controllers\Public\BeritaPublikController::class, 'index'])->name('public.berita.index');
Route::get('/berita/{slug}', [App\Http\Controllers\Public\BeritaPublikController::class, 'show'])->name('public.berita.show');
Route::get('/pengumuman', [App\Http\Controllers\Public\PengumumanPublikController::class, 'index'])->name('public.pengumuman.index');
Route::get('/galeri', [App\Http\Controllers\Public\GaleriPublikController::class, 'index'])->name('public.galeri.index');
Route::get('/galeri/{album}', [App\Http\Controllers\Public\GaleriPublikController::class, 'show'])->name('public.galeri.show');
Route::get('/kontak', function() {
    return \Inertia\Inertia::render('Public/Kontak', [
        'settings' => \Illuminate\Support\Facades\Cache::remember('settings', 3600, fn () => \App\Models\Setting::pluck('value', 'key')),
    ]);
})->name('public.kontak');
Route::post('/kontak', [App\Http\Controllers\Public\PesanController::class, 'store'])->name('public.pesan.store');

// Halaman Statis Dinamis
Route::get('/halaman/{slug}', [App\Http\Controllers\Public\PagePublikController::class, 'show'])->name('public.page.show');



// Dashboard akademik
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('siswa')) {
        return redirect()->route('ujian.index');
    }
    return \Inertia\Inertia::render('Dashboard', [
        'stats' => [
            'total_siswa' => \App\Models\Siswa::count(),
            'total_guru' => \App\Models\Guru::count(),
            'total_rombel' => \App\Models\Rombel::count(),
        ]
    ]);
})->middleware(['auth'])->name('dashboard');

// ─── CMS / MANAJEMEN WEBSITE ───────────────────────────────────────────────
Route::prefix('admin/web')
    ->name('admin.web.')
    ->middleware(['auth', 'can:dashboard.view'])
    ->group(function () {

        // Dashboard Web
        Route::get('/', [DashboardWebController::class, 'index'])->name('dashboard');

        // Pengaturan Website
        Route::get('pengaturan', [WebSettingController::class, 'index'])->name('setting');
        Route::put('pengaturan', [WebSettingController::class, 'update'])->name('setting.update');

        // Manajemen Menu Navigasi
        Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
        Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
        Route::put('menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');
        Route::post('menu/order', [MenuController::class, 'updateOrder'])->name('menu.order');

        // Manajemen Slider
        Route::resource('slider', SliderController::class)->except(['show']);

        // Manajemen Halaman Statis
        Route::resource('halaman', PageController::class)->except(['show']);

        // Manajemen Berita
        Route::resource('kategori-berita', KategoriBeritaController::class)->except(['show', 'create', 'edit']);
        Route::resource('berita', BeritaController::class)->except(['show']);

        // Manajemen Pengumuman
        Route::resource('pengumuman', PengumumanController::class)->except(['show']);

        // Manajemen Galeri & Album
        Route::resource('album', AlbumController::class);
        Route::post('album/{album}/photos', [AlbumController::class, 'uploadPhotos'])->name('album.photos.upload');
        Route::resource('galeri', GaleriController::class)->only(['destroy', 'update']);

        // Manajemen Fasilitas
        Route::resource('fasilitas', FasilitasController::class)->except(['show']);
        Route::post('fasilitas/order', [FasilitasController::class, 'updateOrder'])->name('fasilitas.order');

        // Pesan Masuk (Form Kontak)
        Route::get('pesan', [PesanController::class, 'index'])->name('pesan.index');
        Route::get('pesan/{pesan}', [PesanController::class, 'show'])->name('pesan.show');
        Route::delete('pesan/{pesan}', [PesanController::class, 'destroy'])->name('pesan.destroy');

        // Manajemen Kelulusan Siswa
        Route::get('kelulusan', [SiswaKelulusanController::class, 'index'])->name('kelulusan.index');
        Route::post('kelulusan/import', [SiswaKelulusanController::class, 'import'])->name('kelulusan.import');
        Route::put('kelulusan/{siswa}', [SiswaKelulusanController::class, 'update'])->name('kelulusan.update');
        Route::delete('kelulusan/all', [SiswaKelulusanController::class, 'destroyAll'])->name('kelulusan.destroyAll');
        Route::delete('kelulusan/{siswa}', [SiswaKelulusanController::class, 'destroy'])->name('kelulusan.destroy');
        Route::get('kelulusan/template', [SiswaKelulusanController::class, 'downloadTemplate'])->name('kelulusan.template');

        // ── Master Akademik ──────────────────────────────────────────────────
        // Rombel
        Route::resource('rombel', RombelController::class)->except(['create', 'edit', 'show']);
        Route::get('rombel/{rombel}/kartu-ujian',
                   [App\Http\Controllers\Admin\KartuUjianController::class, 'perRombel'])
                   ->name('rombel.kartu-ujian');

        // Kartu Ujian
        Route::get('kartu-ujian', [App\Http\Controllers\Admin\KartuUjianController::class, 'index'])
               ->name('kartu-ujian.index');

        // Guru
        Route::resource('guru', GuruController::class)->except(['create', 'edit', 'show']);

        // Siswa
        Route::resource('siswa', SiswaController::class)->except(['create', 'edit', 'show']);
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
    });

// ─── UJIAN ONLINE (CBT) - ADMIN / GURU ─────────────────────────────────────
Route::prefix('admin/ujian')
    ->name('admin.ujian.')
    ->middleware(['auth'])
    ->group(function () {
        // Mata Pelajaran
        Route::resource('mata-pelajaran', App\Http\Controllers\Admin\MataPelajaranController::class)
             ->only(['index', 'store', 'update', 'destroy']);

        // Bank Soal
        Route::resource('bank-soal', App\Http\Controllers\Admin\BankSoalController::class)
             ->only(['index', 'store', 'show', 'update', 'destroy'])
             ->middleware('can:ujian.bank-soal.manage');

        // Soal dalam bank
        Route::resource('bank-soal.soal', App\Http\Controllers\Admin\SoalController::class)
             ->middleware('can:ujian.soal.manage');

        // Import soal via Excel
        Route::post('bank-soal/{bankSoal}/import-excel', [App\Http\Controllers\Admin\SoalController::class, 'importExcel'])
             ->name('bank-soal.import-excel')
             ->middleware('can:ujian.soal.manage');

        // Download template Excel
        Route::get('soal/template/excel', [App\Http\Controllers\Admin\SoalController::class, 'downloadTemplate'])
             ->name('soal.template.excel');

        // Import soal via Word
        Route::post('bank-soal/{bankSoal}/import-word', [App\Http\Controllers\Admin\SoalController::class, 'import'])
             ->name('bank-soal.import-word')
             ->middleware('can:ujian.soal.manage');

        // Download template Word
        Route::get('soal/template/word', [App\Http\Controllers\Admin\SoalController::class, 'downloadWordTemplate'])
             ->name('soal.template.word');

        // Paket Ujian
        Route::resource('paket', App\Http\Controllers\Admin\PaketUjianController::class)
             ->middleware('can:ujian.paket.manage');
        Route::post('paket/{paket}/tambah-soal',
                    [App\Http\Controllers\Admin\PaketUjianController::class, 'tambahSoal'])->name('paket.tambah-soal');
        Route::delete('paket/{paket}/soal/{soal}',
                      [App\Http\Controllers\Admin\PaketUjianController::class, 'hapusSoal'])->name('paket.hapus-soal');

        // Sesi Ujian
        Route::resource('sesi', App\Http\Controllers\Admin\SesiUjianController::class)
             ->middleware('can:ujian.sesi.manage');
        Route::post('sesi/{sesi}/tambah-peserta',
                    [App\Http\Controllers\Admin\SesiUjianController::class, 'tambahPeserta'])->name('sesi.tambah-peserta');
        Route::patch('sesi/{sesi}/toggle-status',
                     [App\Http\Controllers\Admin\SesiUjianController::class, 'toggleStatus'])->name('sesi.toggle');
        Route::post('sesi/{sesi}/generate-peserta',
                    [App\Http\Controllers\Admin\SesiUjianController::class, 'generatePeserta'])->name('sesi.generate-peserta');
        Route::get('sesi/{sesi}/monitor',
                   [App\Http\Controllers\Admin\SesiUjianController::class, 'monitor'])->name('sesi.monitor');
        Route::get('sesi/{sesi}/kartu-ujian',
                   [App\Http\Controllers\Admin\SesiUjianController::class, 'kartuUjian'])->name('sesi.kartu-ujian');

        // Penilaian Essay
        Route::get('penilaian',
                   [App\Http\Controllers\Admin\PenilaianEssayController::class, 'index'])->name('penilaian.index');
        Route::post('penilaian/{jawaban}/nilai',
                    [App\Http\Controllers\Admin\PenilaianEssayController::class, 'nilai'])->name('penilaian.nilai');

        // Laporan & Export
        Route::get('laporan', [LaporanUjianController::class, 'index'])->name('laporan.index');
        Route::get('laporan/sesi/{sesi}', [LaporanUjianController::class, 'perSesi'])->name('laporan.sesi');
        Route::get('laporan/sesi/{sesi}/export/{format}', [LaporanUjianController::class, 'export'])
             ->name('laporan.export')
             ->where('format', 'excel|pdf');
        Route::get('laporan/rekap-rombel/export', [LaporanUjianController::class, 'rekapRombel'])->name('laporan.rekap-rombel');

        // Nilai (Rekap nilai siswa per rombel per mata pelajaran)
        Route::get('nilai', [App\Http\Controllers\Admin\NilaiController::class, 'index'])->name('nilai.index');
        Route::get('nilai/export', [App\Http\Controllers\Admin\NilaiController::class, 'export'])->name('nilai.export');

        // Soal (standalone store/destroy for BankSoal Show page)
        Route::post('soal', [App\Http\Controllers\Admin\SoalController::class, 'store'])->name('soal.store')->middleware('can:ujian.soal.manage');
        Route::put('soal/{soal}/bobot', [App\Http\Controllers\Admin\SoalController::class, 'updateBobot'])->name('soal.update-bobot')->middleware('can:ujian.soal.manage');
        Route::delete('soal/{soal}', [App\Http\Controllers\Admin\SoalController::class, 'destroy'])->name('soal.destroy')->middleware('can:ujian.soal.manage');
    });

// ─── MANAJEMEN USER & RBAC ──────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'can:dashboard.view'])
    ->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('roles', RoleController::class)->except(['create', 'edit', 'show']);
    });

// ─── UJIAN ONLINE (CBT) - SISWA ────────────────────────────────────────────
Route::prefix('ujian')
    ->name('ujian.')
    ->middleware(['exam.auth', 'throttle:60,1'])
    ->group(function () {
        Route::get('/', [App\Http\Controllers\Ujian\RuangUjianController::class, 'index'])->name('index');
        Route::post('/logout', function (\Illuminate\Http\Request $request) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login?context=ujian');
        })->name('logout');
        Route::get('/{sesi}/masuk', [App\Http\Controllers\Ujian\RuangUjianController::class, 'masuk'])->name('masuk');
        Route::post('/{sesi}/mulai', [App\Http\Controllers\Ujian\RuangUjianController::class, 'mulai'])->name('mulai');
        Route::get('/{sesi}/ruang', [App\Http\Controllers\Ujian\RuangUjianController::class, 'ruang'])->name('ruang');
        Route::post('/{sesi}/simpan', [App\Http\Controllers\Ujian\RuangUjianController::class, 'simpan'])->name('simpan');
        Route::post('/{sesi}/selesai', [App\Http\Controllers\Ujian\RuangUjianController::class, 'selesai'])->name('selesai');
        Route::post('/{sesi}/pelanggaran', [App\Http\Controllers\Ujian\RuangUjianController::class, 'pelanggaran'])->name('pelanggaran');
        Route::get('/{sesi}/hasil', [App\Http\Controllers\Ujian\RuangUjianController::class, 'hasil'])->name('hasil');
    });
