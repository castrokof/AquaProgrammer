<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteFoto;
use App\Models\ClienteSerie;
use App\Models\Admin\Ordenesmtl;
use App\Models\Estrato;
use Illuminate\Http\Request;

/**
 * Controller WEB – Control de Clientes / Verificación NUIP.
 *
 * Rutas:
 *   GET  /clientes                       → index()        Listado con búsqueda
 *   GET  /clientes/{id}                  → show()         Perfil + historial + series
 *   POST /clientes                       → store()        Crear / actualizar perfil
 *   POST /clientes/{id}/foto             → agregarFoto()  Subir foto desde el panel web
 *   DELETE /clientes/{id}/foto/{fid}     → eliminarFoto() Eliminar foto
 */
class ClienteController extends Controller
{
    // ────────────────────────────────────────────────
    // LISTADO
    // ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Cliente::with('fotos');

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }
        if ($request->filled('id_ruta')) {
            $query->where('id_ruta', $request->id_ruta);
        }
        if ($request->filled('tiene_medidor')) {
            $query->where('tiene_medidor', $request->tiene_medidor === '1');
        }
        if ($request->filled('ciclo')) {
            $suscriptores = Ordenesmtl::where('Ciclo', $request->ciclo)->distinct()->pluck('Suscriptor');
            $query->whereIn('suscriptor', $suscriptores);
        }

        $clientes = $query->orderBy('updated_at', 'desc')->get();
        $estratos = Estrato::where('activo', true)->orderBy('numero')->get();
        $rutas    = Cliente::whereNotNull('id_ruta')->distinct()->orderBy('id_ruta')->pluck('id_ruta');
        $ciclos   = Ordenesmtl::whereNotNull('Ciclo')->where('Ciclo', '!=', '')->distinct()->orderBy('Ciclo')->pluck('Ciclo');

        return view('clientes.index', compact('clientes', 'estratos', 'rutas', 'ciclos'));
    }

    // ────────────────────────────────────────────────
    // DETALLE / PERFIL
    // ────────────────────────────────────────────────

    public function show($id)
    {
        $cliente = Cliente::with('fotos', 'series', 'estrato')->findOrFail($id);

        // Historial de lecturas/órdenes del suscriptor
        // Ordena por fecha real de ejecución (si existe) y luego por período
        $ordenes = Ordenesmtl::where('Suscriptor', $cliente->suscriptor)
            ->orderByRaw("CASE WHEN fecha_de_ejecucion IS NULL OR fecha_de_ejecucion = '0000-00-00 00:00:00' THEN '1900-01-01' ELSE fecha_de_ejecucion END DESC")
            ->orderBy('Periodo', 'desc')
            ->limit(36)
            ->get();

        $estratos = Estrato::where('activo', true)->orderBy('numero')->get();

        return view('clientes.show', compact('cliente', 'ordenes', 'estratos'));
    }

    /**
     * Panel lateral rápido: retorna fragmento HTML sin layout.
     * Usado por el drawer de derecha en el listado de clientes.
     */
    public function showPanel($id)
    {
        $cliente = Cliente::with('fotos', 'estrato')->findOrFail($id);

        $ordenes = Ordenesmtl::where('Suscriptor', $cliente->suscriptor)
            ->orderByRaw("CASE WHEN fecha_de_ejecucion IS NULL OR fecha_de_ejecucion = '0000-00-00 00:00:00' THEN '1900-01-01' ELSE fecha_de_ejecucion END DESC")
            ->orderBy('Periodo', 'desc')
            ->limit(6)
            ->get();

        return view('clientes._detalle_panel', compact('cliente', 'ordenes'));
    }

    // ────────────────────────────────────────────────
    // CREAR / ACTUALIZAR PERFIL (formulario web)
    // ────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->validate($request, [
            'suscriptor'     => 'required|string|max:50',
            'nuip'           => 'nullable|string|max:30',
            'tipo_documento' => 'nullable|string|max:10',
            'nombre'         => 'nullable|string|max:150',
            'apellido'       => 'nullable|string|max:150',
            'telefono'       => 'nullable|string|max:30',
            'direccion'      => 'nullable|string|max:255',
            'serie_medidor'  => 'nullable|string|max:100',
            'foto'           => 'nullable|image|max:5120',
            'tipo_foto'      => 'nullable|string|in:documento,medidor,predio',
            // Facturación
            'estrato_id'     => 'nullable|exists:estratos,id',
            'servicios'      => 'nullable|in:AG,AL,AG-AL',
            'tipo_uso'       => 'nullable|in:RESIDENCIAL,COMERCIAL,INDUSTRIAL,OFICIAL',
            'tiene_medidor'  => 'nullable',
            'sector'         => 'nullable|string|max:100',
            'ruta'           => 'nullable|string|max:100',
            'id_ruta'        => 'nullable|integer|min:1',
            'consecutivo'    => 'nullable|integer|min:1',
            'estado'         => 'nullable|in:ACTIVO,SUSPENDIDO,CORTADO,INACTIVO',
        ]);

        $cliente = Cliente::upsertDesdeDatos($request->only([
            'suscriptor', 'nuip', 'tipo_documento', 'nombre', 'apellido',
            'telefono', 'direccion', 'serie_medidor',
        ]));

        // Guardar campos de facturación si vienen en el request
        $billing = array_filter([
            'estrato_id'    => $request->input('estrato_id'),
            'servicios'     => $request->input('servicios'),
            'tipo_uso'      => $request->input('tipo_uso'),
            'sector'        => $request->input('sector'),
            'ruta'          => $request->input('ruta'),
            'id_ruta'       => $request->input('id_ruta') ?: null,
            'consecutivo'   => $request->input('consecutivo') ?: null,
            'estado'        => $request->input('estado') ?: 'ACTIVO',
        ], fn($v) => $v !== null && $v !== '');
        $billing['tiene_medidor'] = $request->input('tiene_medidor', '1') === '0' ? false : true;
        $cliente->update($billing);

        // Registrar serie en el historial si viene informada
        if ($request->filled('serie_medidor')) {
            ClienteSerie::registrar($cliente->id, $request->input('serie_medidor'), date('Ym'));
        }

        // Guardar foto si viene adjunta
        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
            $this->_guardarFoto($request->file('foto'), $cliente, $request->input('tipo_foto', 'medidor'));
        }

        return redirect()->route('clientes.show', $cliente->id)
            ->with('success', 'Perfil de cliente guardado correctamente.');
    }

    // ────────────────────────────────────────────────
    // AGREGAR FOTO DESDE EL PANEL WEB
    // ────────────────────────────────────────────────

    public function agregarFoto(Request $request, $id)
    {
        $this->validate($request, [
            'foto'      => 'required|image|max:8192',
            'tipo_foto' => 'nullable|string|in:documento,medidor,predio',
        ]);

        $cliente = Cliente::findOrFail($id);

        if ($request->file('foto')->isValid()) {
            $this->_guardarFoto($request->file('foto'), $cliente, $request->input('tipo_foto', 'medidor'));
        }

        return redirect()->route('clientes.show', $cliente->id)
            ->with('success', 'Foto agregada correctamente.');
    }

    // ────────────────────────────────────────────────
    // ELIMINAR FOTO
    // ────────────────────────────────────────────────

    public function eliminarFoto($clienteId, $fotoId)
    {
        $foto = ClienteFoto::where('cliente_id', $clienteId)->findOrFail($fotoId);

        if (file_exists(public_path($foto->ruta_foto))) {
            unlink(public_path($foto->ruta_foto));
        }

        $foto->delete();

        return redirect()->route('clientes.show', $clienteId)
            ->with('success', 'Foto eliminada.');
    }

    // ────────────────────────────────────────────────
    // HELPER PRIVADO
    // ────────────────────────────────────────────────

    private function _guardarFoto($archivo, Cliente $cliente, string $tipo)
    {
        $directorio = public_path('uploads/clientes');
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $nombre = 'cli_' . $cliente->id . '_' . time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($directorio, $nombre);

        ClienteFoto::create([
            'cliente_id' => $cliente->id,
            'ruta_foto'  => 'uploads/clientes/' . $nombre,
            'tipo'       => $tipo,
        ]);
    }
}
