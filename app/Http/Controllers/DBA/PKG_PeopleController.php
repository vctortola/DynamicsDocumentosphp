<?php

namespace App\Http\Controllers\DBA;

use App\DB\Db;

class PKG_PeopleController extends Controller
{
    public function __construct()
    {
    }

    public function Foto($Correlativo)
    {
        $params = array(
            array(
              ["PCORRELATIVO"  => $Correlativo, "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
              ["RETVAL"  => "",     "TIPODATO"  => "CURSOR",    "TIPO" => "OUT"]
            )
        );

        $res = (new Db())->CursorProcedureNormal("DBAFISICC.PKG_PEOPLE.FOTO", $params);

        return $res["RETVAL"];
    }
}
