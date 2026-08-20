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
            'rombels' => Rombel::select('id', 'nama', 'tingkat')
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
        ]);

        $tahunAjaranId = $validated['tahun_ajaran_id'];
        $defaultMaxJam = $validated['max_jam'] ?? 8;
        $maxJamTingkat = [
            'X'   => (int)($validated['max_jam_tingkat']['X']   ?? $defaultMaxJam),
            'XI'  => (int)($validated['max_jam_tingkat']['XI']  ?? $defaultMaxJam),
            'XII' => (int)($validated['max_jam_tingkat']['XII'] ?? $defaultMaxJam),
        ];
        $hariList = $this->getHariAktif();

        $rombels = Rombel::where('tahun_ajaran_id', $tahunAjaranId)
            ->when(isset($validated['rombel_ids']), fn($q) => $q->whereIn('id', $validated['rombel_ids']))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();

        if ($rombels->isEmpty()) {
            return back()->with('error', 'Tidak ada rombel yang dipilih.');
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

        foreach ($rombels as $rombel) {
            $maxJam = $maxJamTingkat[$rombel->tingkat] ?? $defaultMaxJam;

            $matchingSubjects = $subjects->filter(function ($s) use ($rombel) {
                if ($s->tingkat !== $rombel->tingkat) return false;
                if ($s->jurusan && $s->jurusan !== $rombel->jurusan) return false;
                return $s->jam_per_minggu > 0 && $s->gurus->isNotEmpty();
            })->values();

            if ($matchingSubjects->isEmpty()) {
                $errors[] = "{$rombel->nama}: tidak ada mata pelajaran cocok.";
                continue;
            }

            $totalJam       = $matchingSubjects->sum('jam_per_minggu');
            $availableSlots = count($hariList) * $maxJam;

            if ($totalJam > $availableSlots) {
                $errors[] = "{$rombel->nama}: total jam ({$totalJam}) melebihi kapasitas ({$availableSlots}).";
                continue;
            }

            // ── 1. Pecah semua mapel menjadi blok 2-jam dan sisa 1-jam ──
            $pool = [];
            foreach ($matchingSubjects as $subject) {
                $guru  = $subject->gurus->first();
                $hours = $subject->jam_per_minggu;
                while ($hours >= 2) {
                    $pool[] = ['s' => $subject, 'g' => $guru, 'dur' => 2];
                    $hours -= 2;
                }
                if ($hours === 1) {
                    $pool[] = ['s' => $subject, 'g' => $guru, 'dur' => 1];
                }
            }
            // Prioritaskan blok 2 jam agar terisi teratur
            usort($pool, fn($a, $b) => $b['dur'] <=> $a['dur']);

            // Backtracking variables
            $dayDurations = [];
            $subjectDays = [];
            $assignments = [];

            // Solver closure
            $solve = function($blockIdx, $allowSameDaySubject, $minHours) use (
                &$solve, $pool, $hariList, $maxJam, $totalJam,
                &$teacherSchedule, &$dayDurations, &$subjectDays, &$assignments
            ) {
                if ($blockIdx >= count($pool)) {
                    $effectiveMin = min($minHours, $totalJam);
                    foreach ($hariList as $hari) {
                        $dur = $dayDurations[$hari] ?? 0;
                        if ($totalJam >= count($hariList) && $dur === 0) {
                            return false; // Day cannot be empty if we have enough total hours
                        }
                        if ($dur > 0 && $dur < $effectiveMin) {
                            return false;
                        }
                    }
                    return true;
                }

                $block = $pool[$blockIdx];
                $subjectId = $block['s']->id;
                $guruId = $block['g']->id;
                $dur = $block['dur'];

                foreach ($hariList as $hari) {
                    $start = ($dayDurations[$hari] ?? 0) + 1;
                    $end = $start + $dur - 1;

                    if ($end > $maxJam) {
                        continue;
                    }

                    if (!$allowSameDaySubject && isset($subjectDays[$subjectId][$hari])) {
                        continue;
                    }

                    // Check teacher availability
                    $clash = false;
                    for ($jam = $start; $jam <= $end; $jam++) {
                        if (isset($teacherSchedule[$guruId][$hari][$jam])) {
                            $clash = true;
                            break;
                        }
                    }
                    if ($clash) {
                        continue;
                    }

                    // Place block
                    $prevDur = $dayDurations[$hari] ?? 0;
                    $dayDurations[$hari] = $end;
                    for ($jam = $start; $jam <= $end; $jam++) {
                        $teacherSchedule[$guruId][$hari][$jam] = true;
                    }
                    $subjectDays[$subjectId][$hari] = true;
                    $assignments[$blockIdx] = ['hari' => $hari, 'start' => $start];

                    if ($solve($blockIdx + 1, $allowSameDaySubject, $minHours)) {
                        return true;
                    }

                    // Backtrack
                    $dayDurations[$hari] = $prevDur;
                    for ($jam = $start; $jam <= $end; $jam++) {
                        unset($teacherSchedule[$guruId][$hari][$jam]);
                    }
                    unset($subjectDays[$subjectId][$hari]);
                    unset($assignments[$blockIdx]);
                }

                return false;
            };

            $teacherScheduleBackup = $teacherSchedule;
            $solved = false;

            // Strategy relaxation cascade
            foreach ([
                ['sameDay' => false, 'min' => 4],
                ['sameDay' => true, 'min' => 4],
                ['sameDay' => false, 'min' => 1],
                ['sameDay' => true, 'min' => 1]
            ] as $strategy) {
                $dayDurations = [];
                foreach ($hariList as $hari) {
                    $dayDurations[$hari] = 0;
                }
                $subjectDays = [];
                $assignments = [];
                $teacherSchedule = $teacherScheduleBackup;

                if ($solve(0, $strategy['sameDay'], $strategy['min'])) {
                    $solved = true;
                    break;
                }
            }

            if ($solved) {
                foreach ($assignments as $blockIdx => $assign) {
                    $block = $pool[$blockIdx];
                    $hari = $assign['hari'];
                    $start = $assign['start'];
                    $dur = $block['dur'];
                    for ($k = 0; $k < $dur; $k++) {
                        $jamPos = $start + $k;
                        Jadwal::create([
                            'rombel_id'         => $rombel->id,
                            'mata_pelajaran_id' => $block['s']->id,
                            'guru_id'           => $block['g']->id,
                            'tahun_ajaran_id'   => $tahunAjaranId,
                            'hari'              => $hari,
                            'jam_ke'            => $jamPos,
                        ]);
                        $created++;
                    }
                }
            } else {
                $errors[] = "{$rombel->nama}: Gagal menjadwalkan kelas karena bentrok guru atau slot tidak mencukupi.";
            }
        }

        $message = "Jadwal berhasil digenerate: {$created} entri jadwal dibuat.";
        if (!empty($errors)) $message .= ' ' . implode(' ', $errors);
        return back()->with('success', $message);
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

