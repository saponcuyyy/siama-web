<?php

use App\Http\Controllers\Admin\Akademik\SemesterController;
use App\Http\Controllers\Admin\Akademik\TahunAjaranController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\BankSoalController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\DashboardWebController;
use App\Http\Controllers\Admin\FasilitasController;
use App\Http\Controllers\Admin\GaleriController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\KartuUjianController;
use App\Http\Controllers\Admin\KategoriBeritaController;
use App\Http\Controllers\Admin\LaporanUjianController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NilaiController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaketUjianController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\Admin\PenilaianEssayController;
use App\Http\Controllers\Admin\PesanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RombelController;
use App\Http\Controllers\Admin\SesiUjianController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\SiswaKelulusanController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\SoalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Public\BeritaPublikController;
use App\Http\Controllers\Public\GaleriPublikController;
use App\Http\Controllers\Public\KelulusanController;
use App\Http\Controllers\Public\KontakController;
use App\Http\Controllers\Public\PagePublikController;
use App\Http\Controllers\Public\PengumumanPublikController;
use App\Http\Controllers\Sys\CronController;
use App\Http\Controllers\Ujian\RuangUjianController;
use Illuminate\Support\Facades\Route;

// ─── WEB CRON (Hostinger Fix) ──────────────────────────────────────────────────
Route::get('/sys/cron', CronController::class);

// ─── PUBLIC ROUTES ───────────────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('home');

// Halaman Pengumuman Kelulusan
Route::get('/kelulusan', [KelulusanController::class, 'index'])->name('public.kelulusan');
Route::post('/kelulusan', [KelulusanController::class, 'cek'])->middleware('throttle:15,1')->name('public.kelulusan.cek');
Route::get('/kelulusan/hasil', [KelulusanController::class, 'hasil'])->name('public.kelulusan.hasil');

// Halaman Publik - Connected to real controllers
Route::get('/berita', [BeritaPublikController::class, 'index'])->name('public.berita.index');
Route::get('/berita/{slug}', [BeritaPublikController::class, 'show'])->name('public.berita.show');
Route::get('/pengumuman', [PengumumanPublikController::class, 'index'])->name('public.pengumuman.index');
Route::get('/galeri', [GaleriPublikController::class, 'index'])->name('public.galeri.index');
Route::get('/galeri/{album}', [GaleriPublikController::class, 'show'])->name('public.galeri.show');
Route::get('/kontak', [KontakController::class, 'index'])->name('public.kontak');
Route::post('/kontak', [App\Http\Controllers\Public\PesanController::class, 'store'])->name('public.pesan.store');

// Halaman Statis Dinamis
Route::get('/halaman/{slug}', [PagePublikController::class, 'show'])->name('public.page.show');

// Dashboard akademik
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'sync.ujian'])
    ->name('dashboard');

// ─── CMS / MANAJEMEN WEBSITE ───────────────────────────────────────────────
Route::prefix('admin/web')
    ->name('admin.web.')
    ->middleware(['auth', 'sync.ujian'])
    ->group(function () {

        // ── CMS (only settings.manage) ───────────────────────────────────────
        Route::middleware('can:settings.manage')->group(function () {
            Route::get('/', [DashboardWebController::class, 'index'])->name('dashboard');

            Route::get('pengaturan', [WebSettingController::class, 'index'])->name('setting');
            Route::put('pengaturan', [WebSettingController::class, 'update'])->name('setting.update');

            Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
            Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
            Route::put('menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
            Route::delete('menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');
            Route::post('menu/order', [MenuController::class, 'updateOrder'])->name('menu.order');

            Route::resource('slider', SliderController::class)->except(['show']);
            Route::resource('halaman', PageController::class)->except(['show']);
            Route::resource('kategori-berita', KategoriBeritaController::class)->except(['show', 'create', 'edit']);
            Route::resource('berita', BeritaController::class)->except(['show']);
            Route::resource('pengumuman', PengumumanController::class)->except(['show']);
            Route::resource('album', AlbumController::class);
            Route::post('album/{album}/photos', [AlbumController::class, 'uploadPhotos'])->name('album.photos.upload');
            Route::resource('galeri', GaleriController::class)->only(['destroy', 'update']);
            Route::resource('fasilitas', FasilitasController::class)->except(['show']);
            Route::post('fasilitas/order', [FasilitasController::class, 'updateOrder'])->name('fasilitas.order');

            Route::get('pesan', [PesanController::class, 'index'])->name('pesan.index');
            Route::get('pesan/{pesan}', [PesanController::class, 'show'])->name('pesan.show');
            Route::delete('pesan/{pesan}', [PesanController::class, 'destroy'])->name('pesan.destroy');

            Route::get('kelulusan', [SiswaKelulusanController::class, 'index'])->name('kelulusan.index');
            Route::post('kelulusan/import', [SiswaKelulusanController::class, 'import'])->name('kelulusan.import');
            Route::put('kelulusan/{siswa}', [SiswaKelulusanController::class, 'update'])->name('kelulusan.update');
            Route::delete('kelulusan/all', [SiswaKelulusanController::class, 'destroyAll'])->name('kelulusan.destroyAll');
            Route::delete('kelulusan/{siswa}', [SiswaKelulusanController::class, 'destroy'])->name('kelulusan.destroy');
            Route::get('kelulusan/template', [SiswaKelulusanController::class, 'downloadTemplate'])->name('kelulusan.template');
        });

        // ── Master Akademik ──────────────────────────────────────────────────
        Route::resource('rombel', RombelController::class)->except(['create', 'edit', 'show'])->middleware('can:siswa.view');
        Route::get('rombel/{rombel}/kartu-ujian',
            [KartuUjianController::class, 'perRombel'])
            ->name('rombel.kartu-ujian')->middleware('can:siswa.view');
        Route::get('kartu-ujian', [KartuUjianController::class, 'index'])
            ->name('kartu-ujian.index')->middleware('can:siswa.view');

        Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import')->middleware('can:guru.manage');
        Route::get('guru/template', [GuruController::class, 'downloadTemplate'])->name('guru.template')->middleware('can:guru.view');
        Route::resource('guru', GuruController::class)->except(['create', 'edit', 'show'])->middleware('can:guru.view');

        Route::resource('tahun-ajaran', TahunAjaranController::class)
            ->except(['create', 'edit', 'show'])->middleware('can:dashboard.view');

        Route::resource('semester', SemesterController::class)
            ->except(['create', 'edit', 'show'])->middleware('can:dashboard.view');

        Route::resource('siswa', SiswaController::class)->except(['create', 'edit', 'show'])->middleware('can:siswa.view');
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import')->middleware('can:siswa.manage');
        Route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template')->middleware('can:siswa.view');
    });

// ─── UJIAN ONLINE (CBT) - ADMIN / GURU ─────────────────────────────────────
Route::prefix('admin/ujian')
    ->name('admin.ujian.')
    ->middleware(['auth', 'sync.ujian'])
    ->group(function () {
        // Mata Pelajaran
        Route::resource('mata-pelajaran', MataPelajaranController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('can:ujian.manage');

        // Bank Soal
        Route::resource('bank-soal', BankSoalController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy'])
            ->middleware('can:ujian.bank-soal.manage');

        // Soal dalam bank
        Route::resource('bank-soal.soal', SoalController::class)
            ->middleware('can:ujian.soal.manage');

        // Import soal via Excel
        Route::post('bank-soal/{bankSoal}/import-excel', [SoalController::class, 'importExcel'])
            ->name('bank-soal.import-excel')
            ->middleware('can:ujian.soal.manage');

        // Download template Excel
        Route::get('soal/template/excel', [SoalController::class, 'downloadTemplate'])
            ->name('soal.template.excel');

        // Import soal via Word
        Route::post('bank-soal/{bankSoal}/import-word', [SoalController::class, 'import'])
            ->name('bank-soal.import-word')
            ->middleware('can:ujian.soal.manage');

        // Download template Word
        Route::get('soal/template/word', [SoalController::class, 'downloadWordTemplate'])
            ->name('soal.template.word');

        // Paket Ujian
        Route::resource('paket', PaketUjianController::class)
            ->middleware('can:ujian.paket.manage');
        Route::post('paket/{paket}/tambah-soal',
            [PaketUjianController::class, 'tambahSoal'])->name('paket.tambah-soal');
        Route::delete('paket/{paket}/soal/{soal}',
            [PaketUjianController::class, 'hapusSoal'])->name('paket.hapus-soal');

        // Sesi Ujian
        Route::resource('sesi', SesiUjianController::class)
            ->middleware('can:ujian.sesi.manage');
        Route::post('sesi/{sesi}/tambah-peserta',
            [SesiUjianController::class, 'tambahPeserta'])->name('sesi.tambah-peserta');
        Route::patch('sesi/{sesi}/toggle-status',
            [SesiUjianController::class, 'toggleStatus'])->name('sesi.toggle');
        Route::post('sesi/{sesi}/generate-peserta',
            [SesiUjianController::class, 'generatePeserta'])->name('sesi.generate-peserta');
        Route::get('sesi/{sesi}/monitor',
            [SesiUjianController::class, 'monitor'])->name('sesi.monitor');
        Route::get('sesi/{sesi}/peserta/{peserta}',
            [SesiUjianController::class, 'detailPeserta'])->name('sesi.peserta.detail');
        Route::get('sesi/{sesi}/kartu-ujian',
            [SesiUjianController::class, 'kartuUjian'])->name('sesi.kartu-ujian');

        // Penilaian Essay
        Route::get('penilaian',
            [PenilaianEssayController::class, 'index'])->name('penilaian.index')->middleware('can:ujian.penilaian.essay');
        Route::post('penilaian/{jawaban}/nilai',
            [PenilaianEssayController::class, 'nilai'])->name('penilaian.nilai')->middleware('can:ujian.penilaian.essay');

        // Laporan & Export
        Route::get('laporan', [LaporanUjianController::class, 'index'])->name('laporan.index')->middleware('can:ujian.laporan.view');
        Route::get('laporan/sesi/{sesi}', [LaporanUjianController::class, 'perSesi'])->name('laporan.sesi')->middleware('can:ujian.laporan.view');
        Route::get('laporan/sesi/{sesi}/export/{format}', [LaporanUjianController::class, 'export'])
            ->name('laporan.export')
            ->middleware('can:ujian.laporan.export')
            ->where('format', 'excel|pdf');
        Route::get('laporan/rekap-rombel/export', [LaporanUjianController::class, 'rekapRombel'])->name('laporan.rekap-rombel')->middleware('can:ujian.laporan.export');

        // Nilai (Rekap nilai siswa per rombel per mata pelajaran)
        Route::get('nilai', [NilaiController::class, 'index'])->name('nilai.index')->middleware('can:nilai.view');
        Route::get('nilai/export', [NilaiController::class, 'export'])->name('nilai.export')->middleware('can:nilai.view');

        // Soal (standalone store/destroy for BankSoal Show page)
        Route::post('soal', [SoalController::class, 'store'])->name('soal.store')->middleware('can:ujian.soal.manage');
        Route::put('soal/{soal}/bobot', [SoalController::class, 'updateBobot'])->name('soal.update-bobot')->middleware('can:ujian.soal.manage');
        Route::delete('soal/{soal}', [SoalController::class, 'destroy'])->name('soal.destroy')->middleware('can:ujian.soal.manage');
    });

// ─── MANAJEMEN USER & RBAC ──────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'sync.ujian'])
    ->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show'])->middleware('can:users.view');
        Route::resource('roles', RoleController::class)->except(['create', 'edit', 'show'])->middleware('can:roles.view');
    });

// ─── UJIAN ONLINE (CBT) - SISWA ────────────────────────────────────────────
Route::prefix('ujian')
    ->name('ujian.')
    ->middleware(['exam.auth', 'throttle:60,1', 'sync.ujian'])
    ->group(function () {
        Route::get('/', [RuangUjianController::class, 'index'])->name('index');
        Route::post('/logout', [RuangUjianController::class, 'logout'])->name('logout');
        Route::get('/{sesi}/masuk', [RuangUjianController::class, 'masuk'])->name('masuk');
        Route::post('/{sesi}/mulai', [RuangUjianController::class, 'mulai'])->name('mulai');
        Route::get('/{sesi}/ruang', [RuangUjianController::class, 'ruang'])->name('ruang');
        Route::post('/{sesi}/simpan', [RuangUjianController::class, 'simpan'])->name('simpan');
        Route::post('/{sesi}/simpan-batch', [RuangUjianController::class, 'simpanBatch'])->name('simpan-batch');
        Route::post('/{sesi}/selesai', [RuangUjianController::class, 'selesai'])->name('selesai');
        Route::post('/{sesi}/pelanggaran', [RuangUjianController::class, 'pelanggaran'])->name('pelanggaran');
        Route::get('/{sesi}/hasil', [RuangUjianController::class, 'hasil'])->name('hasil');
    });
