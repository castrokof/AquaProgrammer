<?php

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

if(version_compare(PHP_VERSION, '7.2.0', '>=')) { error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); }




/* RUTAS IMAGENES TEXTO */

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('/', 'Admin\InicioController@index')->name('inicio');
Route::get('seguridad/login', 'Seguridad\LoginController@index')->name('login');
Route::post('seguridad/login', 'Seguridad\LoginController@login')->name('login_post');
Route::get('seguridad/logout', 'Seguridad\LoginController@logout')->name('logout');
Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'superadmin']], function () {
     
     
     /* RUTAS DEL MENU */
     Route::get('menu', 'MenuController@index')->name('menu');
     Route::get('menu/crear', 'MenuController@crear')->name('crear_menu');
     Route::get('menu/{id}/editar', 'MenuController@editar')->name('editar_menu');
     Route::put('menu/{id}', 'MenuController@actualizar')->name('actualizar_menu');
     Route::post('menu', 'MenuController@guardar')->name('guardar_menu');
     Route::post('menu/guardar-orden', 'MenuController@guardarOrden')->name('guardar_orden');
     Route::get('rol/{id}/elimniar', 'MenuController@eliminar')->name('eliminar_menu');
    
     /* RUTAS DEL ROL */
     Route::get('rol', 'RolController@index')->name('rol');
     Route::get('rol/crear', 'RolController@crear')->name('crear_rol');
     Route::post('rol', 'RolController@guardar')->name('guardar_rol');
     Route::get('rol/{id}/editar', 'RolController@editar')->name('editar_rol');
     Route::put('rol/{id}', 'RolController@actualizar')->name('actualizar_rol');
     Route::delete('rol/{id}', 'RolController@eliminar')->name('eliminar_rol');
    
     /* RUTAS DEL MENUROL */
     Route::get('menu-rol', 'MenuRolController@index')->name('menu_rol');
     Route::post('menu-rol', 'MenuRolController@guardar')->name('guardar_menu_rol');
     
     /* RUTAS DEL PERMISO */
     Route::get('permiso', 'PermisoController@index')->name('permiso');
     Route::get('permiso/crear', 'PermisoController@crear')->name('crear_permiso');
     Route::post('permiso', 'PermisoController@guardar')->name('guardar_permiso');
     Route::get('permiso/{id}/editar', 'PermisoController@editar')->name('editar_permiso');
     Route::put('permiso/{id}', 'PermisoController@actualizar')->name('actualizar_permiso');
     Route::delete('permiso/{id}', 'PermisoController@eliminar')->name('eliminar_permiso');
     
     /* RUTAS DEL PERMISOROL */
     Route::get('permiso-rol', 'PermisoRolController@index')->name('permiso_rol');
     Route::post('permiso-rol', 'PermisoRolController@guardar')->name('guardar_permiso_rol');


   
});


Route::group(['middleware' => ['auth']], function () {

Route::get('/tablero', 'AdminController@index')->name('tablero');

/* RUTAS DEL USUARIO */
Route::get('usuario', 'UsuarioController@index')->name('usuario')->middleware('superConsultor');
Route::get('usuario/crear', 'UsuarioController@crear')->name('crear_usuario')->middleware('superEditor');
Route::post('usuario', 'UsuarioController@guardar')->name('guardar_usuario')->middleware('superEditor');
Route::get('usuario/{id}/editar', 'UsuarioController@editar')->name('editar_usuario')->middleware('superEditor');
Route::get('usuario/{id}/password', 'UsuarioController@editarpassword')->name('editar_password')->middleware('superEditor');
Route::put('usuario/{id}', 'UsuarioController@actualizar')->name('actualizar_usuario')->middleware('superEditor');
Route::put('password/{id}', 'UsuarioController@actualizarpassword')->name('actualizar_password')->middleware('superEditor');
Route::get('usuario_pdf', 'UsuarioController@pdf')->name('usuario_pdf')->middleware('superConsultor')->middleware('superEditor');

/* RUTAS DEL USUARIO NO ADMIN PARA CONTRASEÑA */
Route::put('password1/{id}', 'UsuarioController@actualizarpassword1')->name('actualizar_password1');

/* RUTAS DEL ARCHIVO y ENTRADA */
Route::get('archivo', 'ArchivoController@index')->name('archivo')->middleware('superConsultor');
Route::post('guardar', 'Admin\EntradaController@guardar')->name('subir_archivo')->middleware('superEditor');
Route::get('sincronizar-entradas', 'Admin\EntradaController@sincronizarApi')->name('sincronizar.entradas')->middleware('superConsultor');
/* RUTAS DE ASIGNACION */
Route::get('asignacion', 'OrdenesmtlasignarController@index')->name('asignacion')->middleware('superEditor');
Route::post('asignacion_orden', 'OrdenesmtlasignarController@actualizar')->name('actualizar_asignacion')->middleware('superEditor');
Route::post('desasignacion_orden', 'OrdenesmtlasignarController@desasignar')->name('desasignar_asignacion')->middleware('superEditor');
Route::get('idDivision', 'OrdenesmtlasignarController@idDivisionss')->name('idDivisionsss')->middleware('superEditor');
/* DETALLE DE ORDENES */
Route::get('seguimiento', 'OrdenesmtlasignarController@seguimiento')->name('seguimiento')->middleware('superConsultor');
Route::post('seguimiento1', 'OrdenesmtlasignarController@seguimiento1')->name('seguimiento1')->middleware('superConsultor');
Route::get('orden-foto-url/{id}', 'OrdenesmtlasignarController@getFotoUrl')->name('fotos.url')->middleware('superConsultor');
Route::get('seguimiento/{id}', 'OrdenesmtlasignarController@fotos')->name('fotos')->middleware('superConsultor');
Route::get('seguimientodetalle/{id}', 'OrdenesmtlasignarController@detalle')->name('detalle_de_orden')->middleware('superConsultor');
Route::get('posicionamiento', 'OrdenesmtlasignarController@posicionamiento')->name('posicionamiento')->middleware('superConsultor');
Route::post('updateestado', 'OrdenesmtlasignarController@updateEstado')->name('updateestado');
Route::post('/actualizar-lectura', 'OrdenesmtlasignarController@actualizarLectura')->name('actualizar.lectura')->middleware('superConsultor');


Route::get('exportarciclo', 'OrdenesmtlasignarController@exportarCiclo')->name('exportarCiclo');
Route::post('exportarcicloe', 'OrdenesmtlasignarController@exportarExcel')->name('exportarCicloExcel');
Route::get('download-excel', 'OrdenesmtlasignarController@downloadExcel')->name('downloadExcel');


/* RUTAS DE MARCA */
Route::get('marca', 'MarcasController@index')->name('marca')->middleware('superConsultor');
Route::get('marca/crear', 'MarcasController@crear')->name('crear_marca')->middleware('superEditor');
Route::post('marca', 'MarcasController@guardar')->name('guardar_marca')->middleware('superEditor');
Route::get('marca/{id}/editar', 'MarcasController@editar')->name('editar_marca')->middleware('superEditor');
Route::put('marca/{id}', 'MarcasController@actualizar')->name('actualizar_marca')->middleware('superEditor');
    
   
});


Route::group(['middleware' => ['auth','superEditor']], function () {

/* ORDENES CRITICA */
Route::get('critica', 'OrdenesmtlasignarController@critica')->name('critica');
Route::get('criticaadd', 'OrdenesmtlasignarController@criticaadd')->name('criticaadd');
Route::get('generar_critica', 'OrdenesmtlasignarController@generarcritica')->name('generar_critica');
Route::post('adicionar_critica', 'OrdenesmtlasignarController@adicionarcritica')->name('adicionar_critica');
Route::post('eliminar_critica', 'OrdenesmtlasignarController@eliminarcritica')->name('eliminar_critica');

Route::get('export_factura', 'OrdenesmtlasignarController@factura')->name('export_factura');
Route::post('export_facturap', 'OrdenesmtlasignarController@facturap')->name('export_facturap');
Route::get('generar_factura', 'OrdenesmtlasignarController@generarfactura')->name('generar_factura');

});

Route::group(['middleware' => 'auth'], function () {

    Route::resource('macromedidores', 'MacromedidorController');
});

Route::group(['middleware' => 'auth'], function () {

    // Vista de criticas (supervisor ve lecturas con Estado=4)
    Route::get('revisiones/criticas', 'RevisionController@criticas')
        ->name('revisiones.criticas');

    // AJAX: marcar lecturas para revision (Coordenada = 'generar')
    Route::post('revisiones/adicionar-critica', 'RevisionController@adicionarcritica')
        ->name('revisiones.adicionar-critica');

    // AJAX: desmarcar lecturas (Coordenada = NULL)
    Route::post('revisiones/eliminar-critica', 'RevisionController@eliminarcritica')
        ->name('revisiones.eliminar-critica');

    // Generar ordenes de revision desde las marcadas
    Route::post('revisiones/generar', 'RevisionController@generar')
        ->name('revisiones.generar');

    // Reasignar revisor
    Route::post('revisiones/{id}/reasignar', 'RevisionController@reasignar')
        ->name('revisiones.reasignar');

    // Listado y detalle de revisiones
    Route::get('revisiones', 'RevisionController@index')
        ->name('revisiones.index');
    Route::get('revisiones/{id}', 'RevisionController@show')
        ->name('revisiones.show');
    Route::delete('revisiones/{id}', 'RevisionController@destroy')
        ->name('revisiones.destroy');

    // Gestion de listas parametros
    Route::get('listas-parametros', 'RevisionController@listas')
        ->name('listas.index');
});

// ─────────────────────────────────────────────────────────────────
// CONTROL DE CLIENTES / VERIFICACIÓN NUIP
// ─────────────────────────────────────────────────────────────────
Route::group(['middleware' => 'auth'], function () {

    // Listado de clientes
    Route::get('clientes', 'ClienteController@index')
        ->name('clientes.index');

    // Detalle / perfil del cliente
    Route::get('clientes/{id}', 'ClienteController@show')
        ->name('clientes.show');

    // Crear / actualizar perfil (formulario web)
    Route::post('clientes', 'ClienteController@store')
        ->name('clientes.store');

    // Agregar foto desde el panel web
    Route::post('clientes/{id}/foto', 'ClienteController@agregarFoto')
        ->name('clientes.foto.agregar');

    // Eliminar foto
    Route::delete('clientes/{clienteId}/foto/{fotoId}', 'ClienteController@eliminarFoto')
        ->name('clientes.foto.eliminar');
});

// ─────────────────────────────────────────────────────────────────
// MÓDULO DE FACTURACIÓN
// ─────────────────────────────────────────────────────────────────
Route::group(['middleware' => 'auth', 'prefix' => 'facturacion'], function () {

    // ── Períodos de Lectura ──────────────────────────────────────
    Route::get('periodos', 'PeriodoLecturaController@index')
        ->name('periodos.index');
    Route::post('periodos', 'PeriodoLecturaController@store')
        ->name('periodos.store');
    Route::put('periodos/{id}', 'PeriodoLecturaController@update')
        ->name('periodos.update');
    Route::post('periodos/{id}/generar-ordenes', 'PeriodoLecturaController@generarOrdenes')
        ->name('periodos.generar_ordenes');
    Route::post('periodos/{id}/estado', 'PeriodoLecturaController@cambiarEstado')
        ->name('periodos.estado');
    Route::get('periodos/{id}', 'PeriodoLecturaController@show')
        ->name('periodos.show');

    // ── Tarifas ──────────────────────────────────────────────────
    Route::get('tarifas', 'TarifaController@index')
        ->name('tarifas.index');
    Route::post('tarifas', 'TarifaController@store')
        ->name('tarifas.store');
    Route::get('tarifas/{id}/detalle', 'TarifaController@detalle')
        ->name('tarifas.detalle');
    Route::post('tarifas/{id}/cargos', 'TarifaController@guardarCargos')
        ->name('tarifas.cargos');
    Route::post('tarifas/{id}/rangos', 'TarifaController@guardarRangos')
        ->name('tarifas.rangos');
    Route::post('tarifas/{id}/activar', 'TarifaController@activar')
        ->name('tarifas.activar');

    // ── Facturas ─────────────────────────────────────────────────
    Route::get('facturas', 'FacturaController@index')
        ->name('facturas.index');
    Route::get('facturas/generar', 'FacturaController@generar')
        ->name('facturas.generar');
    Route::post('facturas/preview', 'FacturaController@preview')
        ->name('facturas.preview');
    Route::post('facturas/buscar-cliente', 'FacturaController@buscarCliente')
        ->name('facturas.buscar-cliente');
    Route::post('facturas', 'FacturaController@store')
        ->name('facturas.store');
    Route::get('facturas/{id}', 'FacturaController@show')
        ->name('facturas.show');
    Route::get('facturas/{id}/pdf', 'FacturaController@descargarPdf')
        ->name('facturas.pdf');
    Route::post('facturas/{id}/pago', 'FacturaController@registrarPago')
        ->name('facturas.pago');
    Route::post('facturas/{id}/anular', 'FacturaController@anular')
        ->name('facturas.anular');

    // ── Facturación Masiva ────────────────────────────────────────
    Route::get('facturasMasiva', 'FacturacionMasivaController@index')->name('facturas.masiva');
    Route::post('facturas/masiva/procesar', 'FacturacionMasivaController@procesar')->name('facturas.masiva.procesar');
    Route::post('facturas/masiva/criticas-confirmadas', 'FacturacionMasivaController@procesarCriticasConfirmadas')->name('facturas.masiva.criticas_confirmadas');
    Route::get('facturas/masiva/resumen', 'FacturacionMasivaController@resumen')->name('facturas.masiva.resumen');
    Route::get('facturas/masiva/lecturas-no-normales', 'FacturacionMasivaController@lecturasNoNormales')->name('facturas.masiva.lecturas-no-normales');
    Route::post('facturas/masiva/facturar-seleccionadas', 'FacturacionMasivaController@facturarSeleccionadas')->name('facturas.masiva.facturar-seleccionadas');
    Route::post('facturas/descargar-masivo', 'FacturacionMasivaController@descargarMasivo')->name('facturas.descargar_masivo');
     // Nueva ruta para exportar ZIP
    Route::post('facturas/exportar-masivo', 'FacturaController@exportarMasivo')->name('facturas.exportar-masivo');

   

      // ── Facturación Especial (No Normales) ────────────────────────
    Route::get('facturas-especial', 'FacturacionEspecialController@index')->name('facturacion.especial.index');
    Route::get('facturas-especial/resumen', 'FacturacionEspecialController@resumen')->name('facturacion.especial.resumen');
    Route::get('facturas-especial/lecturas', 'FacturacionEspecialController@getLecturas')->name('facturacion.especial.lecturas');
    
    Route::post('facturas-especial/facturar', 'FacturacionEspecialController@facturarSeleccionadas')->name('facturacion.especial.facturar-seleccionadas');


    // ── Pagos ─────────────────────────────────────────────────────
    Route::get('pagos', 'PagoController@index')
        ->name('pagos.index');

    // ── Otros Cobros ─────────────────────────────────────────────
    Route::get('otros-cobros', 'OtrosCobrosController@index')
        ->name('otros-cobros.index');
    Route::post('otros-cobros', 'OtrosCobrosController@store')
        ->name('otros-cobros.store');
    Route::post('otros-cobros/{id}/anular', 'OtrosCobrosController@anular')
        ->name('otros-cobros.anular');
    Route::get('otros-cobros/buscar-cliente', 'OtrosCobrosController@buscarCliente')
        ->name('otros-cobros.buscar-cliente');
});