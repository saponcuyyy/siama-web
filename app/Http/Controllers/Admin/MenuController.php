<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/Menu/Index', [
            'menus' => Menu::with('children')->whereNull('parent_id')->orderBy('urutan')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'url' => 'required|string',
            'target' => 'required|in:_self,_blank',
            'parent_id' => 'nullable|exists:menus,id',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $validated['urutan'] = Menu::where('parent_id', $validated['parent_id'])->count();

        Menu::create($validated);

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'url' => 'required|string',
            'target' => 'required|in:_self,_blank',
            'parent_id' => 'nullable|exists:menus,id',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $menu->update($validated);

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete(); // This will cascade delete children if database is set up that way, or we can handle it here.
        return back()->with('success', 'Menu berhasil dihapus.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'menus' => 'required|array',
        ]);

        foreach ($request->menus as $index => $item) {
            Menu::where('id', $item['id'])->update([
                'urutan' => $index,
                'parent_id' => $item['parent_id'] ?? null
            ]);
        }

        return back()->with('success', 'Urutan menu diperbarui.');
    }
}
