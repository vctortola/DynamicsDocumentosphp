<?php

namespace App\Http\Controllers\DBA;

use App\DB\Db;

class PKG_PersonalController extends Controller
{
    public function __construct()
    {
    }

    public function Carga_Administrativo($Correlativo_In, $Nombre1, $Nombre2, $Apellido1, $Apellido2, $Decasada, $Depto_Nac, $Muni_Nac, $Cedula, $Nacionalidad, $Pais, $Depto_Id, $Muni_Id, $Dpi, $Pasaporte, $Pais_Pas, $Fechanac, $Sexo, $Estadocivil, $Codigo, $Puesto, $Departamento, $Usuario, $Fechadpi){
      $params = array(
          array(
            ["PCORRELATIVO_IN"  => $Correlativo_In, "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
            ["PNOMBRE1"         => $Nombre1,        "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PNOMBRE2"         => $Nombre2,        "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PAPELLIDO1"       => $Apellido1,      "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PAPELLIDO2"       => $Apellido2,      "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PDECASADA"        => $Decasada,       "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PDEPTO_NAC"       => $Depto_Nac,      "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
            ["PMUNI_NAC"        => $Muni_Nac,       "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
            ["PCEDULA"          => $Cedula,         "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PNACIONALIDAD"    => $Nacionalidad,   "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PPAIS"            => $Pais,           "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
            ["PDEPTO_ID"        => $Depto_Id,       "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
            ["PMUNI_ID"         => $Muni_Id,        "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
            ["PDPI"             => $Dpi,            "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PPASAPORTE"       => $Pasaporte,      "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PPAIS_PAS"        => $Pais_Pas,       "TIPODATO"  => "NUMBER",    "TIPO" => "IN"],
            ["PFECHANAC"        => $Fechanac,       "TIPODATO"  => "DATE",      "TIPO" => "IN"],
            ["PSEXO"            => $Sexo,           "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PESTADOCIVIL"     => $Estadocivil,    "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PCODIGO"          => $Codigo,         "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PPUESTO"          => $Puesto,         "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PDEPARTAMENTO"    => $Departamento,   "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PUSUARIO"         => $Usuario,        "TIPODATO"  => "VARCHAR2",  "TIPO" => "IN"],
            ["PFECHADPI"        => $Fechadpi,       "TIPODATO"  => "DATE",      "TIPO" => "IN"],
            ["PCORRELATIVO"     => "",              "TIPODATO"  => "NUMBER",    "TIPO" => "OUT"],
            ["PCODPERS"         => "",              "TIPODATO"  => "VARCHAR2",  "TIPO" => "OUT"]
          )
      );

      $res = (new Db())->CursorProcedureNormal("DBAFISICC.PKG_PERSONAL.CARGA_ADMINISTRATIVO", $params);

      return $res;
    }
}
