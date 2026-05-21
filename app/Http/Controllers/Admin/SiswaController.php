<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Rombel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['rombel.tahunAjaran'])
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nisn', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->rombel_id) {
            $query->where('rombel_id', $request->rombel_id);
        }

        return Inertia::render('Admin/Akademik/Siswa/Index', [
            'siswaList'  => $query->paginate(20)->withQueryString(),
            'filters'    => $request->only(['search', 'rombel_id']),
            'rombelList' => Rombel::with('tahunAjaran')->select('id', 'nama', 'tingkat', 'tahun_ajaran_id')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'nisn'          => 'required|string|max:20|unique:siswa,nisn',
            'tanggal_lahir' => 'required|date',
            'rombel_id'     => 'required|exists:rombel,id',
        ]);

        $formattedPassword = date('dmY', strtotime($validated['tanggal_lahir'])) . '*';

        DB::transaction(function () use ($validated, $formattedPassword) {
            // Buat akun user untuk login portal ujian
            $user = User::create([
                'name'     => $validated['nama'],
                'email'    => $validated['nisn'],
                'password' => Hash::make($formattedPassword),
            ]);

            // Assign role siswa
            $user->assignRole('siswa');

            // Buat data siswa
            Siswa::create([
                'user_id'       => $user->id,
                'nisn'          => $validated['nisn'],
                'nama'          => $validated['nama'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'rombel_id'     => $validated['rombel_id'],
            ]);
        });

        return back()->with('success', 'Data siswa berhasil ditambahkan. Akun login dibuat otomatis: Username = ' . $validated['nisn'] . ' & Password = ' . $formattedPassword);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'nisn'          => 'required|string|max:20|unique:siswa,nisn,' . $siswa->id,
            'tanggal_lahir' => 'required|date',
            'rombel_id'     => 'required|exists:rombel,id',
        ]);

        $formattedPassword = date('dmY', strtotime($validated['tanggal_lahir'])) . '*';

        DB::transaction(function () use ($siswa, $validated, $formattedPassword) {
            $siswa->update([
                'nama'          => $validated['nama'],
                'nisn'          => $validated['nisn'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'rombel_id'     => $validated['rombel_id'],
            ]);

            // Sync user name, email (NISN), & password jika ada user terkait
            if ($siswa->user_id) {
                $siswa->user->update([
                    'name'     => $validated['nama'],
                    'email'    => $validated['nisn'],
                    'password' => Hash::make($formattedPassword),
                ]);
            }
        });

        return back()->with('success', 'Data siswa berhasil diperbarui.');
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
