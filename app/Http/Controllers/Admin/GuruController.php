<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GuruTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\GuruImport;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Rombel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::with(['user'])
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                    ->orWhere('nip', 'like', '%'.$request->search.'%');
            });
        }

        $guruList = $query->paginate(20)->withQueryString();

        // Penugasan mengajar per kelas (rombel) untuk tiap guru pada halaman ini.
        $penugasan = DB::table('guru_mata_pelajaran as gmp')
            ->join('mata_pelajaran as mp', 'mp.id', '=', 'gmp.mata_pelajaran_id')
            ->leftJoin('rombel as r', 'r.id', '=', 'gmp.rombel_id')
            ->whereIn('gmp.guru_id', collect($guruList->items())->pluck('id'))
            ->orderBy('mp.nama')
            ->orderBy('r.nama')
            ->get([
                'gmp.guru_id',
                'gmp.mata_pelajaran_id',
                'gmp.rombel_id',
                'gmp.jam_per_minggu',
                'mp.nama as mapel_nama',
                'mp.kode as mapel_kode',
                'mp.tingkat as mapel_tingkat',
                'mp.jurusan as mapel_jurusan',
                'r.nama as rombel_nama',
                'r.tingkat as rombel_tingkat',
                'r.jurusan as rombel_jurusan',
            ])
            ->groupBy('guru_id');

        foreach ($guruList->getCollection() as $guru) {
            $guru->penugasan = ($penugasan[$guru->id] ?? collect())->values();
        }

        $mapelList = MataPelajaran::orderBy('nama')->get(['id', 'nama', 'kode', 'tingkat', 'jurusan']);
        $rombelList = Rombel::with('tahunAjaran:id,nama')
            ->select('id', 'nama', 'tingkat', 'jurusan', 'tahun_ajaran_id')
            ->orderBy('nama')
            ->get();

        return Inertia::render('Admin/Akademik/Guru/Index', [
            'guruList' => $guruList,
            'filters' => $request->only(['search']),
            'mapelList' => $mapelList,
            'rombelList' => $rombelList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->guruRules(), [
            'nip' => 'required|string|max:30|unique:guru,nip',
            'email' => 'required|email|max:255|unique:users,email',
        ]));

        $defaultPassword = Guru::defaultPassword($validated['tanggal_lahir']);

        $guru = DB::transaction(function () use ($validated, $defaultPassword) {
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($defaultPassword),
            ]);

            $user->assignRole('guru');

            $guru = Guru::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'nama' => $validated['nama'],
                'jabatan' => $validated['jabatan'] ?? 'Guru',
                'tanggal_lahir' => $validated['tanggal_lahir'],
            ]);

            $this->syncMataPelajaran($guru, $validated['mata_pelajaran'] ?? null);

            return $guru;
        });

        return back()->with('success', 'Data guru berhasil ditambahkan. Akun login telah dibuat.');
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate(array_merge($this->guruRules(), [
            'nip' => 'required|string|max:30|unique:guru,nip,'.$guru->id,
            'email' => 'required|email|max:255|unique:users,email,'.$guru->user_id,
        ]));

        DB::transaction(function () use ($guru, $validated) {
            $guru->update([
                'nama' => $validated['nama'],
                'nip' => $validated['nip'],
                'jabatan' => $validated['jabatan'] ?? 'Guru',
                'tanggal_lahir' => $validated['tanggal_lahir'],
            ]);

            if ($guru->user_id) {
                $guru->user->update([
                    'name' => $validated['nama'],
                    'email' => $validated['email'],
                ]);
            }

            if (array_key_exists('mata_pelajaran', $validated)) {
                $this->syncMataPelajaran($guru, $validated['mata_pelajaran']);
            }
        });

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Aturan validasi bersama store & update.
     *
     * Format penugasan per mapel:
     *   mata_pelajaran: [
     *     { id, jam?, kelas: [{ rombel_id: number|null, jam: number }] }
     *   ]
     */
    private function guruRules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'mata_pelajaran' => 'nullable|array',
            'mata_pelajaran.*.id' => 'required|exists:mata_pelajaran,id',
            'mata_pelajaran.*.jam' => 'nullable|integer|min:0|max:40',
            'mata_pelajaran.*.kelas' => 'nullable|array',
            'mata_pelajaran.*.kelas.*.rombel_id' => 'nullable|exists:rombel,id',
            'mata_pelajaran.*.kelas.*.jam' => 'nullable|integer|min:0|max:40',
        ];
    }

    private function syncMataPelajaran(Guru $guru, ?array $assignments): void
    {
        $rows = [];

        foreach (collect($assignments ?? []) as $assignment) {
            $mapelId = (int) ($assignment['id'] ?? 0);
            if (! $mapelId || ! MataPelajaran::query()->whereKey($mapelId)->exists()) {
                continue;
            }

            // Format baru: rincian per kelas. Fallback format lama: satu baris tanpa kelas.
            $entries = isset($assignment['kelas']) && is_array($assignment['kelas'])
                ? $assignment['kelas']
                : [['rombel_id' => null, 'jam' => $assignment['jam'] ?? 0]];

            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $rombelId = filled($entry['rombel_id'] ?? null) ? (int) $entry['rombel_id'] : null;

                // Dedupe: satu baris per kombinasi mapel + kelas.
                $rows[$mapelId.'|'.($rombelId ?? 0)] = [
                    'guru_id' => $guru->id,
                    'mata_pelajaran_id' => $mapelId,
                    'rombel_id' => $rombelId,
                    'jam_per_minggu' => max(0, min(40, (int) ($entry['jam'] ?? 0))),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $oldMapelIds = $guru->mataPelajarans()->pluck('mata_pelajaran_id')->all();

        $guru->mataPelajarans()->detach();

        if (! empty($rows)) {
            DB::table('guru_mata_pelajaran')->insert(array_values($rows));
        }

        $this->recalculateJamMapel(
            array_merge($oldMapelIds, array_column($rows, 'mata_pelajaran_id'))
        );
    }

    private function recalculateJamMapel(array $mapelIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $mapelIds))));
        if (empty($ids)) {
            return;
        }

        foreach (MataPelajaran::withTrashed()->whereIn('id', $ids)->get() as $mapel) {
            $total = (int) DB::table('guru_mata_pelajaran')
                ->where('mata_pelajaran_id', $mapel->id)
                ->sum('jam_per_minggu');

            $mapel->update(['jam_per_minggu' => $total]);
        }
    }

    public function destroy(Guru $guru)
    {
        DB::transaction(function () use ($guru) {
            $guru->delete();
            if ($guru->user_id) {
                $guru->user?->delete();
            }
        });

        return back()->with('success', 'Data guru berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new GuruImport;
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            Log::error('Import guru gagal: '.$e->getMessage());

            return back()->with('error', 'Gagal mengimpor file. Pastikan format file sudah benar.');
        }

        $total = count($import->createdAccounts);

        $message = "Import selesai. {$total} data guru berhasil ditambahkan.";

        if ($total > 0) {
            $list = collect($import->createdAccounts)
                ->take(5)
                ->map(fn ($a) => "{$a['nama']} ({$a['email']})")
                ->implode(', ');
            $message .= " Akun dibuat: {$list}".(count($import->createdAccounts) > 5 ? ', ...' : '');
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        return Excel::download(new GuruTemplateExport, 'template-import-guru.xlsx');
    }
}
