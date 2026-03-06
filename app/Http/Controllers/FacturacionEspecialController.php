<?php

namespace App\Http\Controllers;

use App\Models\Lectura;
use App\Models\PeriodoLectura;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacturacionEspecialController extends Controller
{
    protected $facturacionService;

    public function __construct(FacturacionService $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }

    /**
     * Mostrar vista de facturación especial con DataTable
     */
    public function index(Request $request)
    {
        $periodos = PeriodoLectura::orderBy('fecha_inicio', 'desc')->get();
        $periodoId = $request->input('periodo_id');
        
        $lecturas = collect();
        
        if ($periodoId) {
            // Obtener lecturas que NO son normales (estado != 54) y están confirmadas o pendientes de gestión especial
            // Ajusta los estados según tu lógica de negocio para "especiales"
            $lecturas = Lectura::with(['cliente', 'periodo'])
                ->where('periodo_id', $periodoId)
                ->where('estado', '!=', 54) // Excluir normales
                ->whereIn('estado', [51, 52, 53, 55, 56, 57, 58]) // Ejemplo: Altas, Bajas, Sin Lectura, etc.
                ->orderBy('cliente_id')
                ->get();
        }

        return view('facturacion.especial.index', compact('periodos', 'lecturas', 'periodoId'));
    }

    /**
     * Facturar las lecturas seleccionadas manualmente
     */
    public function facturarSeleccionadas(Request $request)
    {
        $request->validate([
            'lecturas_ids' => 'required|array',
            'lecturas_ids.*' => 'exists:lecturas,id',
            'periodo_id' => 'required|exists:periodo_lecturas,id'
        ]);

        $lecturasIds = $request->lecturas_ids;
        $periodoId = $request->periodo_id;
        
        $exitosas = 0;
        $errores = [];

        DB::beginTransaction();
        try {
            foreach ($lecturasIds as $lecturaId) {
                $lectura = Lectura::with('cliente')->find($lecturaId);
                
                if (!$lectura) {
                    $errores[] = "Lectura ID {$lecturaId} no encontrada.";
                    continue;
                }

                // Validar que no tenga factura ya
                if ($lectura->factura) {
                    $errores[] = "La lectura del cliente {$lectura->cliente->nombre} ya tiene factura generada.";
                    continue;
                }

                // Llamar al servicio de facturación
                $resultado = $this->facturacionService->generarFactura($lectura);

                if ($resultado['success']) {
                    $exitosas++;
                    // Actualizar estado de la lectura si es necesario
                    $lectura->update(['estado' => 60]); // 60 = Facturada Especial (ejemplo)
                } else {
                    $errores[] = "Error al facturar cliente {$lectura->cliente->nombre}: " . ($resultado['message'] ?? 'Error desconocido');
                }
            }

            if ($exitosas > 0) {
                DB::commit();
                return redirect()->back()->with('success', "Se generaron {$exitosas} facturas especiales correctamente.")
                                   ->with('errores', $errores);
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'No se pudo generar ninguna factura. Verifica los errores.')
                                   ->with('errores', $errores);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en facturación especial masiva: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error crítico: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF individual desde esta vista
     */
    public function descargarPdf($id)
    {
        return $this->facturacionService->descargarPdf($id);
    }
}
