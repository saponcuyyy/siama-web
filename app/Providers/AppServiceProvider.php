<?php

namespace App\Providers;

use App\Models\PesertaUjian;
use App\Models\SesiUjian;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('ikutUjian', function (User $user, SesiUjian $sesi) {
            if (! $user->siswa) {
                return false;
            }

            // Izinkan jika sudah ada di tabel peserta
            $isPeserta = PesertaUjian::where('sesi_ujian_id', $sesi->id)
                ->where('siswa_id', $user->siswa->id)
                ->exists();

            if ($isPeserta) {
                return true;
            }

            // Atau izinkan jika rombel siswa cocok dengan rombel sesi ujian
            if ($sesi->rombel_id && $sesi->rombel_id === $user->siswa->rombel_id) {
                return true;
            }

            return $sesi->rombels()->where('rombel.id', $user->siswa->rombel_id)->exists();
        });
    }
}
