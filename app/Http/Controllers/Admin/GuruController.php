<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GuruTemplateExport;
use App\Imports\GuruImport;
use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::with(['user', 'mataPelajarans'])
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

        $mapelList = MataPelajaran::orderBy('nama')->get(['id', 'nama', 'kode', 'tingkat', 'jurusan']);

        return Inertia::render('Admin/Akademik/Guru/Index', [
            'guruList'  => $query->paginate(20)->withQueryString(),
            'filters'   => $request->only(['search']),
            'mapelList' => $mapelList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:255',
            'nip'               => 'required|string|max:30|unique:guru,nip',
            'jabatan'           => 'nullable|string|max:100',
            'email'             => 'required|email|max:255|unique:users,email',
            'tanggal_lahir'     => 'nullable|date',
            'mata_pelajaran_ids'=> 'nullable|array',
            'mata_pelajaran_ids.*' => 'exists:mata_pelajaran,id',
        ]);

        $defaultPassword = Str::password(10);

        DB::transaction(function () use ($validated, $defaultPassword) {
            $user = User::create([
                'name'     => $validated['nama'],
                'email'    => $validated['email'],
                'password' => Hash::make($defaultPassword),
            ]);

            $user->assignRole('guru');

            $guru = Guru::create([
                'user_id'       => $user->id,
                'nip'           => $validated['nip'],
                'nama'          => $validated['nama'],
                'jabatan'       => $validated['jabatan'] ?? 'Guru',
                'tanggal_lahir' => $validated['tanggal_lahir'],
            ]);

            if (!empty($validated['mata_pelajaran_ids'])) {
                $guru->mataPelajarans()->sync($validated['mata_pelajaran_ids']);
            }
        });

        return back()->with('success', 'Data guru berhasil ditambahkan. Akun login telah dibuat.');
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:255',
            'nip'               => 'required|string|max:30|unique:guru,nip,' . $guru->id,
            'jabatan'           => 'nullable|string|max:100',
            'email'             => 'required|email|max:255|unique:users,email,' . $guru->user_id,
            'tanggal_lahir'     => 'nullable|date',
            'mata_pelajaran_ids'=> 'nullable|array',
            'mata_pelajaran_ids.*' => 'exists:mata_pelajaran,id',
        ]);

        DB::transaction(function () use ($guru, $validated) {
            $guru->update([
                'nama'          => $validated['nama'],
                'nip'           => $validated['nip'],
                'jabatan'       => $validated['jabatan'] ?? 'Guru',
                'tanggal_lahir' => $validated['tanggal_lahir'],
            ]);

            if ($guru->user_id) {
                $guru->user->update([
                    'name'  => $validated['nama'],
                    'email' => $validated['email'],
                ]);
            }

            if (array_key_exists('mata_pelajaran_ids', $validated)) {
                $guru->mataPelajarans()->sync($validated['mata_pelajaran_ids'] ?? []);
            }
        });

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        DB::transaction(function () use ($guru) {
            if ($guru->user_id) {
                $guru->user?->delete();
            }
            $guru->delete();
        });

        return back()->with('success', 'Data guru berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new GuruImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor: ' . $e->getMessage());
        }

        $total = count($import->createdAccounts);

        $message = "Import selesai. {$total} data guru berhasil ditambahkan.";

        if ($total > 0) {
            $list = collect($import->createdAccounts)
                ->take(5)
                ->map(fn($a) => "{$a['nama']} ({$a['email']})")
                ->implode(', ');
            $message .= " Akun dibuat: {$list}" . (count($import->createdAccounts) > 5 ? ', ...' : '');
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        return Excel::download(new GuruTemplateExport, 'template-import-guru.xlsx');
    }
}
