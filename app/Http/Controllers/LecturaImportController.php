<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Admin\Ordenesmtl;
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
     * Sincroniza LA, Promedio y Cons_Act en ordenescu
     * tomando los valores de lectura_actual, promedio_consumo_snapshot y consumo_m3
     * del período de facturación indicado.
     */
    public function sincronizarDesdeFacturas(Request $request)
    {
        $request->validate([
            'periodo_factura' => 'required',
            'periodo_lectura' => 'required',
        ]);

        $pFact = $request->periodo_factura;
        $pLect = $request->periodo_lectura;

        // Tomar los datos del período facturado
        $facturas = Factura::where('periodo', $pFact)
            ->whereNotNull('lectura_actual')
            ->get(['suscriptor', 'lectura_actual', 'consumo_m3', 'promedio_consumo_snapshot']);

        if ($facturas->isEmpty()) {
            return back()->with('error', "No se encontraron facturas para el período {$pFact}.");
        }

        $actualizados = 0;
        $noEncontrados = 0;

        foreach ($facturas as $f) {
            $filas = Ordenesmtl::where('Periodo', $pLect)
                ->where('Suscriptor', $f->suscriptor)
                ->count();

            if ($filas > 0) {
                Ordenesmtl::where('Periodo', $pLect)
                    ->where('Suscriptor', $f->suscriptor)
                    ->update([
                        'LA'       => $f->lectura_actual,
                        'Cons_Act' => $f->consumo_m3,
                        'Promedio' => (int) round($f->promedio_consumo_snapshot),
                    ]);
                $actualizados++;
            } else {
                $noEncontrados++;
            }
        }

        $msg = "Actualizados: {$actualizados} registros en período {$pLect}.";
        if ($noEncontrados > 0) {
            $msg .= " Sin orden en {$pLect}: {$noEncontrados} suscriptores.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Importa desde Excel. El archivo debe tener encabezados:
     * suscriptor | periodo | lec_anterior | consumo | promedio
     */
    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo'        => 'required|file|mimes:xlsx,xls,csv',
            'periodo_destino' => 'required',
        ]);

        $import = new LecturaAnteriorImport($request->periodo_destino);
        Excel::import($import, $request->file('archivo'));

        $msg = "Excel procesado. Actualizados: {$import->actualizados}";
        if ($import->noEncontrados > 0) {
            $msg .= ", sin orden en el período: {$import->noEncontrados}";
        }
        if ($import->errores > 0) {
            $msg .= ", con errores: {$import->errores}";
        }

        return back()->with('success', $msg . '.');
    }

    /**
     * Descarga la plantilla Excel de ejemplo.
     */
    public function plantilla()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_lecturas.csv"',
        ];

        $csv = "suscriptor,periodo,lec_anterior,consumo,promedio\n";
        $csv .= "101,202601,1250,18,17\n";
        $csv .= "102,202601,870,12,14\n";

        return response($csv, 200, $headers);
    }
}
