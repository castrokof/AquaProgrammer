<?php

namespace App\Http\Controllers;

use App\Jobs\GenerarPdfMasivoJob;
use App\Models\Exportacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportacionController extends Controller
{
    /**
     * Recibe los IDs de facturas, crea el registro en exportaciones
     * y despacha el Job a la cola de base de datos.
     */
    public function despachar(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:facturas,id',
        ]);

        $ids = array_values(array_unique($request->ids));

        $exportacion = Exportacion::create([
            'usuario_id' => auth()->id(),
            'ids'        => $ids,
            'estado'     => 'PENDIENTE',
            'total'      => count($ids),
            'procesados' => 0,
            'progreso'   => 0,
        ]);

        // Despachar job a la cola (database)
        GenerarPdfMasivoJob::dispatch($exportacion->id);

        return response()->json([
            'ok'             => true,
            'exportacion_id' => $exportacion->id,
            'total'          => $exportacion->total,
            'mensaje'        => 'Generación en cola. Se procesarán ' . $exportacion->total . ' factura(s).',
        ]);
    }

    /**
     * Devuelve el estado actual de una exportación (para polling desde el frontend).
     */
    public function estado(int $id)
    {
        $exportacion = Exportacion::findOrFail($id);

        // Seguridad: solo el mismo usuario puede consultar
        if ($exportacion->usuario_id && $exportacion->usuario_id !== auth()->id()) {
            abort(403);
        }

        $data = [
            'estado'     => $exportacion->estado,
            'progreso'   => $exportacion->progreso,
            'total'      => $exportacion->total,
            'procesados' => $exportacion->procesados,
        ];

        if ($exportacion->estado === 'LISTO' && $exportacion->archivo) {
            $data['url_descarga'] = route('exportaciones.descargar', $exportacion->id);
        }

        if ($exportacion->estado === 'ERROR') {
            $data['error'] = $exportacion->mensaje_error;
        }

        return response()->json($data);
    }

    /**
     * Descarga el ZIP generado y lo elimina del servidor.
     */
    public function descargar(int $id)
    {
        $exportacion = Exportacion::findOrFail($id);

        if ($exportacion->usuario_id && $exportacion->usuario_id !== auth()->id()) {
            abort(403);
        }

        if ($exportacion->estado !== 'LISTO' || !$exportacion->archivo) {
            abort(404, 'Archivo no disponible.');
        }

        $ruta = storage_path('app/public/' . $exportacion->archivo);

        if (!file_exists($ruta)) {
            abort(404, 'Archivo no encontrado en el servidor.');
        }

        return response()->download($ruta, 'facturas_' . now()->format('Y-m-d') . '.zip')
            ->deleteFileAfterSend(true);
    }
}
