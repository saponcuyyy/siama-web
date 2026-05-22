<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            session()->put('url.intended', $request->fullUrl());
            return redirect()->guest(route('login', ['context' => 'ujian']));
        }

        return $next($request);
    }
}
