<?php

namespace App\Http\Controllers\DBAMYSQL;

use Illuminate\Support\Facades\DB;

class PermisosController extends Controller
{
    public function __construct()
    {
    }

    public function Permiso($ip)
    {
      $permiso = DB::table('permisosip')
              ->where("ip", $ip)
              ->count();
      return $permiso;
    }
}
