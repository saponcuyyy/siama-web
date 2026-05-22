<?php
namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\{SesiUjian, PesertaUjian, JawabanSiswa};
use App\Services\Ujian\UjianService;
use App\Http\Requests\Ujian\{MulaiUjianRequest, SimpanJawabanRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RuangUjianController extends Controller
{
    public function __construct(
        private UjianService $ujianService
    ) {}

    // GET /ujian — daftar ujian aktif untuk siswa
    public function index(Request $request)
    {
        $siswa = $request->user()->siswa;
        
        if (!$siswa) {
            abort(403, 'Anda bukan siswa dan tidak memiliki akses ke halaman ujian ini.');
        }
        
        // Sesi aktif: siswa terdaftar peserta ATAU rombel siswa ditugaskan ke sesi ini
        $sesiAktif = SesiUjian::with('paketUjian.mataPelajaran')
            ->where(function ($q) use ($siswa) {
                $q->whereHas('pesertaUjian', fn($q2) => $q2->where('siswa_id', $siswa->id))
                  ->orWhere('rombel_id', $siswa->rombel_id);
            })
            ->whereIn('status', ['menunggu', 'berlangsung'])
            ->latest()
            ->get();
            
        $riwayat = PesertaUjian::with(['sesiUjian.paketUjian'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status', ['selesai', 'didiskualifikasi'])
            ->latest()
            ->get();

        return Inertia::render('Ujian/Index', [
            'sesi_aktif' => $sesiAktif,
            'riwayat'    => $riwayat
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
            ['status' => 'belum_mulai']
        );

        // Jika sudah selesai atau diskualifikasi, redirect ke hasil
        if (in_array($peserta->status, ['selesai', 'didiskualifikasi'])) {
            return redirect()->route('ujian.hasil', $sesi);
        }

        return Inertia::render('Ujian/Masuk', [
            'sesi'    => $sesi->load('paketUjian.mataPelajaran'),
            'peserta' => $peserta
        ]);
    }

    // POST /ujian/{sesi}/mulai — validasi token & mulai ujian
    public function mulai(MulaiUjianRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        if ($request->token !== $sesi->token) {
            return back()->withErrors(['token' => 'Token ujian tidak valid.']);
        }

        if ($sesi->status !== 'berlangsung') {
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

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', request()->user()->siswa->id)
                               ->firstOrFail();

        if (in_array($peserta->status, ['selesai', 'didiskualifikasi'])) {
            return redirect()->route('ujian.hasil', $sesi);
        }

        if ($peserta->status !== 'mengerjakan') {
            return redirect()->route('ujian.masuk', $sesi);
        }

        return Inertia::render('Ujian/Ruang', [
            'sesi'        => $sesi->load('paketUjian.mataPelajaran'),
            'peserta'     => $peserta,
            'soal_list'   => $this->ujianService->getSoalUntukSiswa($peserta),
            'sisa_waktu'  => $peserta->sisa_waktu
        ]);
    }

    // POST /ujian/{sesi}/simpan — endpoint auto-save jawaban
    public function simpan(SimpanJawabanRequest $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', request()->user()->siswa->id)
                               ->firstOrFail();

        if ($peserta->status !== 'mengerjakan' || $peserta->sisa_waktu <= 0) {
            return response()->json(['message' => 'Waktu habis atau ujian sudah selesai'], 403);
        }

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
        $jawaban->save();

        return response()->json(['status' => 'success']);
    }

    // POST /ujian/{sesi}/pelanggaran — log tab change/blur
    public function pelanggaran(Request $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);
        
        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', $request->user()->siswa->id)
                               ->firstOrFail();

        $tipe = $request->input('tipe', 'pindah_tab'); // default
        
        $this->ujianService->catatPelanggaran(
            $peserta, 
            $tipe, 
            $request->ip(), 
            $request->userAgent()
        );

        return response()->json([
            'status' => $peserta->status,
            'jumlah_pelanggaran' => $peserta->jumlah_pelanggaran
        ]);
    }

    // POST /ujian/{sesi}/selesai — submit manual oleh siswa
    public function selesai(Request $request, SesiUjian $sesi)
    {
        Gate::authorize('ikutUjian', $sesi);

        $peserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', $request->user()->siswa->id)
                               ->firstOrFail();

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

        $peserta = PesertaUjian::with('jawabanSiswa')
                               ->where('sesi_ujian_id', $sesi->id)
                               ->where('siswa_id', request()->user()->siswa->id)
                               ->firstOrFail();

        return Inertia::render('Ujian/Hasil', [
            'sesi'    => $sesi->load('paketUjian.mataPelajaran'),
            'peserta' => $peserta
        ]);
    }
}
