<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class KelulusanController extends Controller
{
    public function index()
    {
        return Inertia::render('Public/Kelulusan/Index', [
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
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
        session(['siswa_lulus_id' => $siswa->id]);

        return redirect()->route('public.kelulusan.hasil');
    }

    public function hasil()
    {
        $siswaId = session('siswa_lulus_id');

        if (!$siswaId) {
            return redirect()->route('public.kelulusan')->withErrors([
                'nisn' => 'Sesi berakhir. Silakan login kembali.',
            ]);
        }

        $siswa = Siswa::with('rombel')->find($siswaId);

        if (!$siswa) {
            session()->forget('siswa_lulus_id');
            return redirect()->route('public.kelulusan')->withErrors([
                'nisn' => 'Data siswa tidak ditemukan.',
            ]);
        }

        return Inertia::render('Public/Kelulusan/Hasil', [
            'siswa' => $siswa,
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
        ]);
    }
}
