<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Services\Website\FileUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SliderController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        return Inertia::render('Admin/Web/Slider/Index', [
            'sliders' => Slider::orderBy('urutan')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'subjudul' => 'nullable|string|max:255',
            'file_path' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'link_url' => 'nullable|string|max:255',
            'link_text' => 'nullable|string|max:100',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($request->hasFile('file_path')) {
            $uploaded = $this->fileUploadService->uploadImage($request->file('file_path'), 'sliders');
            $validated['file_path'] = $uploaded['path'];
        }

        Slider::create($validated);

        return back()->with('success', 'Slider berhasil ditambahkan.');
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'subjudul' => 'nullable|string|max:255',
            'file_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'link_url' => 'nullable|string|max:255',
            'link_text' => 'nullable|string|max:100',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($request->hasFile('file_path')) {
            $this->fileUploadService->delete($slider->file_path);
            $uploaded = $this->fileUploadService->uploadImage($request->file('file_path'), 'sliders');
            $validated['file_path'] = $uploaded['path'];
        }

        $slider->update($validated);

        return back()->with('success', 'Slider berhasil diperbarui.');
    }

    public function destroy(Slider $slider)
    {
        $this->fileUploadService->delete($slider->file_path);
        $slider->delete();
        return back()->with('success', 'Slider berhasil dihapus.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sliders,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Slider::where('id', $id)->update(['urutan' => $index]);
        }

        return back()->with('success', 'Urutan slider diperbarui.');
    }
}
