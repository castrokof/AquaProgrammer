<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Macromedidor;
use App\Models\MacroLectura;
use App\Models\MacroLecturaFoto;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Controller API - Endpoints para la app Android.
 *
 * Endpoints:
 *   GET  /api/ordenesMacro?api_token=xxx       -> Todos los macros del usuario (siempre activos)
 *   POST /api/macromedidoresMovil               -> Registrar nueva lectura diaria
 */
class MacromedidorApiController extends Controller
{
    /**
     * GET /api/ordenesMacro?api_token=xxx
     *
     * Devuelve todos los macromedidores asignados al usuario.
     * Siempre con estado PENDIENTE para que la app permita leer diariamente.
     */
    public function ordenesMacro(Request $request)
    {
        $user = $request->user();

        $macros = Macromedidor::with('ultimaLectura')
            ->where('usuario_id', $user->id)
            ->orderBy('codigo_macro')
            ->get();

        $resultado = $macros->map(function ($macro) {
            return $macro->toApiArray();
        });

        return response()->json($resultado);
    }

    /**
     * POST /api/macromedidoresMovil
     *
     * Registra una nueva lectura diaria del macromedidor.
     * Crea un registro en macro_lecturas (historial) y actualiza
     * lectura_anterior en macromedidores para la siguiente lectura.
     *
     * Campos multipart/form-data:
     *   api_token      string   requerido
     *   id_orden       int      requerido (= macromedidores.id)
     *   lectura_actual string   requerido
     *   observacion    string   opcional
     *   gps_latitud    double   opcional
     *   gps_longitud   double   opcional
     *   fotos[]        file     opcional
     */
    public function enviarMacro(Request $request)
    {
        // 1. Validar token
        $apiToken = $request->input('api_token');
        if (!$apiToken) {
            return response()->json(['success' => false, 'message' => 'api_token requerido'], 401);
        }

        $user = Usuario::where('api_token', $apiToken)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Token invalido'], 401);
        }

        // 2. Buscar el macromedidor
        $idOrden = $request->input('id_orden');
        $macro   = Macromedidor::where('id', $idOrden)
            ->where('usuario_id', $user->id)
            ->first();

        if (!$macro) {
            return response()->json(['success' => false, 'message' => 'Macromedidor no encontrado o no pertenece al usuario'], 404);
        }

        // 3. Lectura_anterior = última lectura registrada o valor inicial del macro
        $ultimaLectura   = MacroLectura::where('macromedidor_id', $macro->id)->latest('fecha_lectura')->first();
        $lecturaAnterior = $ultimaLectura ? $ultimaLectura->lectura_actual : $macro->lectura_anterior;

        $lecturaActual = intval($request->input('lectura_actual'));
        $consumo       = $lecturaActual - $lecturaAnterior;

        // 4. Crear registro de lectura en historial
        $lectura = MacroLectura::create([
            'macromedidor_id'  => $macro->id,
            'usuario_id'       => $user->id,
            'lectura_anterior'  => $lecturaAnterior,
            'lectura_actual'   => $lecturaActual,
            'consumo'          => $consumo,
            'observacion'      => $request->input('observacion'),
            'gps_latitud'      => $request->input('gps_latitud'),
            'gps_longitud'     => $request->input('gps_longitud'),
            'fecha_lectura'    => Carbon::now(),
            'sincronizado'     => true,
        ]);

        // 5. Actualizar lectura_anterior del macro para la próxima lectura
        $macro->lectura_anterior = $lecturaActual;
        $macro->save();

        // 6. Guardar fotos en macro_lectura_fotos
        if ($request->hasFile('fotos')) {
            $directorio = public_path('uploads/macros');
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }
            foreach ($request->file('fotos') as $foto) {
                if ($foto->isValid()) {
                    $nombre = 'macro_' . $macro->id . '_' . time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                    $foto->move($directorio, $nombre);
                    MacroLecturaFoto::create([
                        'macro_lectura_id' => $lectura->id,
                        'ruta_foto'        => 'uploads/macros/' . $nombre,
                    ]);
                }
            }
        }

        return response()->json([
            'success'          => true,
            'message'          => 'Lectura registrada correctamente',
            'id'               => $lectura->id,
            'consumo'          => $consumo,
            'lectura_anterior' => $lecturaAnterior,
        ]);
    }
}
