<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pago::with('factura.cliente')
            ->orderBy('fecha_pago', 'desc')
            ->orderBy('id', 'desc');

        if ($desde = $request->desde) $query->where('fecha_pago', '>=', $desde);
        if ($hasta = $request->hasta)  $query->where('fecha_pago', '<=', $hasta);
        if ($medio = $request->medio_pago) $query->where('medio_pago', $medio);
        if ($recibo = $request->numero_recibo) $query->where('numero_recibo', 'like', "%{$recibo}%");

        $pagos = $query->paginate(25)->appends($request->query());

        return view('facturacion.pagos.index', compact('pagos'));
    }
}
