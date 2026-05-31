<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PesertaStatus;
use App\Enums\SesiStatus;
use App\Enums\SoalType;
use App\Http\Controllers\Controller;
use App\Models\PaketUjian;
use App\Models\PesertaUjian;
use App\Models\Rombel;
use App\Models\SesiUjian;
use App\Models\Siswa;
use App\Models\Soal;
use App\Services\Ujian\UjianService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SesiUjianController extends Controller
{
    private function guruMapelIds(): array
    {
        $user = Auth::user();
        if (! $user->hasRole('guru')) {
            return [];
        }

        return $user->guru?->mataPelajarans()->pluck('mata_pelajaran.id')->toArray() ?? [];
    }

    public function index(Request $request)
    {
        $guruMapelIds = $this->guruMapelIds();

        // JIT update status sesi yang expired secara global/admin
        $now = now();
        $expiredSessions = SesiUjian::whereIn('status', [SesiStatus::MENUNGGU->value, SesiStatus::BERLANGSUNG->value])
            ->get()
            ->filter(function ($session) {
                $toleransiDetik = ($session->toleransi_menit ?? 0) * 60;
                $batasWaktu = $session->waktu_selesai?->copy()->addSeconds($toleransiDetik);

                return $batasWaktu && $batasWaktu->isPast();
            });

        if ($expiredSessions->isNotEmpty()) {
            $ujianService = app(UjianService::class);
            foreach ($expiredSessions as $session) {
                $session->update(['status' => SesiStatus::SELESAI->value]);

                // Akhiri ujian untuk peserta yang masih status 'mengerjakan' di sesi ini
                $pesertas = PesertaUjian::where('sesi_ujian_id', $session->id)
                    ->where('status', PesertaStatus::MENGERJAKAN->value)
                    ->get();

                foreach ($pesertas as $peserta) {
                    try {
                        $ujianService->akhiriUjian($peserta, '127.0.0.1', 'System/JIT-Admin-Index');
                    } catch (\Throwable $e) {
                        Log::error("Admin index check close failed for peserta {$peserta->id}: ".$e->getMessage());
                    }
                }
            }
        }

        $query = SesiUjian::with(['paketUjian.mataPelajaran', 'rombel', 'rombels', 'dibuatOleh'])
            ->latest();

        if ($guruMapelIds) {
            $query->whereHas('paketUjian', fn ($q) => $q->whereIn('mata_pelajaran_id', $guruMapelIds));
        }

        if ($request->search) {
            $query->where('nama_sesi', 'like', '%'.$request->search.'%');
        }

        $paketList = PaketUjian::select('id', 'nama', 'kode');
        if ($guruMapelIds) {
            $paketList->whereIn('mata_pelajaran_id', $guruMapelIds);
        }

        return Inertia::render('Admin/Ujian/Sesi/Index', [
            'sesiList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
            'paketList' => $paketList->get(),
            'rombelList' => Rombel::select('id', 'nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paket_ujian_id' => 'required|exists:paket_ujian,id',
            'rombel_ids' => 'nullable|array',
            'rombel_ids.*' => 'exists:rombel,id',
            'nama_sesi' => 'required|string|max:255',
            'waktu_mulai' => 'required|date|after_or_equal:now',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'toleransi_menit' => 'required|integer|min:0',
            'max_pelanggaran' => 'required|integer|min:1',
            'wajib_fullscreen' => 'boolean',
            'catatan' => 'nullable|string',
        ]);

        $validated['token'] = strtoupper(Str::random(8));
        $validated['dibuat_oleh'] = Auth::id();
        $validated['status'] = SesiStatus::MENUNGGU->value;

        $sesi = SesiUjian::create($validated);

        if ($request->rombel_ids) {
            $sesi->rombels()->sync($request->rombel_ids);
        }

        return back()->with('success', 'Sesi ujian berhasil ditambahkan.');
    }

    public function toggleStatus(Request $request, SesiUjian $sesi)
    {
        $request->validate(['status' => 'required|in:menunggu,berlangsung,selesai,dibatalkan']);
        $newStatus = $request->status;
        $sesi->update(['status' => $newStatus]);

        // Auto-generate peserta dari rombel saat sesi mulai berlangsung
        if ($newStatus === SesiStatus::BERLANGSUNG->value) {
            $this->generatePesertaFromRombel($sesi);
        }

        return back()->with('success', "Status sesi berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Tambah satu siswa sebagai peserta sesi.
     */
    public function tambahPeserta(Request $request, SesiUjian $sesi)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        $exists = PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        if ($exists) {
            return back()->with('warning', "Siswa {$siswa->nama} sudah terdaftar sebagai peserta.");
        }

        PesertaUjian::create([
            'sesi_ujian_id' => $sesi->id,
            'siswa_id' => $siswa->id,
            'status' => 'belum_mulai',
        ]);

        return back()->with('success', "Siswa {$siswa->nama} berhasil ditambahkan sebagai peserta.");
    }

    /**
     * Endpoint manual untuk generate/sync peserta dari rombel.
     */
    public function generatePeserta(SesiUjian $sesi)
    {
        $jumlah = $this->generatePesertaFromRombel($sesi);

        if ($jumlah === 0 && ! $this->getRombelIds($sesi)) {
            return back()->with('error', 'Sesi ini tidak memiliki rombel yang ditugaskan.');
        }

        return back()->with('success', "{$jumlah} siswa berhasil didaftarkan sebagai peserta.");
    }

    /**
     * Generate peserta_ujian untuk semua siswa di rombel sesi ini.
     * Sudah ada tidak akan di-duplicate (firstOrCreate).
     */
    private function generatePesertaFromRombel(SesiUjian $sesi): int
    {
        $rombelIds = $this->getRombelIds($sesi);
        if (empty($rombelIds)) {
            return 0;
        }

        $siswaIds = Siswa::whereIn('rombel_id', $rombelIds)->pluck('id');
        $existing = PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->whereIn('siswa_id', $siswaIds)
            ->pluck('siswa_id');

        $newIds = $siswaIds->diff($existing);
        $now = now();

        $newPeserta = $newIds->map(fn ($id) => [
            'sesi_ujian_id' => $sesi->id,
            'siswa_id' => $id,
            'status' => 'belum_mulai',
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        if (! empty($newPeserta)) {
            PesertaUjian::insert($newPeserta);
        }

        return $newIds->count();
    }

    private function getRombelIds(SesiUjian $sesi): array
    {
        $rombelIds = [];

        $sesi->loadMissing('rombels');
        $rombels = $sesi->rombels;

        if ($rombels->isNotEmpty()) {
            $rombelIds = $rombels->pluck('id')->toArray();
        } elseif ($sesi->rombel_id) {
            $rombelIds = [$sesi->rombel_id];
        }

        return $rombelIds;
    }

    public function monitor(Request $request, SesiUjian $sesi)
    {
        // JIT check & update untuk spesifik sesi yang sedang di-monitor
        $now = now();
        $toleransiDetik = ($sesi->toleransi_menit ?? 0) * 60;
        $batasWaktu = $sesi->waktu_selesai?->copy()->addSeconds($toleransiDetik);
        if ($batasWaktu && $batasWaktu->isPast() && in_array($sesi->status, [SesiStatus::MENUNGGU->value, SesiStatus::BERLANGSUNG->value])) {
            $sesi->update(['status' => SesiStatus::SELESAI->value]);

            $ujianService = app(UjianService::class);
            $pesertas = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                ->where('status', PesertaStatus::MENGERJAKAN->value)
                ->get();

            foreach ($pesertas as $peserta) {
                try {
                    $ujianService->akhiriUjian($peserta, '127.0.0.1', 'System/JIT-Admin-Monitor');
                } catch (\Throwable $e) {
                    Log::error("Admin monitor check close failed for peserta {$peserta->id}: ".$e->getMessage());
                }
            }
            $sesi->refresh();
        }

        $sesi->load(['paketUjian.mataPelajaran', 'rombel', 'rombels']);

        $perPage = (int) $request->input('per_page', 10);

        $peserta = PesertaUjian::with('siswa')
            ->withCount(['jawabanSiswa as jawaban_terisi' => function ($q) {
                $q->whereNotNull('jawaban')->orWhereNotNull('jawaban_menjodohkan');
            }])
            ->where('sesi_ujian_id', $sesi->id)
            ->paginate($perPage)
            ->through(function ($p) {
                $total = count($p->urutan_soal ?? []);
                $p->progress = $total > 0 ? round(($p->jawaban_terisi / $total) * 100) : 0;
                $p->jawaban_tersimpan = $p->jawaban_terisi;
                $p->total_soal = $total;

                return $p;
            });

        $maxScore = Cache::remember('max_score_paket_'.$sesi->paket_ujian_id, 60, function () use ($sesi) {
            return $sesi->paketUjian->soal()->sum('bobot') ?: 100;
        });

        $stats = PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CAST(status = ? AS UNSIGNED)) as mengerjakan', [PesertaStatus::MENGERJAKAN->value])
            ->selectRaw('SUM(CAST(status = ? AS UNSIGNED)) as selesai', [PesertaStatus::SELESAI->value])
            ->selectRaw('SUM(CAST(status = ? AS UNSIGNED)) as didiskualifikasi', [PesertaStatus::DIDISKUALIFIKASI->value])
            ->selectRaw('ROUND(AVG(CASE WHEN status = ? AND nilai_akhir IS NOT NULL THEN nilai_akhir END), 2) as rata_nilai', [PesertaStatus::SELESAI->value])
            ->first();

        return Inertia::render('Admin/Ujian/Sesi/Monitor', [
            'sesi' => $sesi,
            'peserta' => $peserta,
            'stats' => $stats,
            'maxScore' => $maxScore,
        ]);
    }

    public function detailPeserta(SesiUjian $sesi, PesertaUjian $peserta)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'paketUjian.soal' => function ($q) {
            $q->orderBy('paket_soal.urutan');
        }, 'rombel', 'rombels']);

        $peserta->load('siswa.rombel');
        $peserta->loadMissing('jawabanSiswa');

        $soalIds = $sesi->paketUjian->soal->pluck('id');
        $jawabanBySoal = $peserta->jawabanSiswa->keyBy('soal_id');

        $soalList = Soal::with(['pilihanJawaban' => fn ($q) => $q->orderBy('urutan'), 'pasanganMenjodohkan' => fn ($q) => $q->orderBy('urutan')])
            ->whereIn('id', $soalIds)
            ->get()
            ->keyBy('id')
            ->each(fn ($s) => $s->makeVisible(['kunci_jawaban', 'pembahasan']));

        $jawabanList = [];
        $nomor = 1;

        foreach ($sesi->paketUjian->soal as $soalPivot) {
            $soal = $soalList[$soalPivot->id] ?? null;
            if (! $soal) {
                continue;
            }

            $jawaban = $jawabanBySoal[$soal->id] ?? null;

            $item = [
                'nomor' => $nomor++,
                'soal_id' => $soal->id,
                'tipe' => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'bobot' => $soal->bobot,
                'gambar_url' => $soal->gambar_url,
                'kunci_jawaban' => $soal->kunci_jawaban,
                'pembahasan' => $soal->pembahasan,
                'jawaban' => $jawaban?->jawaban,
                'jawaban_menjodohkan' => $jawaban?->jawaban_menjodohkan,
                'is_benar' => $jawaban?->is_benar,
                'nilai' => $jawaban?->nilai,
                'skor' => $jawaban?->skor,
                'is_ragu' => $jawaban?->is_ragu,
                'dijawab_at' => $jawaban?->dijawab_at,
                'durasi_detik' => $jawaban?->durasi_detik,
                'catatan_guru' => $jawaban?->catatan_guru,
            ];

            if ($soal->tipe === SoalType::PG->value) {
                $item['pilihan_jawaban'] = $soal->pilihanJawaban->map(fn ($p) => [
                    'kode' => $p->kode,
                    'teks' => $p->teks,
                    'gambar_url' => $p->gambar_url,
                    'is_benar' => $p->is_benar,
                ]);
            } elseif ($soal->tipe === SoalType::MENJODOHKAN->value) {
                $item['pasangan_menjodohkan'] = $soal->pasanganMenjodohkan->map(fn ($p) => [
                    'kiri' => $p->kiri,
                    'kanan' => $p->kanan,
                ]);
            }

            $jawabanList[] = $item;
        }

        return Inertia::render('Admin/Ujian/Sesi/PesertaDetail', [
            'sesi' => $sesi,
            'peserta' => $peserta,
            'jawabanList' => $jawabanList,
            'maxScore' => $sesi->paketUjian->soal->sum('bobot') ?: 100,
        ]);
    }

    public function kartuUjian(SesiUjian $sesi)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel', 'rombels']);

        $rombelIds = $this->getRombelIds($sesi);

        $peserta = PesertaUjian::with('siswa.rombel')
            ->where('sesi_ujian_id', $sesi->id)
            ->whereHas('siswa', fn ($q) => $q->whereIn('rombel_id', $rombelIds))
            ->orderBy(
                PesertaUjian::select('nama')
                    ->from('siswa')
                    ->whereColumn('siswa.id', 'peserta_ujian.siswa_id')
                    ->limit(1)
            )
            ->get();

        $pdf = Pdf::loadView('exports.kartu-ujian', compact('sesi', 'peserta'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('kartu-ujian-'.$sesi->token.'.pdf');
    }
}
