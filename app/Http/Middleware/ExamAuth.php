<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            session()->put('url.intended', $request->fullUrl());

            return redirect()->guest(route('ujian.login'));
        }

        if (! auth()->user()->hasRole('siswa')) {
            abort(403, 'Hanya siswa yang dapat mengakses halaman ujian.');
        }

        return $next($request);
    }
}
