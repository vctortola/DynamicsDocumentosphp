<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DgController;
use App\Http\Controllers\PersonalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



Route::group(['middleware' => ['VerifyIP']], function () {
  Route::Get('/verArchivo/{idarchivo}', [DgController::class, 'verArchivo']);
  Route::Get('/verFoto/{idarchivo}',    [DgController::class, 'verFoto']);

  Route::Get('/fotos/verArchivo/{idarchivo}', [DgController::class, 'verArchivo']);
  Route::Get('/fotos/verFoto/{idarchivo}',    [DgController::class, 'verFoto']);

  Route::Get('/fotos/Personal/obtenerSolicitudes',    [PersonalController::class, 'obtenerSolicitudes']);
  Route::Post('/fotos/Personal/seguimientoSolicitud',    [PersonalController::class, 'seguimientoSolicitud']);

  Route::Post('/fotos/api/DynamicsUG/People',    [PersonalController::class, 'People']);
});
