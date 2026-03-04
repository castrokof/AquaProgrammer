<?php

namespace App\Http\Controllers;

use App\Models\TarifaPeriodo;
use App\Models\TarifaCargoFijo;
use App\Models\TarifaRango;
use App\Models\Estrato;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function index()
    {
        $tarifas  = TarifaPeriodo::orderBy('vigente_desde', 'desc')->get();
        $estratos = Estrato::where('activo', true)->orderBy('numero')->get();
        return view('facturacion.tarifas.index', compact('tarifas', 'estratos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:120',
            'numero_resolucion' => 'nullable|string|max:80',
            'vigente_desde'     => 'required|date',
            'vigente_hasta'     => 'nullable|date|after_or_equal:vigente_desde',
        ]);

        // Si se activa como vigente, desactivar las anteriores
        if ($request->boolean('activo')) {
            TarifaPeriodo::where('activo', true)->update(['activo' => false]);
        }

        $tarifa = TarifaPeriodo::create($request->only([
            'nombre', 'numero_resolucion', 'vigente_desde', 'vigente_hasta', 'observaciones',
        ]) + ['activo' => $request->boolean('activo')]);

        return response()->json(['ok' => true, 'id' => $tarifa->id, 'mensaje' => 'Resolución tarifaria creada.']);
    }

    /** Retorna cargos fijos y rangos de una tarifa para edición Ajax */
    public function detalle($id)
    {
        $tarifa = TarifaPeriodo::with(['cargos.estrato', 'rangos.estrato'])->findOrFail($id);
        return response()->json($tarifa);
    }

    /** Guarda o actualiza cargos fijos masivamente (array de cargos) */
    public function guardarCargos(Request $request, $id)
    {
        $tarifa = TarifaPeriodo::findOrFail($id);

        $request->validate([
            'cargos'                  => 'required|array',
            'cargos.*.servicio'       => 'required|in:ACUEDUCTO,ALCANTARILLADO',
            'cargos.*.estrato_id'     => 'required|exists:estratos,id',
            'cargos.*.cargo_fijo'     => 'required|numeric|min:0',
        ]);

        foreach ($request->cargos as $cargo) {
            TarifaCargoFijo::updateOrCreate(
                ['tarifa_periodo_id' => $tarifa->id, 'servicio' => $cargo['servicio'], 'estrato_id' => $cargo['estrato_id']],
                ['cargo_fijo' => $cargo['cargo_fijo']]
            );
        }

        return response()->json(['ok' => true, 'mensaje' => 'Cargos fijos guardados.']);
    }

    /** Guarda o actualiza rangos de consumo masivamente */
    public function guardarRangos(Request $request, $id)
    {
        $tarifa = TarifaPeriodo::findOrFail($id);

        $request->validate([
            'rangos'                  => 'required|array',
            'rangos.*.servicio'       => 'required|in:ACUEDUCTO,ALCANTARILLADO',
            'rangos.*.estrato_id'     => 'required|exists:estratos,id',
            'rangos.*.tipo'           => 'required|in:BASICO,COMPLEMENTARIO,SUNTUARIO',
            'rangos.*.rango_desde'    => 'required|integer|min:0',
            'rangos.*.rango_hasta'    => 'nullable|integer',
            'rangos.*.precio_m3'      => 'required|numeric|min:0',
        ]);

        foreach ($request->rangos as $rango) {
            TarifaRango::updateOrCreate(
                ['tarifa_periodo_id' => $tarifa->id, 'servicio' => $rango['servicio'],
                 'estrato_id' => $rango['estrato_id'], 'tipo' => $rango['tipo']],
                ['rango_desde' => $rango['rango_desde'], 'rango_hasta' => $rango['rango_hasta'] ?? null,
                 'precio_m3'   => $rango['precio_m3']]
            );
        }

        return response()->json(['ok' => true, 'mensaje' => 'Rangos guardados.']);
    }

    public function activar($id)
    {
        TarifaPeriodo::where('activo', true)->update(['activo' => false]);
        TarifaPeriodo::where('id', $id)->update(['activo' => true]);
        return response()->json(['ok' => true, 'mensaje' => 'Tarifa activada como vigente.']);
    }
}
