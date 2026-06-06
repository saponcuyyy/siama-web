<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class JadwalController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Akademik/Jadwal/Index');
    }
}
