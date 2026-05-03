<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return \Inertia\Inertia::render('Landing');
});

Route::get('/dashboard', function () {
    return \Inertia\Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
