<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Admin\Ordenesmtl;
use App\Models\Cliente;
use App\Models\ClienteHistoricoConsumo;
use App\Models\Factura;
use App\Models\PeriodoLectura;
use App\Imports\LecturaAnteriorImport;

class LecturaImportController extends Controller
{
    public function index()
    {
        $periodos = PeriodoLectura::orderBy('codigo', 'desc')->get(['codigo', 'nombre']);
        return view('lecturas.importar', compact('periodos'));
    }

    /**
     * Opción A – Desde Facturas del Sistema.
     *
     * Lee lectura_actual, consumo_m3, promedio_consumo_snapshot, lectura_anterior
     * de las facturas del período facturado y:
     *   - Escribe LA + Promedio en ordenescu del período destino (lecturas nuevas).
     *   - Registra consumo_m3 + lecturas en cliente_historico_consumos con el
     *     período facturado (actualiza también promedio_consumo del cliente).
     */
    public function sincronizarDesdeFacturas(Request $request)
    {
        $request->validate([
            'periodo_factura' => 'required|string|size:6',
            'periodo_lectura' => 'required|string|size:6',
        ]);

        $pFact = $request->periodo_factura;
        $pLect = $request->periodo_lectura;

        $facturas = Factura::where('periodo', $pFact)
            ->whereNotNull('lectura_actual')
            ->get(['suscriptor', 'lectura_anterior', 'lectura_actual', 'consumo_m3', 'promedio_consumo_snapshot']);

        if ($facturas->isEmpty()) {
            return back()->with('error', "No se encontraron facturas para el período {$pFact}.");
        }

        // Pre-cargar clientes para el historico
        $suscriptores = $facturas->pluck('suscriptor')->unique();
        $clientes = Cliente::whereIn('suscriptor', $suscriptores)
            ->get(['id', 'suscriptor'])
            ->keyBy('suscriptor');

        $actualizados  = 0;
        $noEncontrados = 0;
        $historicos    = 0;

        foreach ($facturas as $f) {
            // ── 1. Actualizar LA + Promedio en ordenescu ──────────────────────
            $existe = Ordenesmtl::where('Periodo', $pLect)
                ->where('Suscriptor', $f->suscriptor)
                ->exists();

            if ($existe) {
                Ordenesmtl::where('Periodo', $pLect)
                    ->where('Suscriptor', $f->suscriptor)
                    ->update([
                        'LA'       => $f->lectura_actual,
                        'Promedio' => (int) round($f->promedio_consumo_snapshot),
                    ]);
                $actualizados++;
            } else {
                $noEncontrados++;
            }

            // ── 2. Registrar consumo en histórico ─────────────────────────────
            $cliente = $clientes->get($f->suscriptor);
            if ($cliente && $f->consumo_m3 !== null) {
                ClienteHistoricoConsumo::registrarYActualizarPromedio(
                    $cliente->id,
                    $f->suscriptor,
                    $pFact,
                    (int) $f->consumo_m3,
                    $f->lectura_anterior,
                    $f->lectura_actual,
                );
                $historicos++;
            }
        }

        $msg = "Actualizados en ordenescu ({$pLect}): {$actualizados}. Históricos registrados ({$pFact}): {$historicos}.";
        if ($noEncontrados > 0) {
            $msg .= " Sin orden en {$pLect}: {$noEncontrados} suscriptores.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Opción B – Desde Excel externo.
     *
     * Columnas: suscriptor | lec_anterior | promedio | consumo
     *   - lec_anterior + promedio → ordenescu.LA / Promedio (período destino).
     *   - consumo → cliente_historico_consumos (período facturado).
     */
    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo'           => 'required|file|max:10240',
            'periodo_destino'   => 'required|string|size:6',
            'periodo_facturado' => 'nullable|string|size:6',
        ]);

        try {
            $import = new LecturaAnteriorImport(
                $request->periodo_destino,
                $request->periodo_facturado ?: null
            );

            Excel::import($import, $request->file('archivo'));

            $msg = "Excel procesado. Actualizados en ordenescu: {$import->actualizados}";
            if ($import->noEncontrados > 0) {
                $msg .= ", sin orden en el período: {$import->noEncontrados}";
            }
            if ($import->errores > 0) {
                $msg .= ", con errores: {$import->errores}";
            }

            return back()->with('success', $msg . '.');
        } catch (\Throwable $e) {
            \Log::error('importarExcel error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Descarga la plantilla CSV de ejemplo.
     */
    public function plantilla()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_lecturas.csv"',
        ];

        $csv  = "suscriptor,lec_anterior,promedio,consumo\n";
        $csv .= "101,1250,17,18\n";
        $csv .= "102,870,14,12\n";

        return response($csv, 200, $headers);
    }
}
