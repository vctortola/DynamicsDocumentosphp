<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\DBAMYSQL as MYSQL;

class VerifyIP
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

        $IP = Request::ip();

        $permiso = (new MYSQL\PermisosController())->Permiso($IP);
        if($permiso > 0){
          return $next($request);
        }else{
          return response("No tiene permisos", 403);
        }
    }
}
