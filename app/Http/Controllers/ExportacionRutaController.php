<?php

namespace App\Http\Controllers;

use App\Jobs\GenerarPdfRutaJob;
use App\Models\Exportacion;
use App\Models\Factura;
use App\Models\PeriodoLectura;
use Illuminate\Http\Request;

class ExportacionRutaController extends Controller
{
    /**
     * Muestra el formulario de selección de período y el listado de exportaciones
     * por ruta ya generadas o en proceso.
     */
    public function index(Request $request)
    {
        $periodos      = PeriodoLectura::orderBy('codigo', 'desc')->get(['id', 'codigo', 'nombre']);
        $exportaciones = collect();
        $periodo       = null;

        if ($request->filled('periodo')) {
            $periodo = $request->periodo;
            $exportaciones = Exportacion::where('tipo', 'por_ruta')
                ->where('periodo', $periodo)
                ->orderBy('id_ruta')
                ->get();
        }

        return view('facturacion.exportaciones.por_ruta', compact('periodos', 'exportaciones', 'periodo'));
    }

    /**
     * AJAX: crea un job de generación de PDF por cada ruta del período indicado.
     * Responde con JSON listando los jobs creados.
     */
    public function generar(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string|max:6',
        ]);

        $periodo = $request->periodo;

        // Eliminar exportaciones previas por_ruta para este período (evitar duplicados)
        Exportacion::where('tipo', 'por_ruta')
            ->where('periodo', $periodo)
            ->whereIn('estado', ['PENDIENTE', 'PROCESANDO'])
            ->delete();

        // Obtener todas las rutas que tienen facturas en este período
        $rutas = Factura::join('clientes', 'clientes.suscriptor', '=', 'facturas.suscriptor')
            ->where('facturas.periodo', $periodo)
            ->whereNotNull('clientes.id_ruta')
            ->distinct()
            ->orderBy('clientes.id_ruta')
            ->pluck('clientes.id_ruta');

        if ($rutas->isEmpty()) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No hay facturas con rutas asignadas para el período ' . $periodo . '.',
            ], 422);
        }

        $creadas = [];

        foreach ($rutas as $ruta) {
            $ids = Factura::join('clientes', 'clientes.suscriptor', '=', 'facturas.suscriptor')
                ->where('facturas.periodo', $periodo)
                ->where('clientes.id_ruta', $ruta)
                ->pluck('facturas.id')
                ->toArray();

            $exportacion = Exportacion::create([
                'usuario_id' => auth()->id(),
                'ids'        => $ids,
                'estado'     => 'PENDIENTE',
                'total'      => count($ids),
                'procesados' => 0,
                'progreso'   => 0,
                'periodo'    => $periodo,
                'id_ruta'    => $ruta,
                'tipo'       => 'por_ruta',
            ]);

            GenerarPdfRutaJob::dispatch($exportacion->id);

            $creadas[] = [
                'ruta'           => $ruta,
                'total'          => count($ids),
                'exportacion_id' => $exportacion->id,
            ];
        }

        return response()->json([
            'ok'      => true,
            'rutas'   => $creadas,
            'mensaje' => count($creadas) . ' ruta(s) enviadas a la cola de generación.',
        ]);
    }

    /**
     * AJAX: retorna el estado actual de una exportación.
     */
    public function estado(int $id)
    {
        $exportacion = Exportacion::findOrFail($id);

        $data = [
            'estado'     => $exportacion->estado,
            'progreso'   => $exportacion->progreso,
            'total'      => $exportacion->total,
            'procesados' => $exportacion->procesados,
        ];

        if ($exportacion->estado === 'LISTO' && $exportacion->archivo) {
            $data['url_descarga'] = route('exportaciones.ruta.descargar', $exportacion->id);
        }

        if ($exportacion->estado === 'ERROR') {
            $data['error'] = $exportacion->mensaje_error;
        }

        return response()->json($data);
    }

    /**
     * Descarga el ZIP de una ruta (el archivo se conserva en el servidor).
     */
    public function descargar(int $id)
    {
        $exportacion = Exportacion::findOrFail($id);

        if ($exportacion->estado !== 'LISTO' || !$exportacion->archivo) {
            abort(404, 'Archivo no disponible aún.');
        }

        $path = storage_path('app/public/' . $exportacion->archivo);

        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado en el servidor.');
        }

        $nombre = 'facturas_ruta_' . $exportacion->id_ruta . '_' . $exportacion->periodo . '.zip';

        return response()->download($path, $nombre);
    }
}
