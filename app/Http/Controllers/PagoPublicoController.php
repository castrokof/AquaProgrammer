<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagoPublicoController extends Controller
{
    /** Página principal: formulario de búsqueda */
    public function index()
    {
        $empresa = Empresa::instancia();
        return view('pago-publico.index', compact('empresa'));
    }

    /** Busca una factura por N° de suscriptor o N° de factura */
    public function buscar(Request $request)
    {
        $request->validate([
            'busqueda' => 'required|string|max:60',
        ], [
            'busqueda.required' => 'Ingresa el número de factura o código de suscriptor.',
        ]);

        $empresa  = Empresa::instancia();
        $termino  = trim($request->busqueda);

        // Quitar el prefijo si el usuario lo incluyó (ej: ASPD202403000001 → 202403000001)
        $prefijo  = $empresa->prefijo_factura ?? '';
        $numLimpio = $prefijo && str_starts_with($termino, $prefijo)
            ? substr($termino, strlen($prefijo))
            : $termino;

        $factura = Factura::with(['cliente', 'pagos', 'tarifaPeriodo'])
            ->where('numero_factura', $numLimpio)
            ->orWhere('suscriptor', $termino)
            ->orderBy('fecha_expedicion', 'desc')
            ->first();

        if (!$factura) {
            return back()->withInput()->withErrors([
                'busqueda' => 'No se encontró ninguna factura con ese número o código de suscriptor.',
            ]);
        }

        $saldoPendiente = $factura->saldoPendiente();
        $wompiActivo    = $empresa->wompi_public_key && $empresa->wompi_integrity_key;

        // Generar referencia única y firma de integridad para Wompi
        $referencia = null;
        $firma      = null;
        $amountCents = 0;

        if ($wompiActivo && $saldoPendiente > 0 && !in_array($factura->estado, ['PAGADA','ANULADA'])) {
            $referencia   = 'FACTURA-' . $factura->id . '-' . time();
            $amountCents  = (int) round($saldoPendiente); // Wompi COP ya son enteros
            $currency     = 'COP';
            $firma = hash('sha256',
                $referencia . $amountCents . $currency . $empresa->wompi_integrity_key
            );
        }

        return view('pago-publico.index', compact(
            'empresa', 'factura', 'saldoPendiente',
            'wompiActivo', 'referencia', 'firma', 'amountCents'
        ));
    }

    /**
     * Resultado de pago: Wompi redirige aquí después del checkout.
     * Wompi añade ?id=xxx a la URL de redirección.
     */
    public function resultado(Request $request)
    {
        $empresa       = Empresa::instancia();
        $transaccionId = $request->get('id');
        $transaccion   = null;
        $factura       = null;

        if ($transaccionId && $empresa->wompi_private_key) {
            // Consultar estado de la transacción en la API de Wompi
            $transaccion = $this->consultarTransaccion($transaccionId, $empresa);

            // Si fue aprobada y tiene referencia, registrar el pago en la BD
            if ($transaccion && $transaccion['status'] === 'APPROVED') {
                $ref     = $transaccion['reference'] ?? null;
                $factura = $this->registrarPagoWompi($transaccion, $empresa);
            }
        }

        return view('pago-publico.resultado', compact('empresa', 'transaccionId', 'transaccion', 'factura'));
    }

    /**
     * Webhook de Wompi: recibe notificaciones de cambio de estado.
     * URL a configurar en el panel Wompi: POST /webhook/wompi
     */
    public function webhook(Request $request)
    {
        $empresa = Empresa::instancia();

        // Validar firma del evento Wompi
        $payload    = $request->all();
        $signature  = $request->header('X-Event-Checksum');

        if ($empresa->wompi_private_key && $signature) {
            $checksum = hash('sha256',
                json_encode($payload['data'] ?? []) .
                ($payload['sent_at'] ?? '') .
                $empresa->wompi_private_key
            );
            if (!hash_equals($checksum, $signature)) {
                Log::warning('Wompi webhook: firma inválida');
                return response()->json(['ok' => false], 401);
            }
        }

        $event = $payload['event'] ?? '';
        if ($event === 'transaction.updated') {
            $tx = $payload['data']['transaction'] ?? null;
            if ($tx && $tx['status'] === 'APPROVED') {
                $this->registrarPagoWompi($tx, $empresa);
            }
        }

        return response()->json(['ok' => true]);
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function consultarTransaccion(string $id, Empresa $empresa): ?array
    {
        try {
            $base = $empresa->wompi_test_mode
                ? 'https://sandbox.wompi.co/v1'
                : 'https://production.wompi.co/v1';

            $response = \Illuminate\Support\Facades\Http::withToken($empresa->wompi_private_key)
                ->get("{$base}/transactions/{$id}");

            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Throwable $e) {
            Log::error('Wompi consultarTransaccion: ' . $e->getMessage());
        }
        return null;
    }

    private function registrarPagoWompi(array $tx, Empresa $empresa): ?Factura
    {
        try {
            $ref = $tx['reference'] ?? '';
            // Referencia formato: FACTURA-{id}-{timestamp}
            preg_match('/^FACTURA-(\d+)-/', $ref, $m);
            if (empty($m[1])) return null;

            $factura = Factura::with('pagos')->find($m[1]);
            if (!$factura || in_array($factura->estado, ['PAGADA','ANULADA'])) return $factura;

            // Evitar doble registro
            if (Pago::where('referencia_pasarela', $tx['id'])->exists()) return $factura;

            $amountCOP  = ($tx['amount_in_cents'] ?? 0); // ya en COP (Wompi COP = enteros)
            $saldo      = $factura->saldoPendiente();
            $toPay      = min($amountCOP, $saldo);

            $pago = new Pago([
                'factura_id'               => $factura->id,
                'fecha_pago'               => now()->toDateString(),
                'medio_pago'               => 'TRANSFERENCIA',
                'banco'                    => 'Wompi (en línea)',
                'referencia_pasarela'      => $tx['id'],
                'estado_pasarela'          => $tx['status'],
                'numero_recibo'            => $tx['id'],
                'pagos_acueducto'          => $toPay,
                'pagos_alcantarillado'     => 0,
                'observaciones'            => 'Pago en línea vía Wompi. Ref: ' . $ref,
            ]);
            $pago->total_pago_realizado = $pago->calcularTotal();
            $pago->save();

            return $factura->fresh();
        } catch (\Throwable $e) {
            Log::error('Wompi registrarPagoWompi: ' . $e->getMessage());
            return null;
        }
    }
}
