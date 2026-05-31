<?php
namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Enums\{PesertaStatus, SesiStatus};
use App\Models\{SesiUjian, PesertaUjian, JawabanSiswa};
use App\Services\Ujian\UjianService;
use App\Http\Requests\Ujian\{MulaiUjianRequest, SimpanJawabanRequest, SimpanBatchJawabanRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        
        if (!$siswa) {
            return redirect()->route('dashboard');
        }

        // Cek dan tutup sesi yang expired yang berkaitan dengan siswa/rombel secara instan
        $now = now();
        $expiredSessions = SesiUjian::whereIn('status', [SesiStatus::MENUNGGU->value, SesiStatus::BERLANGSUNG->value])
            ->where(function ($q) use ($siswa) {
                $q->whereHas('pesertaUjian', fn($q2) => $q2->where('siswa_id', $siswa->id))
                  ->orWhere('rombel_id', $siswa->rombel_id);
            })
            ->get()
            ->filter(function ($session) use ($now) {
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
                    \Illuminate\Support\Facades\Log::error("Index check close failed for peserta {$peserta->id}: " . $e->getMessage());
                }
            }
        }
        
        // Ambil sesi aktif (yang benar-benar masih aktif setelah pembersihan di atas)
        $sesiAktif = SesiUjian::with('paketUjian.mataPelajaran')
            ->where(function ($q) use ($siswa) {
                $q->whereHas('pesertaUjian', fn($q2) => $q2->where('siswa_id', $siswa->id))
                  ->orWhere('rombel_id', $siswa->rombel_id);
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
            'sesi_aktif'   => $sesiAktif,
            'riwayat'      => $riwayat,
            'peserta_saya' => $pesertaSaya,
        ]);
    }

    // GET /ujian/{sesi}/masuk — halaman form token & verifikasi
    public function masuk(SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $siswa = request()->user()->siswa;
         
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
            'sesi'    => $sesi->load('paketUjian.mataPelajaran'),
            'peserta' => $peserta->load(['sesiUjian.paketUjian.mataPelajaran'])
        ]);
    }

    // POST /ujian/{sesi}/mulai — validasi token & mulai ujian
    public function mulai(MulaiUjianRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        if ($request->token !== $sesi->token) {
            return back()->withErrors(['token' => 'Token ujian tidak valid.']);
        }

        if ($sesi->status !== SesiStatus::BERLANGSUNG->value) {
            return back()->withErrors(['token' => 'Sesi ujian ini belum dimulai atau sudah ditutup oleh Proktor.']);
        }

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', request()->user()->siswa->id)
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

        $peserta = PesertaUjian::with(['jawabanSiswa', 'sesiUjian.paketUjian'])
                               ->where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', request()->user()->siswa->id)
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
        $cacheKey = 'ujian_soal_' . $peserta->id;
        $soalList = Cache::remember($cacheKey, 3600, function () use ($peserta) {
            return $this->ujianService->getSoalUntukSiswa($peserta);
        });

        return Inertia::render('Ujian/Ruang', [
            'sesi'        => $sesi->load('paketUjian.mataPelajaran'),
            'peserta'     => $peserta,
            'soal_list'   => $soalList,
            'sisa_waktu'  => $peserta->sisa_waktu
        ]);
    }

    private function cachedPeserta(SesiUjian $sesi): PesertaUjian
    {
        return Cache::remember('peserta_ujian_' . $sesi->id . '_' . auth()->id(), 60, function () use ($sesi) {
            return PesertaUjian::where('sesi_ujian_id', $sesi->id)
                ->where('siswa_id', auth()->user()->siswa->id)
                ->firstOrFail();
        });
    }

    // POST /ujian/{sesi}/simpan-batch — simpan banyak jawaban dalam 1 request
    public function simpanBatch(SimpanBatchJawabanRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = $this->cachedPeserta($sesi);

        if ($this->checkAndCloseIfExpired($sesi, $peserta, $request)) {
            return response()->json(['message' => 'Waktu habis atau ujian sudah selesai'], 403);
        }

        $answers = $request->input('answers', []);

        DB::transaction(function () use ($answers, $peserta) {
            foreach ($answers as $item) {
                $jawaban = JawabanSiswa::firstOrNew([
                    'peserta_ujian_id' => $peserta->id,
                    'soal_id'          => $item['soal_id'],
                ]);

                if (is_array($item['jawaban'])) {
                    $jawaban->jawaban_menjodohkan = $item['jawaban'];
                } else {
                    $jawaban->jawaban = $item['jawaban'];
                }

                $jawaban->durasi_detik = ($jawaban->durasi_detik ?? 0) + ($item['durasi'] ?? 0);
                $jawaban->dijawab_at   = now();
                $jawaban->is_ragu      = $item['is_ragu'] ?? false;

                if ($jawaban->isDirty()) {
                    $jawaban->save();
                }
            }
        });

        return response()->json(['status' => 'success', 'count' => count($answers)]);
    }

    // POST /ujian/{sesi}/simpan — endpoint auto-save jawaban (simpan langsung)
    public function simpan(SimpanJawabanRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = $this->cachedPeserta($sesi);

        if ($this->checkAndCloseIfExpired($sesi, $peserta, $request)) {
            return response()->json(['message' => 'Waktu habis atau ujian sudah selesai'], 403);
        }

        // Throttling: maksimal 1 simpan per 5 detik per soal
        $lastSaveKey = 'ujian_last_save_' . $peserta->id . '_' . $request->soal_id;
        $lastSave = Cache::get($lastSaveKey);

        if ($lastSave && (now()->getTimestamp() - $lastSave) < 5) {
            return response()->json(['status' => 'throttled']);
        }

        Cache::put($lastSaveKey, now()->getTimestamp(), 5);

        // Simpan langsung ke database (ringan, index UNIQUE sudah ada)
        $jawaban = JawabanSiswa::firstOrNew([
            'peserta_ujian_id' => $peserta->id,
            'soal_id'          => $request->soal_id,
        ]);

        if (is_array($request->jawaban)) {
            $jawaban->jawaban_menjodohkan = $request->jawaban;
        } else {
            $jawaban->jawaban = $request->jawaban;
        }

        $jawaban->durasi_detik = ($jawaban->durasi_detik ?? 0) + $request->durasi;
        $jawaban->dijawab_at   = now();
        $jawaban->is_ragu      = $request->is_ragu ?? false;
        $jawaban->save();

        return response()->json(['status' => 'success']);
    }

    // POST /ujian/{sesi}/pelanggaran — catat pelanggaran secara langsung (synchronous)
    public function pelanggaran(Request $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', $request->user()->siswa->id)
                               ->firstOrFail();

        // Jika sudah selesai atau didiskualifikasi, jangan catat lagi
        if (in_array($peserta->status, [PesertaStatus::SELESAI->value, PesertaStatus::DIDISKUALIFIKASI->value])) {
            return response()->json([
                'status'             => $peserta->status,
                'jumlah_pelanggaran' => $peserta->jumlah_pelanggaran,
            ]);
        }

        $tipe = $request->input('tipe', 'pindah_tab');

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
            'status'             => $peserta->status,
            'jumlah_pelanggaran' => $peserta->jumlah_pelanggaran,
        ]);
    }

    // POST /ujian/{sesi}/selesai — submit manual oleh siswa
    public function selesai(Request $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', $request->user()->siswa->id)
                               ->firstOrFail();

        // Hapus cache soal agar data tidak basi
        Cache::forget('ujian_soal_' . $peserta->id);

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

        $peserta = PesertaUjian::with(['jawabanSiswa', 'sesiUjian.paketUjian'])
                               ->where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', request()->user()->siswa->id)
                               ->firstOrFail();

        return Inertia::render('Ujian/Hasil', [
            'sesi'    => $sesi->load('paketUjian.mataPelajaran'),
            'peserta' => $peserta
        ]);
    }

    // Helper untuk mengecek dan menutup sesi ujian jika batas waktu selesai + toleransi sudah lewat
    private function checkAndCloseIfExpired(SesiUjian $sesi, PesertaUjian $peserta, Request $request): bool
    {
        $toleransiDetik = ($sesi->toleransi_menit ?? 0) * 60;
        $batasWaktu = $sesi->waktu_selesai?->copy()->addSeconds($toleransiDetik);
        
        if ($batasWaktu && $batasWaktu->isPast()) {
            if ($peserta->status === PesertaStatus::MENGERJAKAN->value) {
                // Hapus cache soal agar data tidak basi
                Cache::forget('ujian_soal_' . $peserta->id);
                
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
