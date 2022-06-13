<?php

namespace App\Http\Controllers;

use Request;
use Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Response as Response2;
use Illuminate\Http\Request as Request2;
use DateTime;
use DateTimeZone;

class PersonalController extends Controller
{
    public function __construct()
    {
        $this->apiDG = new Client(['base_uri' => env('ADMISIONES_URI')]);
        $this->token = "3580A2BE965382475E5F0253D8247C5A3197FFE3BACEC6B4BA513F9D1FDD5CD403A39D9DBC10C54A1ECBA6DA646067951A20834D240ED0B9A9E6359CAF732FA6";
    }

    public function obtenerSolicitudes(){
        try {

          $retval = (new DBA\PKG_ReclutamientoController())->Obtener_Solic_Reclutamiento();

          for($i = 0; $i < Count($retval); $i++){
            $ArchivoResponse = $this->apiDG->get('api/listado?usuario='. $retval[$i]["CORREO"], ['headers' => ['Content-type' => 'application/json', 'Token' => $this->token]]);
            $response = json_decode($ArchivoResponse->getBody(), true);

            $retval[$i]["DATOS"] =  $response;
          }

          return response()->json($retval, 200);
        } catch (\Exception $e) {

          return response()->json($e->getMessage(), 200);
        }
    }

    public function seguimientoSolicitud(Request2 $request){

      $solicitud = $request->Solicitud;
      $codpers   = $request->Codpers;
      $iddynamics = $request->Iddynamics;
      $Respuesta = ["Exito" => "", "Error" => ""];
      try {
          $respuesta = (new DBA\PKG_ReclutamientoController())->Seguimiento_Reclutamiento($solicitud, "Persona contratada " . $iddynamics, $codpers, $iddynamics);

          if (isset($respuesta["ERROR"])){
            $Respuesta["Error"] = $respuesta["ERROR"];
            return $Respuesta;
          }else{
            if($respuesta["PSQLCODE"] == "0"){
              $Respuesta["Exito"] = "Seguimiento dado exitosamente";
              return $Respuesta;
            }else {
              $Respuesta["Error"] = "Ocurrió un error al darle seguimiento Ref. ". $respuesta["PSQLCODE"];
              return $Respuesta;
            }
          }

      } catch (\Exception $e) {
          $Respuesta["Error"] = "Ocurrió un error al darle seguimiento Ref. " . $e->getMessage();
          return $Respuesta;
      }

    }


    public function People(Request2 $request){

      $sfecha = $request->FechaNac;

      $fechanac = new DateTime($sfecha);
      $fechanac = $fechanac->setTimezone(new DateTimeZone('America/Guatemala'));
      $fechanacdate = $fechanac->format('Y-m-d');

      $retval = (new DBA\PKG_PersonalController())->Carga_Administrativo(null, $request->Nombre1, $request->Nombre2, $request->Apellido1, $request->Apellido2, $request->DeCasada, null, null, $request->Cedula, $request->Nacionalidad, $request->Pais, $request->Depto_ID, $request->Muni_ID, $request->DPI, $request->Pasaporte, $request->Pais_PAS, $fechanacdate, $request->Sexo, $request->EstadoCivil, $request->Codigo, $request->Puesto, $request->Departamento, null, null);

      $correlativo = $retval["PCORRELATIVO"];
      
      return $correlativo;
    }
}
