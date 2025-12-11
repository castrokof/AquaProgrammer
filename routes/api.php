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

Route::middleware('auth.api.token')->group(function () {
    Route::post('medidoresout','OrdenesmtlasignarController@medidorall');
    Route::post('medidores','OrdenEjecutadaController@medidorejecutado');
    Route::post('marcas','MarcasController@marcasall');
    Route::post('medidorejecutado','OrdenesmtlasignarController@medidorejecutadosync');
});

// Ruta pública para login
Route::post('loginMovil1','Seguridad\LoginController@loginMovil');


