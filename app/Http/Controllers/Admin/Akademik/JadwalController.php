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

        $exists = Jadwal::where([
            'rombel_id' => $validated['rombel_id'],
            'hari' => $validated['hari'],
            'jam_ke' => $validated['jam_ke'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
        ])->exists();

        if ($exists) {
            return back()->with('error', 'Sudah ada mata pelajaran pada jam tersebut.');
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

        $exists = Jadwal::where([
            'rombel_id' => $jadwal->rombel_id,
            'hari' => $validated['hari'],
            'jam_ke' => $validated['jam_ke'],
            'tahun_ajaran_id' => $jadwal->tahun_ajaran_id,
        ])->where('id', '!=', $jadwal->id)->exists();

        if ($exists) {
            return back()->with('error', 'Sudah ada mata pelajaran pada jam tersebut.');
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
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'rombel_ids' => 'nullable|array',
            'rombel_ids.*' => 'exists:rombel,id',
            'max_jam' => 'nullable|integer|min:1|max:20',
        ]);

        $tahunAjaranId = $validated['tahun_ajaran_id'];
        $maxJam = $validated['max_jam'] ?? 10;
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

        $subjects = MataPelajaran::with(['gurus' => function ($q) {
            $q->select('guru.id', 'guru.nama');
        }])->get();

        $teacherSchedule = [];
        $created = 0;
        $errors = [];

        foreach ($rombels as $rombel) {
            $matchingSubjects = $subjects->filter(function ($subject) use ($rombel) {
                if ($subject->tingkat !== $rombel->tingkat) {
                    return false;
                }
                if ($subject->jurusan && $subject->jurusan !== $rombel->jurusan) {
                    return false;
                }
                return $subject->jam_per_minggu > 0 && $subject->gurus->isNotEmpty();
            })->values();

            if ($matchingSubjects->isEmpty()) {
                $errors[] = "{$rombel->nama}: tidak ada mata pelajaran cocok dengan guru pengampu.";
                continue;
            }

            $totalJam = $matchingSubjects->sum('jam_per_minggu');
            $availableSlots = count($hariList) * $maxJam;

            if ($totalJam > $availableSlots) {
                $errors[] = "{$rombel->nama}: total jam ({$totalJam}) melebihi slot ({$availableSlots}).";
                continue;
            }

            $remaining = [];
            foreach ($matchingSubjects as $s) {
                $remaining[$s->id] = $s->jam_per_minggu;
            }

            foreach ($hariList as $hari) {
                for ($jam = 1; $jam <= $maxJam; $jam++) {
                    $candidates = [];
                    foreach ($remaining as $subjectId => $hours) {
                        if ($hours <= 0) {
                            continue;
                        }
                        $subject = $matchingSubjects->firstWhere('id', $subjectId);
                        if (!$subject) {
                            continue;
                        }
                        foreach ($subject->gurus as $guru) {
                            if (!isset($teacherSchedule[$guru->id][$hari][$jam])) {
                                $candidates[] = [
                                    'subject' => $subject,
                                    'guru' => $guru,
                                    'remaining' => $hours,
                                ];
                                break;
                            }
                        }
                    }

                    if (empty($candidates)) {
                        continue;
                    }

                    usort($candidates, fn($a, $b) => $b['remaining'] <=> $a['remaining']);

                    $best = $candidates[0];

                    Jadwal::create([
                        'rombel_id' => $rombel->id,
                        'mata_pelajaran_id' => $best['subject']->id,
                        'guru_id' => $best['guru']->id,
                        'tahun_ajaran_id' => $tahunAjaranId,
                        'hari' => $hari,
                        'jam_ke' => $jam,
                    ]);

                    $teacherSchedule[$best['guru']->id][$hari][$jam] = true;
                    $remaining[$best['subject']->id]--;
                    $created++;
                }
            }
        }

        $message = "Jadwal berhasil digenerate: {$created} entries.";
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', $errors);
        }

        return back()->with('success', $message);
    }
}

