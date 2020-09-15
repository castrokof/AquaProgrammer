<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
return $request->user();
});

Route::post('medidoresout','Admin\OrdenesmtlasignarController@medidorall');
Route::post('medidores','Admin\OrdenEjecutadaController@medidorejecutado');
Route::post('marcas','Admin\MarcasController@marcasall');
Route::post('loginMovil1','Seguridad\LoginController@loginMovil');
