<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::with('user')
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

        return Inertia::render('Admin/Akademik/Guru/Index', [
            'guruList' => $query->paginate(20)->withQueryString(),
            'filters'  => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'nip'           => 'required|string|max:30|unique:guru,nip',
            'jabatan'       => 'nullable|string|max:100',
            'email'         => 'required|email|max:255|unique:users,email',
            'tanggal_lahir' => 'nullable|date',
        ]);

        $defaultPassword = Str::password(10);

        DB::transaction(function () use ($validated, $defaultPassword) {
            $user = User::create([
                'name'     => $validated['nama'],
                'email'    => $validated['email'],
                'password' => Hash::make($defaultPassword),
            ]);

            $user->assignRole('guru');

            Guru::create([
                'user_id'       => $user->id,
                'nip'           => $validated['nip'],
                'nama'          => $validated['nama'],
                'jabatan'       => $validated['jabatan'] ?? 'Guru',
                'tanggal_lahir' => $validated['tanggal_lahir'],
            ]);
        });

        return back()->with('success', 'Data guru berhasil ditambahkan. Akun login telah dibuat.');
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'nip'           => 'required|string|max:30|unique:guru,nip,' . $guru->id,
            'jabatan'       => 'nullable|string|max:100',
            'email'         => 'required|email|max:255|unique:users,email,' . $guru->user_id,
            'tanggal_lahir' => 'nullable|date',
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
}
