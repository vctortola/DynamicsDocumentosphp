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

          $nuevafoto = $this->resizeBase64andScaleWidth(base64_encode($retval[0]["FOTO"]),'image/jpg',200,180);

          return Response::make(base64_decode($nuevafoto), 200, [
              'Content-Type' => "image/Jpeg",
              'Content-Disposition' => 'inline; filename="'. $idDecript . '".jpg',
              'Cache-Control' => 'max-age=2628000'
          ]);
        } catch (\Exception $e) {
          return $e->getMessage();
        }
    }

    /*
  *
  * Los parametros son string base64, string mime, int alto deseado
  */
  public function resizeBase64andScaleWidth($base64img,$mimeimg,$newheight, $newwidth){

    // Get new sizes
    list($width, $height) = getimagesizefromstring(base64_decode($base64img));


    // Calcular nuevo ancho con la misma perdida o ganancia proporcial del alto (Escalar)
    //$porNewHeight = ($newheight * 100) / $height;
    //$newwidth =  (int)($width*($porNewHeight / 100));

    ob_start();
    $temp_thumb = imagecreatetruecolor($newwidth, $newheight);
    imagealphablending( $temp_thumb, false );
    imagesavealpha( $temp_thumb, true );

    $source = imagecreatefromstring(base64_decode($base64img));

    // Resize
    imagecopyresized($temp_thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);


    switch ($mimeimg) {
      case 'png':
      case 'image/png':
      case 'PNG':
      case 'IMAGE/PNG':
        imagepng($temp_thumb, null);
        break;
      case 'jpg':
      case 'image/jpg':
      case 'jpeg':
      case 'JPEG':
      case 'JPG':
      case 'IMAGE/JPG':
      case 'IMAGE/JPEG':
      case 'image/jpeg':
        imagejpeg($temp_thumb, null);
        break;
      case 'image/gif':
      case 'gif':
      case 'GIT':
      case 'IMAGE/GIF':
        imagegif($temp_thumb, null);
    }

    $stream = ob_get_clean();
    $newB64 = base64_encode($stream);

    imagedestroy($temp_thumb);
    imagedestroy($source);

    return $newB64;

  }



}
