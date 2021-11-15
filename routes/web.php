<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DgController;
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

Route::Get('/verArchivo/{idarchivo}', [DgController::class, 'verArchivo']);
Route::Get('/verFoto/{idarchivo}',    [DgController::class, 'verFoto']);

Route::Get('/fotos/verArchivo/{idarchivo}', [DgController::class, 'verArchivo']);
Route::Get('/fotos/verFoto/{idarchivo}',    [DgController::class, 'verFoto']);
