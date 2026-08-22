<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\MataPelajaran;
use App\Models\Rombel;
use App\Models\Setting;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class JadwalController extends Controller
{
    public const HARI_ALL = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /**
     * Kebijakan standar kapasitas JP per hari.
     * Senin-Kamis penuh 10 JP, Jumat hari pendek maksimal 6 JP.
     * Hari di luar daftar (mis. Sabtu) mengikuti nilai "max_jam" umum.
     */
    public const MAX_JAM_PER_HARI_DEFAULT = [
        'Senin'  => 10,
        'Selasa' => 10,
        'Rabu'   => 10,
        'Kamis'  => 10,
        'Jumat'  => 6,
    ];

    private function getHariAktif(): array
    {
        $setting = Setting::get('hari_aktif_sekolah');
        if ($setting) {
            $days = is_array($setting) ? $setting : json_decode($setting, true);
            if (is_array($days) && !empty($days)) {
                return array_values(array_intersect(self::HARI_ALL, $days));
            }
        }
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    }

    public function index(Request $request)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $tahunAjaranId = $request->tahun_ajaran_id ?? $tahunAjaranAktif?->id;
        $rombelId = $request->rombel_id;

        $jadwalQuery = Jadwal::with(['rombel', 'mataPelajaran', 'guru', 'tahunAjaran'])
            ->where('tahun_ajaran_id', $tahunAjaranId);

        if ($rombelId) {
            $jadwalQuery->where('rombel_id', $rombelId);
        }

        $jadwal = $jadwalQuery->orderBy('hari')->orderBy('jam_ke')->get();

        $grouped = [];
        $hariUrutan = $this->getHariAktif();
        $maxJam = $jadwal->max('jam_ke') ?? 10;

        if ($rombelId) {
            $grid = [];
            foreach ($hariUrutan as $hari) {
                $grid[$hari] = [];
                for ($j = 1; $j <= $maxJam; $j++) {
                    $entry = $jadwal->firstWhere(fn($item) => $item->hari === $hari && $item->jam_ke === $j);
                    $grid[$hari][$j] = $entry;
                }
            }
            $grouped = [
                'rombel' => Rombel::find($rombelId),
                'grid' => $grid,
                'max_jam' => $maxJam,
            ];
        } else {
            $rombels = Rombel::where('tahun_ajaran_id', $tahunAjaranId)
                ->orderBy('tingkat')->orderBy('nama')->get();

            foreach ($rombels as $rombel) {
                $jadwalRombel = $jadwal->where('rombel_id', $rombel->id);
                $grid = [];
                foreach ($hariUrutan as $hari) {
                    $grid[$hari] = [];
                    for ($j = 1; $j <= $maxJam; $j++) {
                        $entry = $jadwalRombel->firstWhere(fn($item) => $item->hari === $hari && $item->jam_ke === $j);
                        $grid[$hari][$j] = $entry;
                    }
                }
                $grouped[] = [
                    'rombel' => $rombel,
                    'grid' => $grid,
                    'max_jam' => $maxJam,
                ];
            }
        }

        return Inertia::render('Admin/Akademik/Jadwal/Index', [
            'jadwalGrouped' => $grouped,
            'rombels' => Rombel::select('id', 'nama', 'tingkat', 'tahun_ajaran_id')
                ->orderBy('tingkat')->orderBy('nama')->get(),
            'guruList' => Guru::select('id', 'nama', 'nip')->orderBy('nama')->get(),
            'mapelList' => MataPelajaran::select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'tahunAjaranList' => TahunAjaran::select('id', 'nama', 'is_active')
                ->orderByDesc('is_active')->orderByDesc('id')->get(),
            'filters' => $request->only(['rombel_id', 'tahun_ajaran_id']),
            'selectedRombelId' => $rombelId,
            'hariList' => $hariUrutan,
            'semuaHariList' => self::HARI_ALL,
            'maxJam' => $maxJam,
            'maxJamPerHariDefault' => self::MAX_JAM_PER_HARI_DEFAULT,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rombel_id' => 'required|exists:rombel,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'required|exists:guru,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'hari' => ['required', Rule::in($this->getHariAktif())],
            'jam_ke' => 'required|integer|min:1|max:20',
        ]);

        $rombelConflict = Jadwal::where([
            'rombel_id' => $validated['rombel_id'],
            'hari' => $validated['hari'],
            'jam_ke' => $validated['jam_ke'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
        ])->first();

        if ($rombelConflict) {
            return back()->with('error', 'Gagal: Rombel ini sudah memiliki mata pelajaran lain pada hari ' . $validated['hari'] . ' jam ke-' . $validated['jam_ke'] . '.');
        }

        $guruConflict = Jadwal::with('rombel')->where([
            'guru_id' => $validated['guru_id'],
            'hari' => $validated['hari'],
            'jam_ke' => $validated['jam_ke'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
        ])->first();

        if ($guruConflict) {
            $guruNama = Guru::find($validated['guru_id'])?->nama ?? 'Guru';
            $rombelNama = $guruConflict->rombel?->nama ?? 'lain';
            return back()->with('error', "Gagal: Guru {$guruNama} sudah ada jadwal mengajar di kelas {$rombelNama} pada hari {$validated['hari']} jam ke-{$validated['jam_ke']}.");
        }

        Jadwal::create($validated);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'required|exists:guru,id',
            'hari' => ['required', Rule::in($this->getHariAktif())],
            'jam_ke' => 'required|integer|min:1|max:20',
        ]);

        $rombelConflict = Jadwal::where([
            'rombel_id' => $jadwal->rombel_id,
            'hari' => $validated['hari'],
            'jam_ke' => $validated['jam_ke'],
            'tahun_ajaran_id' => $jadwal->tahun_ajaran_id,
        ])->where('id', '!=', $jadwal->id)->first();

        if ($rombelConflict) {
            return back()->with('error', 'Gagal: Rombel ini sudah memiliki mata pelajaran lain pada hari ' . $validated['hari'] . ' jam ke-' . $validated['jam_ke'] . '.');
        }

        $guruConflict = Jadwal::with('rombel')->where([
            'guru_id' => $validated['guru_id'],
            'hari' => $validated['hari'],
            'jam_ke' => $validated['jam_ke'],
            'tahun_ajaran_id' => $jadwal->tahun_ajaran_id,
        ])->where('id', '!=', $jadwal->id)->first();

        if ($guruConflict) {
            $guruNama = Guru::find($validated['guru_id'])?->nama ?? 'Guru';
            $rombelNama = $guruConflict->rombel?->nama ?? 'lain';
            return back()->with('error', "Gagal: Guru {$guruNama} sudah ada jadwal mengajar di kelas {$rombelNama} pada hari {$validated['hari']} jam ke-{$validated['jam_ke']}.");
        }

        $jadwal->update($validated);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    public function updateHariAktif(Request $request)
    {
        $validated = $request->validate([
            'hari' => 'required|array|min:1',
            'hari.*' => Rule::in(self::HARI_ALL),
        ]);

        $sortedDays = array_values(array_intersect(self::HARI_ALL, $validated['hari']));

        Setting::set('hari_aktif_sekolah', json_encode($sortedDays), 'akademik');

        return back()->with('success', 'Hari aktif sekolah berhasil diperbarui.');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran_id'    => 'required|exists:tahun_ajaran,id',
            'rombel_ids'         => 'nullable|array',
            'rombel_ids.*'       => 'exists:rombel,id',
            'max_jam'            => 'nullable|integer|min:1|max:20',
            'max_jam_tingkat'    => 'nullable|array',
            'max_jam_tingkat.X'  => 'nullable|integer|min:1|max:20',
            'max_jam_tingkat.XI' => 'nullable|integer|min:1|max:20',
            'max_jam_tingkat.XII'=> 'nullable|integer|min:1|max:20',
            'max_jam_hari'       => 'nullable|array',
            'max_jam_hari.*'     => 'nullable|integer|min:1|max:20',
        ]);

        $tahunAjaranId = $validated['tahun_ajaran_id'];
        $defaultMaxJam = $validated['max_jam'] ?? 8;
        $maxJamTingkat = [
            'X'   => (int)($validated['max_jam_tingkat']['X']   ?? $defaultMaxJam),
            'XI'  => (int)($validated['max_jam_tingkat']['XI']  ?? $defaultMaxJam),
            'XII' => (int)($validated['max_jam_tingkat']['XII'] ?? $defaultMaxJam),
        ];
        $hariList = $this->getHariAktif();

        // Kapasitas per hari: input manual > kebijakan default > max_jam umum.
        // Batas efektif tiap rombel = min(kapasitas hari, batas tingkat).
        $kapasitasHari = [];
        foreach ($hariList as $hari) {
            $kapasitasHari[$hari] = (int)(
                $validated['max_jam_hari'][$hari]
                ?? self::MAX_JAM_PER_HARI_DEFAULT[$hari]
                ?? $defaultMaxJam
            );
        }

        $rombels = Rombel::where('tahun_ajaran_id', $tahunAjaranId)
            ->when(isset($validated['rombel_ids']), fn($q) => $q->whereIn('id', $validated['rombel_ids']))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();

        if ($rombels->isEmpty()) {
            if (isset($validated['rombel_ids']) && count($validated['rombel_ids']) === 0) {
                return back()->with('error', 'Gagal: Pilih minimal satu rombel terlebih dahulu.');
            }

            $tahunAjaranNama = TahunAjaran::find($tahunAjaranId)?->nama ?? 'tersebut';
            return back()->with('error', "Gagal: Rombel yang dipilih tidak ada yang termasuk tahun ajaran {$tahunAjaranNama}. Pastikan tahun ajaran pada modal generate sesuai dengan tahun ajaran rombel.");
        }

        Jadwal::whereIn('rombel_id', $rombels->pluck('id'))
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->delete();

        $subjects = MataPelajaran::with(['gurus' => fn($q) => $q->select('guru.id', 'guru.nama')])->get();

        // Initialize teacher schedule from existing schedules (e.g. for rombels not being re-generated)
        $existingJadwals = Jadwal::where('tahun_ajaran_id', $tahunAjaranId)->get();
        $teacherSchedule = [];
        foreach ($existingJadwals as $j) {
            $teacherSchedule[$j->guru_id][$j->hari][$j->jam_ke] = true;
        }

        $created = 0;
        $errors  = [];

        $konteksList = [];
        foreach ($rombels as $rombel) {
            $maxJam = $maxJamTingkat[$rombel->tingkat] ?? $defaultMaxJam;

            // Batas JP efektif per hari untuk rombel ini
            $limitPerHari = [];
            foreach ($hariList as $hari) {
                $limitPerHari[$hari] = min($kapasitasHari[$hari], $maxJam);
            }

            $matchingSubjects = $subjects->filter(function ($s) use ($rombel) {
                if ($s->tingkat !== $rombel->tingkat) return false;
                if ($s->jurusan && $s->jurusan !== $rombel->jurusan) return false;
                return $s->jam_per_minggu > 0 && $s->gurus->isNotEmpty();
            })->values();

            if ($matchingSubjects->isEmpty()) {
                $errors[] = "{$rombel->nama}: tidak ada mata pelajaran cocok. Pastikan mapel tingkat {$rombel->tingkat} memiliki guru pengampu dengan jumlah jam di halaman Data Guru.";
                continue;
            }

            $totalJam       = $matchingSubjects->sum('jam_per_minggu');
            $availableSlots = array_sum($limitPerHari);

            if ($totalJam > $availableSlots) {
                $detailKapasitas = implode(', ', array_map(
                    fn($hari, $limit) => "{$hari}: {$limit} JP",
                    array_keys($limitPerHari),
                    $limitPerHari
                ));
                $kurang = $totalJam - $availableSlots;
                $errors[] = "{$rombel->nama}: total jam ({$totalJam}) melebihi kapasitas mingguan ({$availableSlots} slot; {$detailKapasitas}). Tambah {$kurang} slot melalui opsi \"Kapasitas Jam Pelajaran per Hari\" pada modal Generate.";
                continue;
            }

            // ── 1. Pecah semua mapel menjadi blok 2-jam dan sisa 1-jam ──
            // Semua guru pengampu ikut disimpan sebagai kandidat agar solver
            // dapat memilih guru lain yang tidak bentrok di hari & jam tersebut.
            $pool = [];
            foreach ($matchingSubjects as $subject) {
                $gurus = $subject->gurus->unique('id')->values();
                $hours = $subject->jam_per_minggu;
                while ($hours >= 2) {
                    $pool[] = ['s' => $subject, 'gurus' => $gurus, 'dur' => 2];
                    $hours -= 2;
                }
                if ($hours === 1) {
                    $pool[] = ['s' => $subject, 'gurus' => $gurus, 'dur' => 1];
                }
            }
            // Prioritaskan blok 2 jam agar terisi teratur
            usort($pool, fn($a, $b) => [$b['dur'], $a['s']->id] <=> [$a['dur'], $b['s']->id]);

            $konteksList[] = [
                'rombel'       => $rombel,
                'pool'         => $pool,
                'limitPerHari' => $limitPerHari,
                'totalJam'     => $totalJam,
            ];
        }

        // Rombel dengan beban terbesar diproses lebih dulu agar tidak kehabisan
        // kombinasi slot guru oleh rombel ringan yang diproses sebelumnya.
        usort($konteksList, fn($a, $b) => $b['totalJam'] <=> $a['totalJam']);

        $strategi = [
            ['sameDay' => false, 'min' => 4],
            ['sameDay' => true,  'min' => 4],
            ['sameDay' => false, 'min' => 1],
            ['sameDay' => true,  'min' => 1],
        ];

        $berhasil    = [];
        $daftarGagal = [];

        // Batas waktu total untuk seluruh proses pencarian solusi (solver
        // backtracking bersifat eksponensial). Setelah lewat, solver gagal cepat
        // dan rombel yang tersisa ditangani fase best-effort yang linear.
        $deadline = microtime(true) + 15.0;

        // ── Fase 1: solver penuh per rombel ──
        foreach ($konteksList as $ctx) {
            $assign = null;
            foreach ($strategi as $s) {
                $assign = $this->cobaSelesaikan($ctx['pool'], $hariList, $ctx['limitPerHari'], $ctx['totalJam'], $teacherSchedule, $s['sameDay'], $s['min'], $deadline);
                if ($assign !== null) break;
            }

            if ($assign !== null) {
                $berhasil[$ctx['rombel']->id] = ['ctx' => $ctx, 'assignments' => $assign];
            } else {
                $daftarGagal[] = $ctx;
            }
        }

        // ── Fase 2: percobaan ulang dengan urutan acak (tetap tanpa bentrok guru) ──
        $masihGagal = [];
        foreach ($daftarGagal as $ctx) {
            $assign = null;

            if (microtime(true) >= $deadline) {
                $masihGagal[] = $ctx;
                continue;
            }

            foreach ([1, 2, 3, 4, 5] as $seed) {
                foreach ($strategi as $s) {
                    $assign = $this->cobaSelesaikan($ctx['pool'], $hariList, $ctx['limitPerHari'], $ctx['totalJam'], $teacherSchedule, $s['sameDay'], $s['min'], $deadline, $seed);
                    if ($assign !== null) break 2;
                }
            }

            if ($assign !== null) {
                $berhasil[$ctx['rombel']->id] = ['ctx' => $ctx, 'assignments' => $assign];
            } else {
                $masihGagal[] = $ctx;
            }
        }

        // ── Fase 3: best-effort — tempatkan sebanyak mungkin blok tanpa bentrok ──
        foreach ($masihGagal as $ctx) {
            [$placements, $blokSisa] = $this->isiSisaSecaraRakus($ctx['pool'], $hariList, $ctx['limitPerHari'], $teacherSchedule);

            if (!empty($placements)) {
                $berhasil[$ctx['rombel']->id] = ['ctx' => $ctx, 'assignments' => $placements];
            }

            if (!empty($blokSisa)) {
                $perMapel = [];
                foreach ($blokSisa as $blk) {
                    $perMapel[$blk['s']->nama] = ($perMapel[$blk['s']->nama] ?? 0) + $blk['dur'];
                }
                $totalSisa = array_sum($perMapel);
                $detail    = implode(', ', array_map(fn($n, $jp) => "{$n} {$jp} JP", array_keys($perMapel), $perMapel));
                $errors[]  = "{$ctx['rombel']->nama}: {$totalSisa} JP tidak dapat dijadwalkan tanpa bentrok guru ({$detail}).";
            }
        }

        // Simpan entri jadwal hasil seluruh fase
        foreach ($berhasil as $entry) {
            $ctx = $entry['ctx'];
            foreach ($entry['assignments'] as $blockIdx => $assign) {
                $block = $ctx['pool'][$blockIdx];
                for ($k = 0; $k < $block['dur']; $k++) {
                    Jadwal::create([
                        'rombel_id'         => $ctx['rombel']->id,
                        'mata_pelajaran_id' => $block['s']->id,
                        'guru_id'           => $assign['guru_id'],
                        'tahun_ajaran_id'   => $tahunAjaranId,
                        'hari'              => $assign['hari'],
                        'jam_ke'            => $assign['start'] + $k,
                    ]);
                    $created++;
                }
            }
        }

        $message = "Jadwal berhasil digenerate: {$created} entri jadwal dibuat.";
        if (!empty($errors)) $message .= ' ' . implode(' ', $errors);
        return back()->with('success', $message);
    }

    /**
     * Coba selesaikan satu rombel dengan backtracking penuh.
     * Mengembalikan assignments [blockIdx => hari/start/guru_id] saat berhasil,
     * atau null saat gagal / melebihi $deadline (state guruSchedule dikembalikan seperti semula).
     */
    private function cobaSelesaikan(
        array $pool,
        array $hariList,
        array $limitPerHari,
        int $totalJam,
        array &$guruSchedule,
        bool $allowSameDaySubject,
        int $minHours,
        float $deadline,
        ?int $seed = null
    ): ?array {
        $urutanHari = $hariList;

        if ($seed !== null) {
            mt_srand($seed * 7919 + $totalJam + array_sum($limitPerHari));
            shuffle($urutanHari);
            foreach ($pool as $i => $blk) {
                $pool[$i]['_acak'] = mt_rand();
            }
            usort($pool, fn($a, $b) => [$b['dur'], $a['_acak']] <=> [$a['dur'], $b['_acak']]);
        }

        $dayDurations = array_fill_keys($hariList, 0);
        $subjectDays  = [];
        $subjectGuru  = [];
        $assignments  = [];
        $nodes        = 0;
        $timedOut     = false;

        $solve = function ($blockIdx) use (
            &$solve, $pool, $urutanHari, $limitPerHari, $totalJam,
            &$guruSchedule, &$dayDurations, &$subjectDays, &$subjectGuru, &$assignments,
            $allowSameDaySubject, $minHours, $deadline, &$nodes, &$timedOut
        ) {
            if ($timedOut) {
                return false;
            }
            if ((++$nodes & 255) === 0 && microtime(true) >= $deadline) {
                $timedOut = true;
                return false;
            }
            if ($blockIdx >= count($pool)) {
                foreach ($urutanHari as $hari) {
                    $dur = $dayDurations[$hari] ?? 0;
                    if ($totalJam >= count($urutanHari) && $dur === 0) {
                        return false;
                    }
                    $dayMin = min($minHours, $limitPerHari[$hari]);
                    if ($dur > 0 && $dur < $dayMin) {
                        return false;
                    }
                }
                return true;
            }

            $block     = $pool[$blockIdx];
            $subjectId = $block['s']->id;
            $dur       = $block['dur'];

            $candidates = isset($subjectGuru[$subjectId])
                ? [$subjectGuru[$subjectId]]
                : $block['gurus']->all();

            foreach ($urutanHari as $hari) {
                $start = ($dayDurations[$hari] ?? 0) + 1;
                $end   = $start + $dur - 1;

                if ($end > $limitPerHari[$hari]) {
                    continue;
                }

                if (!$allowSameDaySubject && isset($subjectDays[$subjectId][$hari])) {
                    continue;
                }

                foreach ($candidates as $guru) {
                    $guruId = $guru->id;

                    $clash = false;
                    for ($jam = $start; $jam <= $end; $jam++) {
                        if (isset($guruSchedule[$guruId][$hari][$jam])) {
                            $clash = true;
                            break;
                        }
                    }
                    if ($clash) {
                        continue;
                    }

                    $prevDur = $dayDurations[$hari] ?? 0;
                    $dayDurations[$hari] = $end;
                    $assignedHere = !isset($subjectGuru[$subjectId]);
                    for ($jam = $start; $jam <= $end; $jam++) {
                        $guruSchedule[$guruId][$hari][$jam] = true;
                    }
                    if ($assignedHere) {
                        $subjectGuru[$subjectId] = $guru;
                    }
                    $subjectDays[$subjectId][$hari] = true;
                    $assignments[$blockIdx] = ['hari' => $hari, 'start' => $start, 'guru_id' => $guruId];

                    if ($solve($blockIdx + 1)) {
                        return true;
                    }

                    $dayDurations[$hari] = $prevDur;
                    for ($jam = $start; $jam <= $end; $jam++) {
                        unset($guruSchedule[$guruId][$hari][$jam]);
                    }
                    unset($subjectDays[$subjectId][$hari]);
                    if ($assignedHere) {
                        unset($subjectGuru[$subjectId]);
                    }
                    unset($assignments[$blockIdx]);
                }
            }

            return false;
        };

        $ok = $solve(0);

        // Solusi parsial akibat timeout tidak boleh dipakai.
        return ($ok && !$timedOut) ? $assignments : null;
    }

    /**
     * Fase best-effort: tempatkan blok sebanyak mungkin hanya dengan aturan keras
     * (kapasitas hari, slot bebas, tanpa bentrok guru). Mengisi pula lubang jam.
     */
    private function isiSisaSecaraRakus(array $pool, array $hariList, array $limitPerHari, array &$guruSchedule): array
    {
        $grid        = [];
        $subjectDays = [];
        $subjectGuru = [];
        $placements  = [];
        $sisa        = [];

        foreach ($pool as $idx => $block) {
            $subjectId = $block['s']->id;
            $dur       = $block['dur'];

            $candidates = isset($subjectGuru[$subjectId])
                ? [$subjectGuru[$subjectId]]
                : $block['gurus']->all();

            $ditempatkan = false;

            foreach ($candidates as $guru) {
                if ($ditempatkan) break;
                $guruId = $guru->id;

                foreach ($hariList as $hari) {
                    if ($ditempatkan) break;
                    $batas = $limitPerHari[$hari];

                    for ($start = 1; $start <= $batas - $dur + 1; $start++) {
                        $end   = $start + $dur - 1;
                        $bebas = true;

                        for ($j = $start; $j <= $end; $j++) {
                            if (isset($grid[$hari][$j]) || isset($guruSchedule[$guruId][$hari][$j])) {
                                $bebas = false;
                                break;
                            }
                        }
                        if (!$bebas) continue;

                        for ($j = $start; $j <= $end; $j++) {
                            $grid[$hari][$j] = true;
                            $guruSchedule[$guruId][$hari][$j] = true;
                        }
                        $subjectDays[$subjectId][$hari] = true;
                        if (!isset($subjectGuru[$subjectId])) {
                            $subjectGuru[$subjectId] = $guru;
                        }
                        $placements[$idx] = ['hari' => $hari, 'start' => $start, 'guru_id' => $guruId];
                        $ditempatkan = true;
                        break;
                    }
                }
            }

            if (!$ditempatkan) {
                $sisa[$idx] = $block;
            }
        }

        return [$placements, $sisa];
    }


    public function exportPdf(Request $request)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $tahunAjaranId = $request->tahun_ajaran_id ?? $tahunAjaranAktif?->id;
        $tahunAjaran = TahunAjaran::find($tahunAjaranId);
        $rombelId = $request->rombel_id;

        $jadwalQuery = Jadwal::with(['rombel', 'mataPelajaran', 'guru', 'tahunAjaran'])
            ->where('tahun_ajaran_id', $tahunAjaranId);

        if ($rombelId) {
            $jadwalQuery->where('rombel_id', $rombelId);
        }

        $jadwal = $jadwalQuery->orderBy('hari')->orderBy('jam_ke')->get();

        $grouped = [];
        $hariUrutan = $this->getHariAktif();
        $maxJam = $jadwal->max('jam_ke') ?? 10;

        if ($rombelId) {
            $rombel = Rombel::find($rombelId);
            if ($rombel) {
                $grid = [];
                foreach ($hariUrutan as $hari) {
                    $grid[$hari] = [];
                    for ($j = 1; $j <= $maxJam; $j++) {
                        $entry = $jadwal->firstWhere(fn($item) => $item->hari === $hari && $item->jam_ke === $j);
                        $grid[$hari][$j] = $entry;
                    }
                }
                $grouped[] = [
                    'rombel' => $rombel,
                    'grid' => $grid,
                    'max_jam' => $maxJam,
                ];
            }
        } else {
            $rombels = Rombel::where('tahun_ajaran_id', $tahunAjaranId)
                ->orderBy('tingkat')->orderBy('nama')->get();

            foreach ($rombels as $rombel) {
                $jadwalRombel = $jadwal->where('rombel_id', $rombel->id);
                $grid = [];
                foreach ($hariUrutan as $hari) {
                    $grid[$hari] = [];
                    for ($j = 1; $j <= $maxJam; $j++) {
                        $entry = $jadwalRombel->firstWhere(fn($item) => $item->hari === $hari && $item->jam_ke === $j);
                        $grid[$hari][$j] = $entry;
                    }
                }
                $grouped[] = [
                    'rombel' => $rombel,
                    'grid' => $grid,
                    'max_jam' => $maxJam,
                ];
            }
        }

        $namaSekolah = Setting::get('nama_sekolah', 'SIAMA SCHOOL');

        $pdf = Pdf::loadView('exports.jadwal-pelajaran', [
            'jadwalGrouped' => $grouped,
            'tahunAjaran' => $tahunAjaran,
            'hariList' => $hariUrutan,
            'maxJam' => $maxJam,
            'namaSekolah' => $namaSekolah,
            'selectedRombel' => $rombelId ? Rombel::find($rombelId) : null,
        ])->setPaper('a4', 'landscape');

        $rombelNama = $rombelId ? (Rombel::find($rombelId)?->nama ?? 'Rombel') : 'Semua_Kelas';
        $filename = 'Jadwal_Pelajaran_' . str_replace(['/', ' '], '_', $rombelNama) . '_' . str_replace(['/', ' '], '_', $tahunAjaran?->nama ?? 'Aktif') . '.pdf';

        return $pdf->download($filename);
    }
}

