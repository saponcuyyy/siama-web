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
        'settings' => App\Models\Setting::pluck('value', 'key'),
    ]);
})->name('public.kontak');
Route::post('/kontak', [App\Http\Controllers\Public\PesanController::class, 'store'])->name('public.pesan.store');

// Halaman Statis Dinamis
Route::get('/halaman/{slug}', [App\Http\Controllers\Public\PagePublikController::class, 'show'])->name('public.page.show');



// Dashboard akademik
Route::get('/dashboard', function () {
    return \Inertia\Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ─── CMS / MANAJEMEN WEBSITE ───────────────────────────────────────────────
Route::prefix('admin/web')
    ->name('admin.web.')
    ->middleware(['auth', 'verified'])
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
    });
