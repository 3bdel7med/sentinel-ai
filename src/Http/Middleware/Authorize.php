<?php

namespace Abdelhmed\SentinelAi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Same idea as Telescope/Horizon:
 *  - if the app defined the "viewSentinel" gate, it decides;
 *  - otherwise the dashboard is only available in the "local" environment.
 */
class Authorize
{
    public function handle(Request $request, Closure $next)
    {
        $gate = (string) config('sentinel.gate', 'viewSentinel');

        if ($gate !== '' && Gate::has($gate)) {
            abort_unless(Gate::allows($gate), 403);
        } else {
            abort_unless(app()->environment('local'), 403);
        }

        return $next($request);
    }
}
