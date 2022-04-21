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

    public function Seguimiento_Reclutamiento($Solicitud, $Comentario, $Codpers, $Iddynamics)
    {
        $params = array(
            array(
              ["PSOLICITUD"   => $Solicitud,    "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
              ["PCOMENTARIO"  => $Comentario,   "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
              ["PCODPERS"     => $Codpers,      "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
              ["PIDDYNAMICS"  => $Iddynamics,   "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
              ["PSQLCODE"     => "",            "TIPODATO"  => "NUMBER",    "TIPO" => "OUT"]
            )
        );

        $res = (new Db())->CursorProcedureNormal("DBAFISICC.PKG_RECLUTAMIENTO.SEGUIMIENTO_RECLUTAMIENTO", $params);

        return $res;
    }


}
