<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengumumanController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/Pengumuman/Index', [
            'pengumuman' => Pengumuman::with('author:id,name')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Web/Pengumuman/Form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'           => 'required|string|max:255',
            'konten'          => 'required|string',
            'prioritas'       => 'required|in:rendah,normal,tinggi',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'required|in:aktif,nonaktif',
        ]);
        $data['created_by'] = auth()->id();
        Pengumuman::create($data);
        return redirect()->route('admin.web.pengumuman.index')->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Pengumuman $pengumuman)
    {
        return Inertia::render('Admin/Web/Pengumuman/Form', ['pengumuman' => $pengumuman]);
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $request->validate([
            'judul'           => 'required|string|max:255',
            'konten'          => 'required|string',
            'prioritas'       => 'required|in:rendah,normal,tinggi',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'required|in:aktif,nonaktif',
        ]);
        $pengumuman->update($data);
        return redirect()->route('admin.web.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();
        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
