<?php

namespace App\Http\Middleware;

use Closure;

use App;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Ajency\User\Ajency\permissions\AccessPermission;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $output = new ConsoleOutput;

        // get the endpoint being accessed
        $endpoint = $request->route()->uri();

        // get the user accessing the api
        $userId = Auth::id();

        if((new AccessPermission)->checkAccessPermissions($endpoint,$userId))
            return $next($request);
        else {
            return response()->json(["status" => 403, "message" => 'Unauthorised access.']);
        }
    }
}
