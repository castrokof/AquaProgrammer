<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OtrosCobrosCatalogo;
use App\Models\ClienteOtrosCobro;
use Illuminate\Http\Request;

class OtrosCobrosController extends Controller
{
    public function index(Request $request)
    {
        $catalogo = OtrosCobrosCatalogo::activo()->orderBy('nombre')->get();

        $query = ClienteOtrosCobro::with(['cliente', 'catalogo'])
            ->orderBy('created_at', 'desc');

        if ($s = $request->suscriptor) {
            $query->whereHas('cliente', function ($q) use ($s) {
                $q->where('suscriptor', 'like', "%{$s}%");
            });
        }
        if ($e = $request->estado) {
            $query->where('estado', $e);
        }

        $cobros = $query->paginate(20)->appends(request()->query());

        return view('facturacion.otros-cobros.index', compact('cobros', 'catalogo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'   => 'required|exists:clientes,id',
            'catalogo_id'  => 'required|exists:otros_cobros_catalogo,id',
            'tipo_servicio'=> 'required|in:ACUEDUCTO,ALCANTARILLADO',
            'concepto'     => 'required|string|max:255',
            'diametro'     => 'nullable|string|max:50',
            'monto_total'  => 'required|numeric|min:1',
            'num_cuotas'   => 'required|integer|min:1|max:60',
            'fecha_inicio' => 'required|date',
            'observaciones'=> 'nullable|string',
        ]);

        $cuotaMensual = round($request->monto_total / $request->num_cuotas, 2);

        ClienteOtrosCobro::create([
            'cliente_id'    => $request->cliente_id,
            'catalogo_id'   => $request->catalogo_id,
            'tipo_servicio' => $request->tipo_servicio,
            'concepto'      => $request->concepto,
            'diametro'      => $request->diametro,
            'observaciones' => $request->observaciones,
            'monto_total'   => $request->monto_total,
            'num_cuotas'    => $request->num_cuotas,
            'cuota_mensual' => $cuotaMensual,
            'cuotas_pagadas'=> 0,
            'saldo'         => $request->monto_total,
            'fecha_inicio'  => $request->fecha_inicio,
            'estado'        => 'ACTIVO',
            'usuario_id'    => auth()->id(),
        ]);

        return response()->json(['ok' => true, 'cuota_mensual' => $cuotaMensual, 'mensaje' => 'Cobro adicional asignado.']);
    }

    public function anular($id)
    {
        $cobro = ClienteOtrosCobro::findOrFail($id);
        $cobro->update(['estado' => 'ANULADO']);
        return response()->json(['ok' => true, 'mensaje' => 'Cobro anulado.']);
    }

    public function buscarCliente(Request $request)
    {
        $clientes = Cliente::where('suscriptor', 'like', "%{$request->q}%")
            ->orWhere('nombre', 'like', "%{$request->q}%")
            ->limit(10)
            ->get(['id', 'suscriptor', 'nombre', 'apellido', 'direccion']);

        return response()->json($clientes->map(function ($c) {
            return [
                'id'   => $c->id,
                'text' => "{$c->suscriptor} — " . trim($c->nombre . ' ' . $c->apellido),
            ];
        }));
    }
}
