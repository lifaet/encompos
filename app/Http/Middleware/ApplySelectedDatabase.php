<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ApplySelectedDatabase
{
    public function handle($request, Closure $next)
    {
        if ($request->session()->has('selected_db')) {
            $db = $request->session()->get('selected_db');

            // Change database connection
            config(['database.connections.mysql.database' => $db]);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Share with all Blade views
            View::share('current_db', $db);
        } else {
            View::share('current_db', null);
        }

        return $next($request);
    }
}
