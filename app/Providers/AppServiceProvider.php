<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('production')) {
            $this->app->bind('path.public', function () {
                return base_path('../public_html');
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('ikutUjian', function (\App\Models\User $user, \App\Models\SesiUjian $sesi) {
            if (!$user->siswa) {
                return false;
            }
            return \App\Models\PesertaUjian::where('sesi_ujian_id', $sesi->id)
                ->where('siswa_id', $user->siswa->id)
                ->exists();
        });
    }
}
