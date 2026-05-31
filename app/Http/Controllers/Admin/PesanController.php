<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesan;
use Inertia\Inertia;

class PesanController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/Pesan/Index', [
            'pesan' => Pesan::latest()->paginate(15),
        ]);
    }

    public function show(Pesan $pesan)
    {
        if ($pesan->status === 'belum_dibaca') {
            $pesan->update(['status' => 'sudah_dibaca']);
        }

        return Inertia::render('Admin/Web/Pesan/Show', ['pesan' => $pesan]);
    }

    public function destroy(Pesan $pesan)
    {
        $pesan->delete();

        return redirect()->route('admin.web.pesan.index')->with('success', 'Pesan dihapus.');
    }
}
