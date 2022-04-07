<?php

namespace App\Http\Controllers\DBA;

use App\DB\Db;

class PKG_ReclutamientoController extends Controller
{
    public function __construct()
    {
    }

    public function Obtener_Solic_Reclutamiento()
    {
        $params = array(
            array(
              ["RETVAL"  => "",     "TIPODATO"  => "CURSOR",    "TIPO" => "OUT"]
            )
        );

        $res = (new Db())->CursorProcedureNormal("DBAFISICC.PKG_RECLUTAMIENTO.OBTENER_SOLIC_RECLUTAMIENTO", $params);

        return $res["RETVAL"];
    }

}
