<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckSchedulerHeartbeat
{
    public function handle(Request $request, Closure $next): Response
    {
        $lastRun = Cache::get('cron_last_run', 0);

        if ($lastRun < now()->subMinutes(5)->timestamp) {
            $lock = Cache::lock('cron_mimic_lock', 120);

            if ($lock->get()) {
                try {
                    Artisan::call('sesi:start-active', ['--quiet' => true]);
                    Artisan::call('sesi:close-expired', ['--quiet' => true]);

                    Cache::forever('cron_last_run', now()->timestamp);
                } finally {
                    $lock->release();
                }
            }
        }

        return $next($request);
    }
}
