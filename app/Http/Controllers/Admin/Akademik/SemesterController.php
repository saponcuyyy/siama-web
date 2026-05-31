<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SemesterController extends Controller
{
    public function index(Request $request)
    {
        $query = Semester::latest();

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%');
        }

        return Inertia::render('Admin/Akademik/Semester/Index', [
            'semesterList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:semester,nama',
            'is_active' => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        Semester::create($validated);

        return back()->with('success', 'Semester berhasil ditambahkan.');
    }

    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:semester,nama,'.$semester->id,
            'is_active' => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            Semester::where('id', '!=', $semester->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $semester->update($validated);

        return back()->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();

        return back()->with('success', 'Semester berhasil dihapus.');
    }
}
