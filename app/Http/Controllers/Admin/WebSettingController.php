<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebSettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        return Inertia::render('Admin/Web/Setting', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_sekolah'   => 'required|string|max:255',
            'tagline'        => 'nullable|string|max:255',
            'alamat'         => 'required|string',
            'telepon'        => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'facebook'       => 'nullable|string|max:255',
            'instagram'      => 'nullable|string|max:255',
            'youtube'        => 'nullable|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:255',
            'nip_kepala'     => 'nullable|string|max:30',
            'npsn'           => 'nullable|string|max:20',
            'akreditasi'     => 'nullable|string|max:5',
            'map_latitude'   => ['required', 'numeric', 'between:-90,90'],
            'map_longitude'  => ['required', 'numeric', 'between:-180,180'],
            'map_zoom'       => ['required', 'integer', 'between:1,19'],
            'map_label'      => ['required', 'string', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value, 'website');
        }

        return back()->with('success', 'Pengaturan website berhasil disimpan.');
    }
}
