<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ClienteFoto;
use App\Models\ClienteSerie;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\Request;

/**
 * Controller API – Verificación de clientes / captura de NUIP desde la app móvil.
 *
 * Endpoints:
 *   GET  /api/cliente?api_token=xxx&suscriptor=xxx   → Consultar perfil de un suscriptor
 *   POST /api/cliente                                → Crear / actualizar perfil + fotos
 */
class ClienteApiController extends Controller
{
    // ────────────────────────────────────────────────
    // GET /api/cliente?api_token=xxx&suscriptor=xxx
    // ────────────────────────────────────────────────

    /**
     * Consulta el perfil existente de un suscriptor.
     * Útil para que la app prefill el formulario con datos ya registrados.
     */
    public function consultar(Request $request)
    {
        $user = $this->_validarToken($request);
        if (!$user) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        $suscriptor = $request->input('suscriptor');
        if (!$suscriptor) {
            return response()->json(['error' => 'Parámetro suscriptor requerido'], 422);
        }

        $cliente = Cliente::with('fotos')->where('suscriptor', $suscriptor)->first();

        if (!$cliente) {
            return response()->json(['encontrado' => false, 'cliente' => null]);
        }

        return response()->json([
            'encontrado' => true,
            'cliente'    => $cliente->toApiArray(),
        ]);
    }

    // ────────────────────────────────────────────────
    // POST /api/cliente
    // ────────────────────────────────────────────────

    /**
     * Crea o actualiza el perfil del cliente capturado en campo.
     *
     * Parámetros (multipart/form-data):
     *   api_token           string  requerido
     *   suscriptor          string  requerido
     *   nuip                string  opcional
     *   tipo_documento      string  opcional  (CC, TI, CE, PA…)
     *   nombre              string  opcional
     *   apellido            string  opcional
     *   telefono            string  opcional
     *   direccion           string  opcional
     *   serie_medidor       string  opcional  – número de serie del medidor
     *   periodo             string  opcional  – período YYYYMM para trazabilidad (ej: 202503)
     *   orden_ejecutada_id  int     opcional  – vincula la captura a la orden ejecutada
     *
     *   foto_medidor        file    opcional  – foto del medidor (campo dedicado)
     *   foto_predio         file    opcional  – foto del predio / fachada (campo dedicado)
     *   fotos[]             file    opcional  – fotos adicionales genéricas
     *   tipos[]             string  opcional  – tipo por cada foto adicional: documento | medidor | predio
     */
    public function guardar(Request $request)
    {
        $user = $this->_validarToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Token inválido'], 401);
        }

        $suscriptor = $request->input('suscriptor');
        if (!$suscriptor) {
            return response()->json(['success' => false, 'message' => 'suscriptor requerido'], 422);
        }

        // Crear o actualizar perfil
        $cliente = Cliente::upsertDesdeDatos([
            'suscriptor'     => $suscriptor,
            'nuip'           => $request->input('nuip'),
            'tipo_documento' => $request->input('tipo_documento'),
            'nombre'         => $request->input('nombre'),
            'apellido'       => $request->input('apellido'),
            'telefono'       => $request->input('telefono'),
            'direccion'      => $request->input('direccion'),
            'serie_medidor'  => $request->input('serie_medidor'),
        ]);

        $ordenEjecutadaId = $request->input('orden_ejecutada_id');
        $periodo          = $request->input('periodo') ?: date('Ym');

        // Registrar serie en historial si viene informada
        if ($request->filled('serie_medidor')) {
            ClienteSerie::registrar($cliente->id, $request->input('serie_medidor'), $periodo, $ordenEjecutadaId ?: null);
        }

        $directorio = public_path('uploads/clientes');
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        // Foto del medidor (campo dedicado)
        if ($request->hasFile('foto_medidor') && $request->file('foto_medidor')->isValid()) {
            $this->_guardarFotoApi($request->file('foto_medidor'), $cliente, 'medidor', $ordenEjecutadaId, $directorio);
        }

        // Foto del predio (campo dedicado)
        if ($request->hasFile('foto_predio') && $request->file('foto_predio')->isValid()) {
            $this->_guardarFotoApi($request->file('foto_predio'), $cliente, 'predio', $ordenEjecutadaId, $directorio);
        }

        // Fotos adicionales genéricas
        if ($request->hasFile('fotos')) {
            $tipos = $request->input('tipos', []);
            foreach ($request->file('fotos') as $idx => $foto) {
                if (!$foto->isValid()) {
                    continue;
                }
                $tipo = isset($tipos[$idx]) ? $tipos[$idx] : 'documento';
                $this->_guardarFotoApi($foto, $cliente, $tipo, $ordenEjecutadaId, $directorio);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil de cliente guardado',
            'cliente' => $cliente->fresh('fotos')->toApiArray(),
        ]);
    }

    private function _guardarFotoApi($archivo, Cliente $cliente, string $tipo, $ordenEjecutadaId, string $directorio): void
    {
        $nombre = 'cli_' . $cliente->id . '_' . time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($directorio, $nombre);

        ClienteFoto::create([
            'cliente_id'         => $cliente->id,
            'ruta_foto'          => 'uploads/clientes/' . $nombre,
            'tipo'               => $tipo,
            'orden_ejecutada_id' => $ordenEjecutadaId ?: null,
        ]);
    }

    // ────────────────────────────────────────────────
    // HELPER PRIVADO
    // ────────────────────────────────────────────────

    private function _validarToken(Request $request): ?Usuario
    {
        $token = $request->input('api_token');
        if (!$token) {
            return null;
        }
        return Usuario::where('api_token', $token)->first();
    }
}
