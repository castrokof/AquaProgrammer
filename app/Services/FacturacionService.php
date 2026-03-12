<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\PeriodoLectura;
use App\Models\TarifaPeriodo;
use App\Models\ClienteHistoricoConsumo;
use App\Models\ClienteOtrosCobro;
use App\Models\Factura;
use App\Models\OrdenRevision;
use App\Models\Admin\Ordenesmtl;
use Carbon\Carbon;

class FacturacionService
{
    // Rangos fijos en m³ (se sobreescriben por tarifa si se configuran)
    const BASICO_HASTA       = 16;
    const COMPLEMENTARIO_HASTA = 32;

    /**
     * Calcula todos los conceptos de una factura para un cliente y consumo dado.
     * Devuelve un array con todos los campos listos para crear/previsualizar la Factura.
     *
     * @param  Cliente        $cliente
     * @param  int            $consumoM3
     * @param  PeriodoLectura $periodo
     * @param  int|null       $lecturaAnterior
     * @param  int|null       $lecturaActual
     * @return array
     */
    public function calcular(
        Cliente $cliente,
        int $consumoM3,
        PeriodoLectura $periodo,
        ?int $lecturaAnterior = null,
        ?int $lecturaActual   = null
    ): array {
        \Illuminate\Support\Facades\Log::info('=== INICIO FacturacionService::calcular ===', [
            'cliente_id' => $cliente->id,
            'suscriptor' => $cliente->suscriptor,
            'consumoM3' => $consumoM3,
            'periodo' => $periodo->codigo,
            'tiene_medidor' => $cliente->tiene_medidor,
            'estrato_id' => $cliente->estrato_id,
            'servicios' => $cliente->servicios,
        ]);

        // Sin medidor → se factura con el promedio de los últimos 6 meses.
        // Si tampoco hay historial se usa 1 m³ como mínimo facturable.
        if (!$cliente->tiene_medidor) {
            $consumoM3       = max(1, (int) round($cliente->promedio_consumo));
            $lecturaAnterior = null;
            $lecturaActual   = null;
            \Illuminate\Support\Facades\Log::info('Cliente sin medidor, se usa promedio', [
                'promedio_consumo' => $cliente->promedio_consumo,
                'consumoM3_nuevo' => $consumoM3,
            ]);
        }

        $tarifa    = $periodo->tarifa ?? TarifaPeriodo::vigente();
        $estratoId = $cliente->estrato_id;
        $servicios = $cliente->servicios ?? 'AG-AL';

        \Illuminate\Support\Facades\Log::info('Datos iniciales para calculo', [
            'tarifa_id' => optional($tarifa)->id,
            'estratoId' => $estratoId,
            'servicios' => $servicios,
        ]);

        // ── Historial de consumo (6 meses) ───────────────────────────────────
        $historial = ClienteHistoricoConsumo::promedioYDetalle($cliente->id, 6);
        $promedio  = $historial['promedio'];
        $meses     = array_column($historial['meses'], 'consumo_m3');

        // Fallback: si no hay historial en cliente_historico_consumos, usar
        // consumos reales de periodos anteriores en ordenescu.
        if (empty($meses) && $cliente->tiene_medidor) {
            $prevConsumos = Ordenesmtl::where('Suscriptor', $cliente->suscriptor)
                ->where('Estado', 4)
                ->whereNotNull('Cons_Act')
                ->where('Cons_Act', '>', 0)
                ->whereRaw('CAST(Periodo AS UNSIGNED) < ?', [(int) $periodo->codigo])
                ->orderByRaw('CAST(Periodo AS UNSIGNED) DESC')
                ->limit(6)
                ->pluck('Cons_Act')
                ->toArray();

            if (!empty($prevConsumos)) {
                $promedio = round(array_sum($prevConsumos) / count($prevConsumos), 2);
                $meses    = $prevConsumos;
                \Illuminate\Support\Facades\Log::info('Historial obtenido desde ordenescu (fallback)', [
                    'suscriptor' => $cliente->suscriptor,
                    'meses'      => $meses,
                    'promedio'   => $promedio,
                ]);
            }
        }

        // Override: si existe una orden de revisión EJECUTADA con nueva_lectura
        // para este período y esa lectura es menor a la original, se usa para
        // facturar (siempre que el consumo resultante sea razonable).
        if ($cliente->tiene_medidor && !is_null($lecturaActual) && !is_null($lecturaAnterior)) {
            $revision = OrdenRevision::where('codigo_predio', $cliente->suscriptor)
                ->where('estado_orden', 'EJECUTADO')
                ->whereNotNull('nueva_lectura')
                ->whereHas('lectura', fn($q) => $q->where('periodo_lectura_id', $periodo->id))
                ->latest('id')
                ->first();

            if ($revision && $revision->nueva_lectura < $lecturaActual) {
                $consumoRevision = max(0, $revision->nueva_lectura - $lecturaAnterior);
                // Aceptar si el consumo es no negativo y está dentro del doble del promedio
                // (si no hay promedio, se acepta sin límite superior)
                $limiteAceptable = $promedio > 0 ? $promedio * 2 : PHP_INT_MAX;
                if ($consumoRevision <= $limiteAceptable) {
                    \Illuminate\Support\Facades\Log::info('Lectura de revisión aplicada', [
                        'suscriptor'       => $cliente->suscriptor,
                        'lectura_original' => $lecturaActual,
                        'nueva_lectura'    => $revision->nueva_lectura,
                        'consumo_original' => $consumoM3,
                        'consumo_revision' => $consumoRevision,
                        'promedio'         => $promedio,
                    ]);
                    $lecturaActual = $revision->nueva_lectura;
                    $consumoM3     = $consumoRevision;
                }
            }
        }

        while (count($meses) < 6) $meses[] = null;
        [$m1,$m2,$m3,$m4,$m5,$m6] = $meses;

        // ── Acueducto ─────────────────────────────────────────────────────────
        $acueducto = $this->calcularServicio(
            'ACUEDUCTO', $consumoM3, $estratoId, $tarifa, in_array($servicios, ['AG','AG-AL'])
        );

        // ── Alcantarillado ────────────────────────────────────────────────────
        $alcantarillado = $this->calcularServicio(
            'ALCANTARILLADO', $consumoM3, $estratoId, $tarifa, in_array($servicios, ['AL','AG-AL'])
        );

        // ── Subsidio / Contribución por estrato ───────────────────────────────
        // Aplica sobre consumo básico de ACUEDUCTO y ALCANTARILLADO.
        // Prioridad: valor fijo (subsidio_fijo_*) > porcentaje (porcentaje_subsidio).
        // porcentaje_subsidio > 0 → estratos 1-3: descuento
        // porcentaje_subsidio < 0 → estratos 5-6/COM/IND: sobretasa
        $estrato           = $cliente->estrato;
        $pctSubsidio       = $estrato ? (float) $estrato->porcentaje_subsidio       : 0.0;
        $fijoAcueducto     = $estrato ? (float) ($estrato->subsidio_fijo_acueducto     ?? 0) : 0.0;
        $fijoAlcantarillado= $estrato ? (float) ($estrato->subsidio_fijo_alcantarillado ?? 0) : 0.0;

        // — Acueducto —
        $subsidioAcueducto = 0.0;
        if ($fijoAcueducto != 0) {
            $subsidioAcueducto = round($fijoAcueducto, 2);           // fijo: positivo=descuento
        } elseif ($pctSubsidio != 0 && $acueducto['basico_valor'] > 0) {
            $subsidioAcueducto = round($acueducto['basico_valor'] * $pctSubsidio / 100, 2);
        }
        $acueducto['total'] = round($acueducto['total'] - $subsidioAcueducto, 2);

        // — Alcantarillado —
        $subsidioAlcantarillado = 0.0;
        if ($fijoAlcantarillado != 0) {
            $subsidioAlcantarillado = round($fijoAlcantarillado, 2);
        } elseif ($pctSubsidio != 0 && $alcantarillado['basico_valor'] > 0) {
            $subsidioAlcantarillado = round($alcantarillado['basico_valor'] * $pctSubsidio / 100, 2);
        }
        $alcantarillado['total'] = round($alcantarillado['total'] - $subsidioAlcantarillado, 2);

        // ── Otros cobros activos ──────────────────────────────────────────────
        $otrosAcueducto           = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ACUEDUCTO')->activo()->sum('cuota_mensual');
        $montoTotalOtrosAcueducto = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ACUEDUCTO')->activo()->sum('monto_total');
        $saldoOtrosAcueducto      = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ACUEDUCTO')->activo()->sum('saldo');

        $otrosAlcantarillado           = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ALCANTARILLADO')->activo()->sum('cuota_mensual');
        $montoTotalOtrosAlcantarillado = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ALCANTARILLADO')->activo()->sum('monto_total');
        $saldoOtrosAlcantarillado      = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ALCANTARILLADO')->activo()->sum('saldo');

        // ── Saldo anterior (facturas pendientes o vencidas anteriores) ────────
        $saldoAnterior   = $this->calcularSaldoAnterior($cliente, $periodo->codigo);
        $facturasEnMora  = Factura::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['PENDIENTE','VENCIDA'])
            ->where('periodo', '<', $periodo->codigo)
            ->count();

        // ── Subtotales ────────────────────────────────────────────────────────
        $subtotalConexionOtrosAcueducto     = $acueducto['total'] + $otrosAcueducto;
        $subtotalConexionOtrosAlcantarillado = $alcantarillado['total'] + $otrosAlcantarillado;
        $totalAPagar = $subtotalConexionOtrosAcueducto + $subtotalConexionOtrosAlcantarillado + $saldoAnterior;

        return [
            // Encabezado
            'suscriptor'              => $cliente->suscriptor,
            'cliente_id'              => $cliente->id,
            'periodo_lectura_id'      => $periodo->id,
            'tarifa_periodo_id'       => optional($tarifa)->id,
            'periodo'                 => $periodo->codigo,
            'mes_cuenta'              => $periodo->nombre,
            'fecha_del'               => $periodo->fecha_inicio_lectura,
            'fecha_hasta'             => $periodo->fecha_fin_lectura,
            'fecha_expedicion'        => $periodo->fecha_expedicion,
            'fecha_vencimiento'       => $periodo->fecha_vencimiento,
            'fecha_corte'             => $periodo->fecha_corte,
            // Predio snapshot
            'serie_medidor'           => $cliente->serie_medidor,
            'sector'                  => $cliente->sector,
            'estrato_snapshot'        => optional($cliente->estrato)->numero,
            'clase_uso'               => $cliente->tipo_uso,
            'tiene_medidor_snapshot'  => $cliente->tiene_medidor,
            'servicios_snapshot'      => $servicios,
            // Lectura y promedio
            'lectura_anterior'        => $lecturaAnterior,
            'lectura_actual'          => $lecturaActual,
            'consumo_m3'              => $consumoM3,
            'dias_facturados'         => 30,
            'prom_m1'                 => $m1,
            'prom_m2'                 => $m2,
            'prom_m3'                 => $m3,
            'prom_m4'                 => $m4,
            'prom_m5'                 => $m5,
            'prom_m6'                 => $m6,
            'promedio_consumo_snapshot' => $promedio,
            // Acueducto
            'cargo_fijo_acueducto'                       => $acueducto['cargo_fijo'],
            'consumo_basico_acueducto_m3'                => $acueducto['basico_m3'],
            'consumo_basico_acueducto_valor'             => $acueducto['basico_valor'],
            'consumo_complementario_acueducto_m3'        => $acueducto['complementario_m3'],
            'consumo_complementario_acueducto_valor'     => $acueducto['complementario_valor'],
            'consumo_suntuario_acueducto_m3'             => $acueducto['suntuario_m3'],
            'consumo_suntuario_acueducto_valor'          => $acueducto['suntuario_valor'],
            'subtotal_facturacion_acueducto'             => $acueducto['subtotal'],
            'subsidio_emergencia'                        => $subsidioAcueducto,
            'total_facturacion_acueducto'                => $acueducto['total'],
            'otros_cobros_acueducto'                     => $montoTotalOtrosAcueducto,
            'cuota_otros_cobros_acueducto'               => $otrosAcueducto,
            'saldo_otros_cobros_acueducto'               => max(0, $saldoOtrosAcueducto - $otrosAcueducto),
            'subtotal_conexion_otros_acueducto'          => $subtotalConexionOtrosAcueducto,
            // Alcantarillado
            'cargo_fijo_alcantarillado'                      => $alcantarillado['cargo_fijo'],
            'consumo_basico_alcantarillado_m3'               => $alcantarillado['basico_m3'],
            'consumo_basico_alcantarillado_valor'            => $alcantarillado['basico_valor'],
            'consumo_complementario_alcantarillado_m3'       => $alcantarillado['complementario_m3'],
            'consumo_complementario_alcantarillado_valor'    => $alcantarillado['complementario_valor'],
            'consumo_suntuario_alcantarillado_m3'            => $alcantarillado['suntuario_m3'],
            'consumo_suntuario_alcantarillado_valor'         => $alcantarillado['suntuario_valor'],
            'subtotal_alcantarillado'                        => $alcantarillado['subtotal'],
            'subsidio_alcantarillado'                        => $subsidioAlcantarillado,
            'total_facturacion_alcantarillado'               => $alcantarillado['total'],
            'otros_cobros_alcantarillado'                    => $montoTotalOtrosAlcantarillado,
            'cuota_otros_cobros_alcantarillado'              => $otrosAlcantarillado,
            'saldo_otros_cobros_alcantarillado'              => max(0, $saldoOtrosAlcantarillado - $otrosAlcantarillado),
            'subtotal_conexion_otros_alcantarillado'         => $subtotalConexionOtrosAlcantarillado,
            // Conexión (placeholder — se calcula por separado en otros_cobros con codigo CONEXION_*)
            'conexion_acueducto'              => 0,
            'cuota_conexion_acueducto'        => 0,
            'pagos_conexion_acueducto'        => 0,
            'saldo_conexion_acueducto'        => 0,
            'conexion_alcantarillado'         => 0,
            'cuota_conexion_alcantarillado'   => 0,
            'pagos_conexion_alcantarillado'   => 0,
            'saldo_conexion_alcantarillado'   => 0,
            // Totales
            'saldo_anterior'    => $saldoAnterior,
            'facturas_en_mora'  => $facturasEnMora,
            'total_a_pagar'     => round($totalAPagar, 2),
            // Control
            'estado'            => 'PENDIENTE',
            'es_automatica'     => $cliente->lecturaEsNormal($consumoM3),
            'numero_factura'    => Factura::generarNumero($periodo->codigo),
        ];
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function calcularServicio(
        string $servicio,
        int $consumoM3,
        ?int $estratoId,
        ?TarifaPeriodo $tarifa,
        bool $activo
    ): array {
        \Illuminate\Support\Facades\Log::info('=== INICIO calcularServicio ===', [
            'servicio' => $servicio,
            'consumoM3' => $consumoM3,
            'estratoId' => $estratoId,
            'tarifa_id' => optional($tarifa)->id,
            'activo' => $activo,
        ]);

        if (!$activo || !$estratoId || !$tarifa) {
            \Illuminate\Support\Facades\Log::info('Servicio vacio por condiciones no cumplidas', [
                'activo' => $activo,
                'estratoId' => $estratoId,
                'tarifa' => $tarifa,
            ]);
            return $this->servicioVacio();
        }

        $cargoFijo = $tarifa->cargoFijo($servicio, $estratoId);
        \Illuminate\Support\Facades\Log::info('Cargo fijo obtenido', [
            'servicio' => $servicio,
            'estratoId' => $estratoId,
            'cargoFijo' => $cargoFijo,
        ]);

        $desglose = $tarifa->calcularConsumo($consumoM3, $servicio, $estratoId);
        \Illuminate\Support\Facades\Log::info('Desglose de consumo', [
            'consumoM3' => $consumoM3,
            'desglose' => $desglose,
        ]);

        $datos = [
            'cargo_fijo'           => $cargoFijo,
            'basico_m3'            => 0, 'basico_valor'            => 0,
            'complementario_m3'    => 0, 'complementario_valor'    => 0,
            'suntuario_m3'         => 0, 'suntuario_valor'         => 0,
        ];

        foreach ($desglose as $item) {
            $key = strtolower($item['tipo']);
            $datos["{$key}_m3"]    = $item['m3'];
            $datos["{$key}_valor"] = $item['valor'];
        }

        $subtotal = $cargoFijo + $datos['basico_valor'] + $datos['complementario_valor'] + $datos['suntuario_valor'];

        \Illuminate\Support\Facades\Log::info('=== FIN calcularServicio ===', [
            'servicio' => $servicio,
            'datos_finales' => $datos,
            'subtotal' => $subtotal,
        ]);

        return array_merge($datos, ['subtotal' => round($subtotal, 2), 'total' => round($subtotal, 2)]);
    }

    private function servicioVacio(): array
    {
        return [
            'cargo_fijo' => 0,
            'basico_m3' => 0, 'basico_valor' => 0,
            'complementario_m3' => 0, 'complementario_valor' => 0,
            'suntuario_m3' => 0, 'suntuario_valor' => 0,
            'subtotal' => 0, 'total' => 0,
        ];
    }

    private function calcularSaldoAnterior(Cliente $cliente, string $periodoActual): float
    {
        return (float) Factura::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['PENDIENTE', 'VENCIDA'])
            ->where('periodo', '<', $periodoActual)
            ->get()
            ->sum(fn($f) => $f->saldoPendiente());
    }
}
