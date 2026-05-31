<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SiswaRombelImport;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['rombel.tahunAjaran'])
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                    ->orWhere('nisn', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->rombel_id) {
            $query->where('rombel_id', $request->rombel_id);
        }

        return Inertia::render('Admin/Akademik/Siswa/Index', [
            'siswaList' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'rombel_id']),
            'rombelList' => Rombel::with('tahunAjaran')->select('id', 'nama', 'tingkat', 'tahun_ajaran_id')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nisn' => 'required|string|max:20|unique:siswa,nisn',
            'tanggal_lahir' => 'required|date',
            'agama' => 'nullable|string|max:50',
            'rombel_id' => 'required|exists:rombel,id',
        ]);

        $randomPassword = Str::password(10);

        DB::transaction(function () use ($validated, $randomPassword) {
            // Buat akun user untuk login portal ujian
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['nisn'],
                'password' => Hash::make($randomPassword),
            ]);

            // Assign role siswa
            $user->assignRole('siswa');

            // Buat data siswa
            Siswa::create([
                'user_id' => $user->id,
                'nisn' => $validated['nisn'],
                'nama' => $validated['nama'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'agama' => $validated['agama'] ?? null,
                'rombel_id' => $validated['rombel_id'],
            ]);
        });

        return back()->with('success', 'Data siswa berhasil ditambahkan. Akun login telah dibuat.');
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nisn' => 'required|string|max:20|unique:siswa,nisn,'.$siswa->id,
            'tanggal_lahir' => 'required|date',
            'agama' => 'nullable|string|max:50',
            'rombel_id' => 'required|exists:rombel,id',
            'reset_password' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($siswa, $validated) {
            $siswa->update([
                'nama' => $validated['nama'],
                'nisn' => $validated['nisn'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'agama' => $validated['agama'] ?? null,
                'rombel_id' => $validated['rombel_id'],
            ]);

            // Sync user name & email (NISN)
            if ($siswa->user_id) {
                $userData = [
                    'name' => $validated['nama'],
                    'email' => $validated['nisn'],
                ];

                // Hanya reset password jika checkbox reset_password dicentang
                if (! empty($validated['reset_password'])) {
                    $newPassword = Str::password(10);
                    $userData['password'] = Hash::make($newPassword);
                    $siswa->freshPassword = $newPassword;
                }

                $siswa->user->update($userData);
            }
        });

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'rombel_id' => 'required|exists:rombel,id',
        ]);

        $rombel = Rombel::findOrFail($request->rombel_id);
        $import = new SiswaRombelImport((int) $request->rombel_id);
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor: '.$e->getMessage());
        }

        $failures = $import->failures();
        $errors = $import->errors();
        $total = count($import->createdAccounts);

        $message = "Import selesai. {$total} siswa berhasil ditambahkan ke rombel {$rombel->nama}.";

        if ($total > 0) {
            $list = collect($import->createdAccounts)
                ->take(5)
                ->map(fn ($a) => $a['nisn'])
                ->implode(', ');
            $message .= " NISN: {$list}".(count($import->createdAccounts) > 5 ? ', ...' : '');
        }

        if ($failures->count() > 0 || count($errors) > 0) {
            $failMessages = $failures->map(fn ($f) => "Baris {$f->row()}: ".implode(', ', $f->errors()))->toArray();

            return back()->with('warning', $message.' | '.count($failMessages).' baris gagal: '.implode(' | ', $failMessages));
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        return Excel::download(new SiswaTemplateExport, 'template-import-siswa.xlsx');
    }

    public function destroy(Siswa $siswa)
    {
        DB::transaction(function () use ($siswa) {
            // Hapus user terkait jika ada
            if ($siswa->user_id) {
                $siswa->user?->delete();
            }
            $siswa->delete();
        });

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
