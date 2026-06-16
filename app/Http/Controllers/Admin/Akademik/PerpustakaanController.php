<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PerpustakaanController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Akademik/Perpustakaan/Index');
    }
}
