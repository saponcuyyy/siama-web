<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class SiswaKelulusanController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with('rombel');

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%')
                ->orWhere('nisn', 'like', '%'.$request->search.'%');
        }

        if ($request->status && $request->status !== 'semua') {
            $query->where('status_lulus', $request->status);
        }

        $stats = Siswa::selectRaw('COUNT(*) as total')
            ->selectRaw("COALESCE(SUM(CAST(status_lulus = 'lulus' AS UNSIGNED)), 0) as lulus")
            ->selectRaw("COALESCE(SUM(CAST(status_lulus = 'tidak_lulus' AS UNSIGNED)), 0) as tidak_lulus")
            ->selectRaw("COALESCE(SUM(CAST(status_lulus = 'ditunda' AS UNSIGNED)), 0) as ditunda")
            ->toBase()
            ->first();

        return Inertia::render('Admin/Kelulusan/Index', [
            'siswas' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'mode' => 'required|in:tambah,ganti',
        ]);

        // Jika mode "ganti", hapus semua data siswa dulu
        if ($request->mode === 'ganti') {
            if (! auth()->user()->hasRole('super_admin')) {
                return back()->with('error', 'Mode "ganti" hanya dapat dilakukan oleh Super Admin.');
            }
            Siswa::query()->forceDelete();
        }

        $import = new SiswaImport;
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor: '.$e->getMessage());
        }

        $failures = $import->failures();
        $errors = $import->errors();

        if ($failures->count() > 0 || count($errors) > 0) {
            $failMessages = $failures->map(fn ($f) => "Baris {$f->row()}: ".implode(', ', $f->errors()))->toArray();

            return back()->with('warning', 'Import selesai dengan '.$failures->count().' baris gagal. '.implode(' | ', $failMessages));
        }

        return back()->with('success', 'Data siswa berhasil diimport.');
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'status_lulus' => 'required|in:lulus,tidak_lulus,ditunda',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $siswa->update($validated);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    public function destroyAll()
    {
        if (! auth()->user()->hasRole('super_admin')) {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus seluruh data siswa.');
        }
        Siswa::query()->forceDelete();

        return back()->with('success', 'Seluruh data siswa berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        // Generate template CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_siswa.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['nisn', 'nama', 'tanggal_lahir', 'status_lulus', 'keterangan']);
            fputcsv($handle, ['1234567890', 'Budi Santoso', '2006-05-15', 'lulus', 'Selamat, Anda lulus!']);
            fputcsv($handle, ['0987654321', 'Siti Aminah', '2006-08-20', 'tidak_lulus', 'Silakan mengikuti remedial.']);
            fputcsv($handle, ['1111111111', 'Andi Darmawan', '2006-01-01', 'ditunda', 'Lengkapi dokumen administrasi.']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
