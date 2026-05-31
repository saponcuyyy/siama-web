<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Rombel;
use App\Models\Siswa;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        if (auth()->user()->hasRole('siswa')) {
            return redirect()->route('ujian.index');
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_siswa' => Siswa::count(),
                'total_guru' => Guru::count(),
                'total_rombel' => Rombel::count(),
            ]
        ]);
    }
}
