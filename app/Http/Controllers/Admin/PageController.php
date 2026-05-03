<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/Halaman/Index', [
            'pages' => Page::with('author:id,name')->latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Web/Halaman/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['created_by'] = auth()->id();

        Page::create($validated);

        return redirect()->route('admin.web.halaman.index')->with('success', 'Halaman berhasil dibuat.');
    }

    public function edit(Page $halaman)
    {
        return Inertia::render('Admin/Web/Halaman/Form', [
            'page' => $halaman
        ]);
    }

    public function update(Request $request, Page $halaman)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $halaman->update($validated);

        return redirect()->route('admin.web.halaman.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(Page $halaman)
    {
        $halaman->delete();
        return back()->with('success', 'Halaman berhasil dihapus.');
    }
}
