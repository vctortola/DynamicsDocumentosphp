<?php

namespace App\Http\Controllers;

use Request;
use Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Response as Response2;

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

}
