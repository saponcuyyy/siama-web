<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RombelController extends Controller
{
    public function index(Request $request)
    {
        $query = Rombel::with(['tahunAjaran', 'waliKelas'])
            ->withCount('siswa')
            ->latest();

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%');
        }

        if ($request->tahun_ajaran_id) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        return Inertia::render('Admin/Akademik/Rombel/Index', [
            'rombelList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'tahun_ajaran_id']),
            'guruList' => Guru::select('id', 'nama', 'nip')->orderBy('nama')->get(),
            'tahunAjaranList' => TahunAjaran::select('id', 'nama', 'is_active')->orderByDesc('is_active')->orderByDesc('id')->get(),
            'rombelCountByTa' => Rombel::selectRaw('tahun_ajaran_id, count(*) as total')
                ->groupBy('tahun_ajaran_id')
                ->pluck('total', 'tahun_ajaran_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
            'jurusan' => 'nullable|in:IPA,IPS',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'nullable|exists:guru,id',
        ]);

        Rombel::create($validated);

        return back()->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function salin(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran_sumber' => 'required|exists:tahun_ajaran,id',
            'tahun_ajaran_tujuan' => 'required|exists:tahun_ajaran,id|different:tahun_ajaran_sumber',
        ], [
            'tahun_ajaran_tujuan.different' => 'Tahun ajaran tujuan tidak boleh sama dengan tahun ajaran sumber.',
        ]);

        $sumberNama = TahunAjaran::find($validated['tahun_ajaran_sumber'])?->nama ?? '-';
        $tujuanNama = TahunAjaran::find($validated['tahun_ajaran_tujuan'])?->nama ?? '-';

        $sumber = Rombel::where('tahun_ajaran_id', $validated['tahun_ajaran_sumber'])
            ->orderBy('tingkat')->orderBy('nama')
            ->get();

        if ($sumber->isEmpty()) {
            return back()->with('error', "Gagal: Tidak ada rombel pada tahun ajaran {$sumberNama} untuk disalin.");
        }

        // Rombel dengan nama+tingkat+jurusan yang sama di tahun ajaran tujuan tidak disalin ulang
        $existing = Rombel::where('tahun_ajaran_id', $validated['tahun_ajaran_tujuan'])
            ->get()
            ->keyBy(fn($r) => $r->tingkat.'|'.$r->jurusan.'|'.$r->nama);

        $dibuat = 0;
        $dilewati = 0;

        foreach ($sumber as $rombel) {
            $key = $rombel->tingkat.'|'.$rombel->jurusan.'|'.$rombel->nama;

            if ($existing->has($key)) {
                $dilewati++;
                continue;
            }

            Rombel::create([
                'nama'            => $rombel->nama,
                'tingkat'         => $rombel->tingkat,
                'jurusan'         => $rombel->jurusan,
                'tahun_ajaran_id' => $validated['tahun_ajaran_tujuan'],
                'guru_id'         => $rombel->guru_id,
            ]);
            $existing->put($key, true);
            $dibuat++;
        }

        if ($dibuat === 0) {
            return back()->with('error', "Gagal: Semua rombel dari {$sumberNama} sudah ada di {$tujuanNama}. Tidak ada yang perlu disalin.");
        }

        $pesan = "Berhasil menyalin {$dibuat} rombel dari {$sumberNama} ke {$tujuanNama}";
        $pesan .= $dilewati > 0 ? " ({$dilewati} dilewati karena sudah ada)." : '.';

        return back()->with('success', $pesan);
    }

    public function update(Request $request, Rombel $rombel)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
            'jurusan' => 'nullable|in:IPA,IPS',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'nullable|exists:guru,id',
        ]);

        $rombel->update($validated);

        return back()->with('success', 'Data rombel berhasil diperbarui.');
    }

    public function destroy(Rombel $rombel)
    {
        if ($rombel->siswa()->count() > 0) {
            return back()->with('error', 'Rombel tidak dapat dihapus karena masih memiliki siswa terdaftar.');
        }

        $rombel->delete();

        return back()->with('success', 'Rombel berhasil dihapus.');
    }
}
