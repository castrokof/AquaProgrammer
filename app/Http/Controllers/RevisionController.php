<?php

namespace App\Http\Controllers;

use App\Models\OrdenRevision;
use App\Models\ListaParametro;
use App\Models\Seguridad\Usuario;
use App\Models\Admin\Ordenesmtl;
use App\Models\PeriodoLectura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller WEB - Revisiones.
 *
 * Flujo real:
 * 1. /revisiones/criticas                 -> Supervisor ve lecturas con Estado=4 (criticas)
 * 2. AJAX adicionarcritica/eliminarcritica -> Marca/desmarca con Coordenada='generar'
 * 3. POST /revisiones/generar             -> Genera ordenes desde las marcadas (Coordenada='generar')
 * 4. /revisiones                          -> Listado de ordenes generadas
 * 5. /revisiones/{id}                     -> Detalle con resultado del wizard
 */
class RevisionController extends Controller
{
    // ========================================
    // TABLERO DE CONTROL
    // ========================================

    /**
     * GET /revisiones/tablero
     */
    public function tablero(Request $request)
    {
        $query = OrdenRevision::query();

        if ($request->filled('motivo'))      $query->where('motivo_revision', $request->motivo);
        if ($request->filled('usuario_id'))  $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('fecha_desde')) $query->whereDate('created_at', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta')) $query->whereDate('created_at', '<=', $request->fecha_hasta);

        // ── KPIs globales ──────────────────────────────────────────────────────
        $total      = (clone $query)->count();
        $ejecutadas = (clone $query)->where('estado_orden', 'EJECUTADO')->count();
        $pendientes = (clone $query)->where('estado_orden', 'PENDIENTE')->count();
        $porcentaje = $total > 0 ? round(($ejecutadas / $total) * 100, 1) : 0;

        // ── Resumen por revisor ────────────────────────────────────────────────
        $porUsuario = (clone $query)
            ->select(
                'usuario_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN estado_orden='EJECUTADO' THEN 1 ELSE 0 END) as ejecutadas"),
                DB::raw("SUM(CASE WHEN estado_orden='PENDIENTE' THEN 1 ELSE 0 END) as pendientes"),
                DB::raw('SUM((SELECT COUNT(*) FROM revision_fotos rf WHERE rf.revision_id = ordenes_revision.id)) as total_fotos')
            )
            ->with('usuario')
            ->groupBy('usuario_id')
            ->orderByDesc('total')
            ->get();

        // ── Resumen por motivo ─────────────────────────────────────────────────
        $porMotivo = (clone $query)
            ->select(
                'motivo_revision',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN estado_orden='EJECUTADO' THEN 1 ELSE 0 END) as ejecutadas"),
                DB::raw("SUM(CASE WHEN estado_orden='PENDIENTE' THEN 1 ELSE 0 END) as pendientes")
            )
            ->groupBy('motivo_revision')
            ->orderByDesc('total')
            ->get();

        // ── Resumen por estado_acometida (campo de ejecución) ─────────────────
        $porAcometida = OrdenRevision::where('estado_orden', 'EJECUTADO')
            ->when($request->filled('motivo'),      fn($q) => $q->where('motivo_revision', $request->motivo))
            ->when($request->filled('usuario_id'),  fn($q) => $q->where('usuario_id', $request->usuario_id))
            ->when($request->filled('fecha_desde'), fn($q) => $q->whereDate('created_at', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->fecha_hasta))
            ->select('estado_acometida', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('estado_acometida')
            ->groupBy('estado_acometida')
            ->orderByDesc('cnt')
            ->get();

        $usuarios = Usuario::orderBy('nombre')->pluck('nombre', 'id');

        return view('revisiones.tablero', compact(
            'total', 'ejecutadas', 'pendientes', 'porcentaje',
            'porUsuario', 'porMotivo', 'porAcometida', 'usuarios'
        ));
    }

    // ========================================
    // LISTADO DE ORDENES DE REVISION
    // ========================================

    /**
     * GET /revisiones
     */
    public function index(Request $request)
    {
        $query = OrdenRevision::with('usuario', 'fotos', 'censoHidraulico');

        if ($request->filled('estado_orden')) {
            $query->where('estado_orden', $request->estado_orden);
        }
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        if ($request->filled('motivo_revision')) {
            $query->where('motivo_revision', $request->motivo_revision);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('codigo_predio', 'LIKE', '%' . $request->buscar . '%')
                  ->orWhere('nombre_suscriptor', 'LIKE', '%' . $request->buscar . '%')
                  ->orWhere('direccion', 'LIKE', '%' . $request->buscar . '%');
            });
        }

        $revisiones = $query->orderBy('created_at', 'desc')->paginate(20);
        $usuarios = Usuario::orderBy('nombre')->pluck('nombre', 'id');

        return view('revisiones.index', compact('revisiones', 'usuarios'));
    }

    // ========================================
    // VER LECTURAS CON CRITICA (supervisor)
    // ========================================

    /**
     * GET /revisiones/criticas
     * Muestra las lecturas de ordenescu con Estado=4 (criticas).
     * Las que tienen Coordenada='generar' estan marcadas para revision.
     */
    public function criticas(Request $request)
    {
        // ── Determinar el scope temporal ─────────────────────────────────────
        // Prioridad: 1) rango de fechas manual  2) periodo seleccionado  3) último período
        $periodos       = PeriodoLectura::orderBy('id', 'desc')->get(['id','nombre','codigo','fecha_inicio_lectura','fecha_fin_lectura']);
        $ultimoPeriodo  = $periodos->first();
        $periodoActivo  = null;

        $query = Ordenesmtl::where('Estado', 4)->where('Critica', '!=', '54-NORMAL');

        if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
            // Rango de fechas manual — ignora periodo
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_de_ejecucion', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_de_ejecucion', '<=', $request->fecha_hasta);
            }
        } elseif ($request->filled('periodo_id')) {
            $periodoActivo = $periodos->firstWhere('id', $request->periodo_id);
            $query->where('periodo_lectura_id', $request->periodo_id);
        } else {
            // Por defecto: último período
            $periodoActivo = $ultimoPeriodo;
            if ($ultimoPeriodo) {
                $query->where('periodo_lectura_id', $ultimoPeriodo->id);
            }
        }

        // Filtros adicionales
        if ($request->filled('critica')) {
            $query->where('Critica', $request->critica);
        }
        if ($request->filled('ciclo')) {
            $query->where('Ciclo', $request->ciclo);
        }
        if ($request->filled('ruta')) {
            $query->where('Ruta', $request->ruta);
        }
        if ($request->filled('marcadas')) {
            if ($request->marcadas == 'si') {
                $query->where('Coordenada', 'generar');
            } elseif ($request->marcadas == 'no') {
                $query->where(function ($q) {
                    $q->whereNull('Coordenada')->orWhere('Coordenada', '!=', 'generar');
                });
            }
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('Suscriptor', 'LIKE', '%' . $request->buscar . '%')
                  ->orWhere('Nombre', 'LIKE', '%' . $request->buscar . '%')
                  ->orWhere('Direccion', 'LIKE', '%' . $request->buscar . '%');
            });
        }

        // Contadores: se clona ANTES de ordenar/paginar para no corromper el builder
        $queryMarcadas = clone $query;
        $criticas      = $query->orderBy('id', 'desc')->paginate(30)->appends($request->query());
        $totalCriticas = $criticas->total();
        $totalMarcadas = $queryMarcadas->where('Coordenada', 'generar')->count();

        // Tipos de crítica disponibles
        $tiposCritica = Ordenesmtl::where('Estado', 4)
            ->whereNotNull('Critica')->where('Critica', '!=', '')
            ->distinct()->pluck('Critica');

        $revisores = Usuario::orderBy('nombre')->pluck('nombre', 'id');

        return view('revisiones.criticas', compact(
            'criticas', 'tiposCritica', 'revisores',
            'totalCriticas', 'totalMarcadas',
            'periodos', 'periodoActivo'
        ));
    }

    // ========================================
    // MARCAR / DESMARCAR CRITICAS (AJAX)
    // Usa tu logica existente: Coordenada = 'generar'
    // ========================================

    /**
     * POST /revisiones/adicionar-critica (AJAX)
     * Marca lecturas seleccionadas con Coordenada='generar'
     */
    public function adicionarcritica(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('id');

            foreach ($id as $fila) {
                DB::table('ordenescu')
                    ->where([
                        ['id', '=', $fila],
                        ['Estado', '=', 4],
                    ])
                    ->update(['Coordenada' => 'generar']);
            }

            return response()->json(['mensaje' => 'ok']);
        }
    }

    /**
     * POST /revisiones/eliminar-critica (AJAX)
     * Desmarca lecturas seleccionadas (Coordenada = NULL)
     */
    public function eliminarcritica(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('id');

            foreach ($id as $fila) {
                DB::table('ordenescu')
                    ->where([
                        ['id', '=', $fila],
                        ['Estado', '=', 4],
                    ])
                    ->update(['Coordenada' => null]);
            }

            return response()->json(['mensaje' => 'ok']);
        }
    }

    // ========================================
    // GENERAR ORDENES DE REVISION
    // ========================================

    /**
     * POST /revisiones/generar
     * Genera ordenes de revision a partir de las lecturas marcadas
     * (Estado=4, Coordenada='generar') que aun no tienen orden.
     */
    public function generar(Request $request)
    {
        $this->validate($request, [
            'usuario_id' => 'required|exists:usuario,id',
        ]);

        $usuarioId = $request->input('usuario_id');

        // Obtener todas las marcadas que NO tienen orden de revision
        $lecturasConRevision = OrdenRevision::pluck('lectura_id')->toArray();

        $marcadas = Ordenesmtl::where('Estado', 4)
            ->where('Coordenada', 'generar')
            ->when(!empty($lecturasConRevision), function ($q) use ($lecturasConRevision) {
                $q->whereNotIn('id', $lecturasConRevision);
            })
            ->get();

        if ($marcadas->isEmpty()) {
            return redirect()->route('revisiones.criticas')
                ->with('error', 'No hay lecturas marcadas para generar ordenes de revision.');
        }

        $creadas = 0;
        foreach ($marcadas as $lectura) {
            OrdenRevision::crearDesdeLectura($lectura, $usuarioId);
            $creadas++;
        }

        return redirect()->route('revisiones.index')
            ->with('success', "$creadas ordenes de revision creadas y asignadas.");
    }

    // ========================================
    // DETALLE DE UNA REVISION
    // ========================================

    /**
     * GET /revisiones/{id}
     */
    public function show($id)
    {
        $revision = OrdenRevision::with('fotos', 'censoHidraulico', 'usuario', 'lectura')
            ->findOrFail($id);

        return view('revisiones.show', compact('revision'));
    }

    // ========================================
    // ELIMINAR REVISION (solo PENDIENTE)
    // ========================================

    /**
     * DELETE /revisiones/{id}
     */
    public function destroy($id)
    {
        $revision = OrdenRevision::findOrFail($id);

        if ($revision->estado_orden === 'EJECUTADO') {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una revision ya ejecutada.');
        }

        // Devolver la lectura a sin marcar
        if ($revision->lectura_id) {
            DB::table('ordenescu')
                ->where('id', $revision->lectura_id)
                ->update(['Coordenada' => null]);
        }

        $revision->delete();

        return redirect()->route('revisiones.index')
            ->with('success', 'Orden de revision eliminada.');
    }

    // ========================================
    // REASIGNAR USUARIO
    // ========================================

    /**
     * POST /revisiones/{id}/reasignar
     */
    public function reasignar(Request $request, $id)
    {
        $this->validate($request, [
            'usuario_id' => 'required|exists:usuario,id',
        ]);

        $revision = OrdenRevision::findOrFail($id);

        if ($revision->estado_orden === 'EJECUTADO') {
            return redirect()->back()
                ->with('error', 'No se puede reasignar una revision ya ejecutada.');
        }

        $revision->update(['usuario_id' => $request->usuario_id]);

        return redirect()->route('revisiones.show', $id)
            ->with('success', 'Revision reasignada correctamente.');
    }

    // ========================================
    // GESTION DE LISTAS PARAMETROS
    // ========================================

    /**
     * GET /listas-parametros
     */
    public function listas(Request $request)
    {
        $query = ListaParametro::query();

        if ($request->filled('tipo_lista')) {
            $query->where('tipo_lista', $request->tipo_lista);
        }

        $listas = $query->orderBy('tipo_lista')->orderBy('id')->paginate(50);
        $tipos = ListaParametro::distinct()->pluck('tipo_lista');

        return view('revisiones.listas', compact('listas', 'tipos'));
    }
}
