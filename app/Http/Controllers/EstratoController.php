<?php

namespace App\Http\Controllers;

use App\Models\Estrato;
use Illuminate\Http\Request;

class EstratoController extends Controller
{
    /** Vista de gestión de subsidios por estrato */
    public function index()
    {
        $estratos = Estrato::orderBy('numero')->get();
        return view('facturacion.estratos.subsidios', compact('estratos'));
    }

    /** Actualiza el subsidio de un estrato individual (AJAX) */
    public function update(Request $request, $id)
    {
        $estrato = Estrato::findOrFail($id);

        $data = $request->validate([
            'porcentaje_subsidio'          => 'required|numeric|between:-100,100',
            'subsidio_fijo_acueducto'      => 'nullable|numeric|min:0',
            'subsidio_fijo_alcantarillado' => 'nullable|numeric|min:0',
            'consumo_minimo_subsidio'      => 'nullable|numeric|min:0',
        ]);

        // Si se define un valor fijo para un servicio, el porcentaje no se usa para ese servicio.
        // consumo_minimo_subsidio: m³ mínimos para que aplique el subsidio (0 = siempre aplica).
        $estrato->update([
            'porcentaje_subsidio'          => $data['porcentaje_subsidio'],
            'subsidio_fijo_acueducto'      => $data['subsidio_fijo_acueducto'] ?? 0,
            'subsidio_fijo_alcantarillado' => $data['subsidio_fijo_alcantarillado'] ?? 0,
            'consumo_minimo_subsidio'      => $data['consumo_minimo_subsidio'] ?? 0,
        ]);

        return response()->json([
            'ok'      => true,
            'mensaje' => "Subsidio del estrato \"{$estrato->nombre}\" actualizado.",
            'estrato' => $estrato->fresh(),
        ]);
    }
}
