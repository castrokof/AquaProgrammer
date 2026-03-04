<?php

namespace App\Http\Controllers;

use App\Models\PeriodoLectura;
use App\Models\TarifaPeriodo;
use Illuminate\Http\Request;

class PeriodoLecturaController extends Controller
{
    public function index()
    {
        $periodos = PeriodoLectura::with('tarifa')->orderBy('codigo', 'desc')->paginate(20);
        $tarifas  = TarifaPeriodo::where('activo', true)->orderBy('vigente_desde', 'desc')->get();
        return view('facturacion.periodos.index', compact('periodos', 'tarifas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'               => 'required|string|size:6|unique:periodos_lectura,codigo',
            'nombre'               => 'required|string|max:80',
            'ciclo'                => 'required|integer|min:1',
            'tarifa_periodo_id'    => 'nullable|exists:tarifa_periodos,id',
            'fecha_inicio_lectura' => 'required|date',
            'fecha_fin_lectura'    => 'required|date|after_or_equal:fecha_inicio_lectura',
            'fecha_expedicion'     => 'required|date',
            'fecha_vencimiento'    => 'required|date|after_or_equal:fecha_expedicion',
            'fecha_corte'          => 'required|date|after_or_equal:fecha_vencimiento',
        ]);

        PeriodoLectura::create($request->all());

        return response()->json(['ok' => true, 'mensaje' => 'Período creado correctamente.']);
    }

    public function update(Request $request, $id)
    {
        $periodo = PeriodoLectura::findOrFail($id);

        $request->validate([
            'nombre'               => 'required|string|max:80',
            'ciclo'                => 'required|integer|min:1',
            'tarifa_periodo_id'    => 'nullable|exists:tarifa_periodos,id',
            'fecha_inicio_lectura' => 'required|date',
            'fecha_fin_lectura'    => 'required|date|after_or_equal:fecha_inicio_lectura',
            'fecha_expedicion'     => 'required|date',
            'fecha_vencimiento'    => 'required|date|after_or_equal:fecha_expedicion',
            'fecha_corte'          => 'required|date|after_or_equal:fecha_vencimiento',
            'observaciones'        => 'nullable|string',
        ]);

        $periodo->update($request->all());

        return response()->json(['ok' => true, 'mensaje' => 'Período actualizado.']);
    }

    /** Avanza el estado del período (flujo: PLANIFICADO → ACTIVO → LECTURA_CERRADA → FACTURADO → CERRADO) */
    public function cambiarEstado(Request $request, $id)
    {
        $periodo = PeriodoLectura::findOrFail($id);

        $flujo = [
            'PLANIFICADO'    => 'ACTIVO',
            'ACTIVO'         => 'LECTURA_CERRADA',
            'LECTURA_CERRADA'=> 'FACTURADO',
            'FACTURADO'      => 'CERRADO',
        ];

        $nuevoEstado = $request->input('estado') ?? ($flujo[$periodo->estado] ?? null);

        if (!$nuevoEstado) {
            return response()->json(['ok' => false, 'mensaje' => 'No hay estado siguiente.'], 422);
        }

        $periodo->update(['estado' => $nuevoEstado]);

        return response()->json(['ok' => true, 'nuevo_estado' => $nuevoEstado, 'mensaje' => "Estado cambiado a {$nuevoEstado}."]);
    }

    public function show($id)
    {
        $periodo = PeriodoLectura::with('tarifa')->findOrFail($id);
        return response()->json($periodo);
    }
}
