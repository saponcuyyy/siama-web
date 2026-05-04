<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelulusanController extends Controller
{
    public function index()
    {
        return Inertia::render('Public/Kelulusan/Index', [
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function cek(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
            'tanggal_lahir' => 'required|date',
        ]);

        $siswa = Siswa::where('nisn', $request->nisn)
                      ->where('tanggal_lahir', $request->tanggal_lahir)
                      ->first();

        if (!$siswa) {
            return back()->withErrors([
                'nisn' => 'Data siswa tidak ditemukan atau kombinasi NISN & Tanggal Lahir salah.',
            ]);
        }

        // Simpan data di session untuk mencegah akses langsung via URL ke halaman hasil
        session(['siswa_lulus' => $siswa]);

        return redirect()->route('public.kelulusan.hasil');
    }

    public function hasil()
    {
        $siswa = session('siswa_lulus');

        if (!$siswa) {
            return redirect()->route('public.kelulusan')->withErrors([
                'nisn' => 'Sesi berakhir. Silakan login kembali.',
            ]);
        }

        return Inertia::render('Public/Kelulusan/Hasil', [
            'siswa' => $siswa,
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
