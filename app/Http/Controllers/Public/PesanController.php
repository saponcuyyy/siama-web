<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class PesanController extends Controller
{
    public function store(Request $request)
    {
        $executed = RateLimiter::attempt(
            'send-message:'.$request->ip(),
            $perHour = 3,
            function () use ($request) {
                $validated = $request->validate([
                    'nama' => 'required|string|max:100',
                    'email' => 'required|email|max:100',
                    'subjek' => 'required|string|max:200',
                    'pesan' => 'required|string|max:2000',
                ]);

                $validated['ip_address'] = $request->ip();
                $validated['status'] = 'belum_dibaca';

                Pesan::create($validated);
            },
            60 * 60 // 1 hour
        );

        if (! $executed) {
            return back()->with('error', 'Terlalu banyak mencoba. Silakan coba lagi nanti (Maks 3 pesan/jam).');
        }

        return back()->with('success', 'Pesan Anda berhasil dikirim.');
    }
}
