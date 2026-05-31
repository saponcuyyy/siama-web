<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CronController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(
            $request->has('token') && hash_equals(config('app.cron_token'), $request->token),
            403,
            'Invalid token.'
        );

        Artisan::call('schedule:run');
        Cache::forever('cron_last_run', now()->timestamp);

        return 'Cron ran at ' . now();
    }
}
