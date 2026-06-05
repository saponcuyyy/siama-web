<?php

namespace App\Http\Controllers\Ujian;

use App\Enums\PesertaStatus;
use App\Enums\SesiStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ujian\MulaiUjianRequest;
use App\Http\Requests\Ujian\SimpanBatchJawabanRequest;
use App\Http\Requests\Ujian\SimpanJawabanRequest;
use App\Models\JawabanSiswa;
use App\Models\PesertaUjian;
use App\Models\SesiUjian;
use App\Models\Siswa;
use App\Services\Ujian\UjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class RuangUjianController extends Controller
{
    public function __construct(
        private UjianService $ujianService
    ) {}

    // POST /ujian/logout — logout siswa dari portal ujian
    public function logout(Request $request)
    {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login', ['context' => 'ujian']);
    }

    // GET /ujian — daftar ujian aktif untuk siswa
    public function index(Request $request)
    {
        $siswa = $request->user()->siswa;

        if (! $siswa) {
            return redirect()->route('dashboard');
        }

        // Cek dan tutup sesi yang expired yang berkaitan dengan siswa/rombel secara instan
        $now = now();
        $expiredSessions = SesiUjian::whereIn('status', [SesiStatus::MENUNGGU->value, SesiStatus::BERLANGSUNG->value])
            ->where(function ($q) use ($siswa) {
                $q->whereHas('pesertaUjian', fn ($q2) => $q2->where('siswa_id', $siswa->id))
                    ->orWhere('rombel_id', $siswa->rombel_id)
                    ->orWhereHas('rombels', fn ($q2) => $q2->where('rombel.id', $siswa->rombel_id));
            })
            ->get()
            ->filter(function ($session) {
                $toleransiDetik = ($session->toleransi_menit ?? 0) * 60;
                $batasWaktu = $session->waktu_selesai?->copy()->addSeconds($toleransiDetik);

                return $batasWaktu && $batasWaktu->isPast();
            });

        foreach ($expiredSessions as $session) {
            $session->update(['status' => SesiStatus::SELESAI->value]);

            // Akhiri ujian untuk peserta yang masih status 'mengerjakan' di sesi ini
            $pesertas = PesertaUjian::where('sesi_ujian_id', $session->id)
                ->where('status', PesertaStatus::MENGERJAKAN->value)
                ->get();

            foreach ($pesertas as $peserta) {
                try {
                    $this->ujianService->akhiriUjian($peserta, $request->ip(), $request->userAgent());
                } catch (\Throwable $e) {
                    Log::error("Index check close failed for peserta {$peserta->id}: ".$e->getMessage());
                }
            }
        }

        // Ambil sesi aktif (yang benar-benar masih aktif setelah pembersihan di atas)
        $sesiAktif = SesiUjian::with('paketUjian.mataPelajaran')
            ->where(function ($q) use ($siswa) {
                $q->whereHas('pesertaUjian', fn ($q2) => $q2->where('siswa_id', $siswa->id))
                    ->orWhere('rombel_id', $siswa->rombel_id)
                    ->orWhereHas('rombels', fn ($q2) => $q2->where('rombel.id', $siswa->rombel_id));
            })
            ->whereIn('status', [SesiStatus::MENUNGGU->value, SesiStatus::BERLANGSUNG->value])
            ->latest()
            ->get();

        $riwayat = PesertaUjian::with(['sesiUjian.paketUjian'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status', [PesertaStatus::SELESAI->value, PesertaStatus::DIDISKUALIFIKASI->value])
            ->latest()
            ->get();

        // Map status peserta siswa untuk setiap sesi aktif
        $sesiIds = $sesiAktif->pluck('id');
        $pesertaSaya = PesertaUjian::where('siswa_id', $siswa->id)
            ->whereIn('sesi_ujian_id', $sesiIds)
            ->get()
            ->keyBy('sesi_ujian_id');

        return Inertia::render('Ujian/Index', [
            'sesi_aktif' => $sesiAktif,
            'riwayat' => $riwayat,
            'peserta_saya' => $pesertaSaya,
        ]);
    }

    // GET /ujian/{sesi}/masuk — halaman form token & verifikasi
    public function masuk(SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $siswa = request()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Akses khusus siswa.');
        }

        // Auto-create peserta jika belum ada dan rombel cocok (atau sesi tanpa rombel)
        $peserta = PesertaUjian::firstOrCreate(
            ['sesi_ujian_id' => $sesi->id, 'siswa_id' => $siswa->id],
            ['status' => PesertaStatus::BELUM_MULAI->value]
        );

        // Jika sudah selesai atau diskualifikasi, redirect ke hasil
        if (in_array($peserta->status, [PesertaStatus::SELESAI->value, PesertaStatus::DIDISKUALIFIKASI->value])) {
            return redirect()->route('ujian.hasil', $sesi);
        }

        if ($this->checkAndCloseIfExpired($sesi, $peserta, request())) {
            return redirect()->route('ujian.hasil', $sesi);
        }

        return Inertia::render('Ujian/Masuk', [
            'sesi' => $sesi->load('paketUjian.mataPelajaran'),
            'peserta' => $peserta->load(['sesiUjian.paketUjian.mataPelajaran']),
        ]);
    }

    // POST /ujian/{sesi}/mulai — validasi token & mulai ujian
    public function mulai(MulaiUjianRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $siswa = $request->user()->siswa;
        if (! $siswa) {
            abort(403, 'Akses khusus siswa.');
        }

        // Auto-start session if it's MENUNGGU but start time has passed
        if ($sesi->status === SesiStatus::MENUNGGU->value && $sesi->waktu_mulai && $sesi->waktu_mulai->isPast()) {
            DB::transaction(function () use ($sesi) {
                $sesi->update(['status' => SesiStatus::BERLANGSUNG->value]);
                
                // Tambahkan peserta rombel yang belum terdaftar otomatis
                $rombelIds = [];
                if ($sesi->rombels->isNotEmpty()) {
                    $rombelIds = $sesi->rombels->pluck('id')->toArray();
                } elseif ($sesi->rombel_id) {
                    $rombelIds = [$sesi->rombel_id];
                }

                if (! empty($rombelIds)) {
                    $siswaIds = Siswa::whereIn('rombel_id', $rombelIds)->pluck('id');
                    $existing = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                        ->whereIn('siswa_id', $siswaIds)
                        ->pluck('siswa_id');

                    $newIds = $siswaIds->diff($existing);
                    $now = now();

                    $newPeserta = $newIds->map(fn ($id) => [
                        'sesi_ujian_id' => $sesi->id,
                        'siswa_id' => $id,
                        'status' => PesertaStatus::BELUM_MULAI->value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->toArray();

                    if (! empty($newPeserta)) {
                        PesertaUjian::insert($newPeserta);
                    }
                }
            });
            $sesi->refresh();
        }

        if ($request->token !== $sesi->token) {
            return back()->withErrors(['token' => 'Token ujian tidak valid.']);
        }

        if ($sesi->status !== SesiStatus::BERLANGSUNG->value) {
            return back()->withErrors(['token' => 'Sesi ujian ini belum dimulai atau sudah ditutup oleh Proktor.']);
        }

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        if ($peserta->isSesiAktifDiDevice($request->device_token)) {
            return back()->withErrors(['token' => 'Akun Anda sedang aktif mengerjakan ujian di perangkat lain.']);
        }

        $this->ujianService->mulaiUjian(
            $peserta,
            $request->device_token,
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->route('ujian.ruang', $sesi);
    }

    // GET /ujian/{sesi}/ruang — interface utama ujian siswa
    public function ruang(SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $siswa = request()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Akses khusus siswa.');
        }

        $peserta = PesertaUjian::with(['jawabanSiswa', 'sesiUjian.paketUjian'])
            ->where('sesi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        if (in_array($peserta->status, [PesertaStatus::SELESAI->value, PesertaStatus::DIDISKUALIFIKASI->value])) {
            return redirect()->route('ujian.hasil', $sesi);
        }

        if ($this->checkAndCloseIfExpired($sesi, $peserta, request())) {
            return redirect()->route('ujian.hasil', $sesi);
        }

        if ($peserta->status !== PesertaStatus::MENGERJAKAN->value) {
            return redirect()->route('ujian.masuk', $sesi);
        }

        // Cache soal untuk menghindari regenerasi urutan acak yang berulang
        $cacheKey = 'ujian_soal_'.$peserta->id;
        $soalList = Cache::remember($cacheKey, 3600, function () use ($peserta) {
            return $this->ujianService->getSoalUntukSiswa($peserta);
        });

        return Inertia::render('Ujian/Ruang', [
            'sesi' => $sesi->load('paketUjian.mataPelajaran'),
            'peserta' => $peserta,
            'soal_list' => $soalList,
            'sisa_waktu' => $peserta->sisa_waktu,
        ]);
    }

    private function getPeserta(SesiUjian $sesi): PesertaUjian
    {
        $siswa = auth()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Akses khusus siswa.');
        }

        return PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();
    }

    // POST /ujian/{sesi}/simpan-batch — simpan banyak jawaban dalam 1 request
    public function simpanBatch(SimpanBatchJawabanRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = $this->getPeserta($sesi);

        if ($this->checkAndCloseIfExpired($sesi, $peserta, $request)) {
            return response()->json(['message' => 'Waktu habis atau ujian sudah selesai'], 403);
        }

        if ($peserta->status !== PesertaStatus::MENGERJAKAN->value) {
            return response()->json(['message' => 'Ujian tidak aktif atau sudah selesai'], 403);
        }

        $answers = $request->input('answers', []);
        $urutanSoal = $peserta->urutan_soal ?? [];

        // Validasi soal_id
        $validAnswers = [];
        foreach ($answers as $item) {
            if (in_array($item['soal_id'], $urutanSoal)) {
                $validAnswers[] = $item;
            }
        }

        if (empty($validAnswers)) {
            return response()->json(['status' => 'success', 'count' => 0]);
        }

        DB::transaction(function () use ($validAnswers, $peserta) {
            foreach ($validAnswers as $item) {
                $jawaban = JawabanSiswa::firstOrNew([
                    'peserta_ujian_id' => $peserta->id,
                    'soal_id' => $item['soal_id'],
                ]);

                if (is_array($item['jawaban'])) {
                    $jawaban->jawaban_menjodohkan = $item['jawaban'];
                } else {
                    $jawaban->jawaban = $item['jawaban'];
                }

                $jawaban->durasi_detik = ($jawaban->durasi_detik ?? 0) + ($item['durasi'] ?? 0);
                $jawaban->dijawab_at = now();
                $jawaban->is_ragu = $item['is_ragu'] ?? false;

                if ($jawaban->isDirty()) {
                    $jawaban->save();
                }
            }
        });

        return response()->json(['status' => 'success', 'count' => count($validAnswers)]);
    }

    // POST /ujian/{sesi}/simpan — endpoint auto-save jawaban (simpan langsung)
    public function simpan(SimpanJawabanRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = $this->getPeserta($sesi);

        if ($this->checkAndCloseIfExpired($sesi, $peserta, $request)) {
            return response()->json(['message' => 'Waktu habis atau ujian sudah selesai'], 403);
        }

        if ($peserta->status !== PesertaStatus::MENGERJAKAN->value) {
            return response()->json(['message' => 'Ujian tidak aktif atau sudah selesai'], 403);
        }

        // Validasi soal_id
        $urutanSoal = $peserta->urutan_soal ?? [];
        if (!in_array($request->soal_id, $urutanSoal)) {
            return response()->json(['message' => 'Soal tidak valid untuk ujian ini'], 400);
        }

        // Throttling: maksimal 1 simpan per 5 detik per soal
        $lastSaveKey = 'ujian_last_save_'.$peserta->id.'_'.$request->soal_id;
        $lastSave = Cache::get($lastSaveKey);

        if ($lastSave && (now()->getTimestamp() - $lastSave) < 5) {
            return response()->json(['status' => 'throttled']);
        }

        Cache::put($lastSaveKey, now()->getTimestamp(), 5);

        // Simpan langsung ke database (ringan, index UNIQUE sudah ada)
        $jawaban = JawabanSiswa::firstOrNew([
            'peserta_ujian_id' => $peserta->id,
            'soal_id' => $request->soal_id,
        ]);

        if (is_array($request->jawaban)) {
            $jawaban->jawaban_menjodohkan = $request->jawaban;
        } else {
            $jawaban->jawaban = $request->jawaban;
        }

        $jawaban->durasi_detik = ($jawaban->durasi_detik ?? 0) + $request->durasi;
        $jawaban->dijawab_at = now();
        $jawaban->is_ragu = $request->is_ragu ?? false;
        $jawaban->save();

        return response()->json(['status' => 'success']);
    }

    // POST /ujian/{sesi}/pelanggaran — catat pelanggaran secara langsung (synchronous)
    public function pelanggaran(Request $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $siswa = $request->user()->siswa;
        if (! $siswa) {
            abort(403, 'Akses khusus siswa.');
        }

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        // Jika sudah selesai atau didiskualifikasi, jangan catat lagi
        if (in_array($peserta->status, [PesertaStatus::SELESAI->value, PesertaStatus::DIDISKUALIFIKASI->value])) {
            return response()->json([
                'status' => $peserta->status,
                'jumlah_pelanggaran' => $peserta->jumlah_pelanggaran,
            ]);
        }

        // Validasi tipe input
        $request->validate([
            'tipe' => 'required|string|in:pindah_tab,blur_window,keluar_fullscreen,unload_tab'
        ]);

        $tipe = $request->input('tipe');

        // Throttling: maksimal 1 pelanggaran per 5 detik untuk mencegah spam
        $lastViolationKey = 'ujian_last_violation_'.$peserta->id;
        $lastViolation = Cache::get($lastViolationKey);

        if ($lastViolation && (now()->getTimestamp() - $lastViolation) < 5) {
            return response()->json([
                'status' => $peserta->status,
                'jumlah_pelanggaran' => $peserta->jumlah_pelanggaran,
            ]);
        }

        Cache::put($lastViolationKey, now()->getTimestamp(), 5);

        // Panggil service secara synchronous — increment terjadi sebelum response dikirim
        $this->ujianService->catatPelanggaran(
            $peserta,
            $tipe,
            $request->ip(),
            $request->userAgent()
        );

        // Refresh dari DB untuk mendapatkan nilai terbaru setelah increment
        $peserta->refresh();

        return response()->json([
            'status' => $peserta->status,
            'jumlah_pelanggaran' => $peserta->jumlah_pelanggaran,
        ]);
    }

    // POST /ujian/{sesi}/selesai — submit manual oleh siswa
    public function selesai(Request $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $siswa = $request->user()->siswa;
        if (! $siswa) {
            abort(403, 'Akses khusus siswa.');
        }

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        // Hapus cache soal agar data tidak basi
        Cache::forget('ujian_soal_'.$peserta->id);

        $this->ujianService->akhiriUjian(
            $peserta,
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->route('ujian.hasil', $sesi);
    }

    // GET /ujian/{sesi}/hasil — tampilan hasil
    public function hasil(SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $siswa = request()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Akses khusus siswa.');
        }

        $peserta = PesertaUjian::with(['jawabanSiswa', 'sesiUjian.paketUjian'])
            ->where('sesi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        return Inertia::render('Ujian/Hasil', [
            'sesi' => $sesi->load('paketUjian.mataPelajaran'),
            'peserta' => $peserta,
        ]);
    }

    // Helper untuk mengecek dan menutup sesi ujian jika batas waktu selesai + toleransi sudah lewat
    // atau jika durasi pengerjaan siswa sendiri telah habis.
    private function checkAndCloseIfExpired(SesiUjian $sesi, PesertaUjian $peserta, Request $request): bool
    {
        // Jika status sudah selesai atau didiskualifikasi, tidak perlu diproses lagi
        if (in_array($peserta->status, [PesertaStatus::SELESAI->value, PesertaStatus::DIDISKUALIFIKASI->value])) {
            return true;
        }

        $isExpired = false;
        $sessionTimeExpired = false;

        // 1. Cek batas waktu sesi absolut (waktu_selesai + toleransi_menit)
        $toleransiDetik = ($sesi->toleransi_menit ?? 0) * 60;
        $batasWaktuSesi = $sesi->waktu_selesai?->copy()->addSeconds($toleransiDetik);
        if ($batasWaktuSesi && $batasWaktuSesi->isPast()) {
            $isExpired = true;
            $sessionTimeExpired = true;
        }

        // 2. Cek batas waktu durasi pengerjaan siswa sendiri (sisa_waktu <= 0)
        if (!$isExpired && $peserta->status === PesertaStatus::MENGERJAKAN->value) {
            if ($peserta->sisa_waktu <= 0) {
                $isExpired = true;
            }
        }

        if ($isExpired) {
            if ($sessionTimeExpired) {
                // Waktu sesi habis -> tutup seluruh sesi & akhiri semua peserta aktif
                $activePeserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                    ->where('status', PesertaStatus::MENGERJAKAN->value)
                    ->get();

                foreach ($activePeserta as $p) {
                    Cache::forget('ujian_soal_'.$p->id);
                    try {
                        $this->ujianService->akhiriUjian(
                            $p,
                            $request->ip(),
                            $request->userAgent()
                        );
                    } catch (\Throwable $e) {
                        Log::error("checkAndCloseIfExpired: Gagal akhiri peserta {$p->id}: ".$e->getMessage());
                    }
                }

                $sesi->update(['status' => SesiStatus::SELESAI->value]);
            } elseif ($peserta->status === PesertaStatus::MENGERJAKAN->value) {
                // Hanya waktu individu siswa yang habis -> akhiri siswa ini saja
                Cache::forget('ujian_soal_'.$peserta->id);

                $this->ujianService->akhiriUjian(
                    $peserta,
                    $request->ip(),
                    $request->userAgent()
                );
                $peserta->refresh();
            }

            return true;
        }

        return false;
    }
}
