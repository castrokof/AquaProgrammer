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

    // GET  /api/ordenesMacro?api_token=xxx
    Route::get('ordenesMacro', 'Api\MacromedidorApiController@ordenesMacro');

    // Subir lectura con fotos (multipart)
    // POST /api/macromedidoresMovil
    Route::post('macromedidoresMovil', 'Api\MacromedidorApiController@enviarMacro');
    // Descargar ordenes de revision del usuario
    // GET  /api/ordenesRevision?api_token=xxx
    Route::get('ordenesRevision', 'Api\RevisionApiController@ordenesRevision');

    // Subir revision ejecutada (multipart con fotos, firma, censo)
    // POST /api/revisionesMovilV2
    Route::post('revisionesMovilV2', 'Api\RevisionApiController@enviarRevisionV2');

    // Descargar listas de parametros (dropdowns de la app)
    // GET  /api/listasParametros?api_token=xxx
    Route::get('listasParametros', 'Api\RevisionApiController@listasParametros');

    // ── CLIENTES / VERIFICACIÓN NUIP ──────────────────────────────
    // GET  /api/cliente?api_token=xxx&suscriptor=xxx   → Consultar perfil
    Route::get('cliente', 'Api\ClienteApiController@consultar');

    // POST /api/cliente   → Crear / actualizar perfil + fotos desde la app
    Route::post('cliente', 'Api\ClienteApiController@guardar');





});

// Ruta pública para login
Route::post('loginMovil1','Seguridad\LoginController@loginMovil');

 



