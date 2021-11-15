<?php

namespace App\Http\Controllers;

use Request;
use Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Response as Response2;

class DgController extends Controller
{
    public function __construct()
    {
        $this->apiDG = new Client(['base_uri' => env('DG_URI')]);
    }

    public function verArchivo($idarchivo){
        try {
          $idDecript = base64_decode($idarchivo);

          $ArchivoResponse = $this->apiDG->get('api/DG/Obtenerxarchivo?Idarchivo='.$idDecript, ['headers' => ['Content-type' => 'application/json', 'Token' => 'Eventos']]);
          $response = json_decode($ArchivoResponse->getBody(), true);

          return Response::make(base64_decode($response["Contenido"]), 200, [
              'Content-Type' => $response["MIME"],
              'Content-Disposition' => 'inline; filename="'. $response["Nombre"] . '"',
              'Cache-Control' => 'max-age=2628000'
          ]);
        } catch (\Exception $e) {
          return $e->getMessage();
        }
    }

    public function verFoto($idarchivo){
        try {
          $idDecript = base64_decode($idarchivo);

          $retval = (new DBA\PKG_PeopleController())->Foto($idDecript);

          return Response::make($retval[0]["FOTO"], 200, [
              'Content-Type' => "image/Jpeg",
              'Content-Disposition' => 'inline; filename="'. $idDecript . '".jpg',
              'Cache-Control' => 'max-age=2628000'
          ]);
        } catch (\Exception $e) {
          return $e->getMessage();
        }
    }


}
