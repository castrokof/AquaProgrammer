<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\PeriodoLectura;
use App\Models\TarifaPeriodo;
use App\Models\ClienteHistoricoConsumo;
use App\Models\ClienteOtrosCobro;
use App\Models\Factura;
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
        // Sin medidor → se factura con el promedio de los últimos 6 meses.
        // Si tampoco hay historial se usa 1 m³ como mínimo facturable.
        if (!$cliente->tiene_medidor) {
            $consumoM3       = max(1, (int) round($cliente->promedio_consumo));
            $lecturaAnterior = null;
            $lecturaActual   = null;
        }

        $tarifa    = $periodo->tarifa ?? TarifaPeriodo::vigente();
        $estratoId = $cliente->estrato_id;
        $servicios = $cliente->servicios ?? 'AG-AL';

        // ── Historial de consumo (6 meses) ───────────────────────────────────
        $historial = ClienteHistoricoConsumo::promedioYDetalle($cliente->id, 6);
        $meses     = array_column($historial['meses'], 'consumo_m3');
        while (count($meses) < 6) $meses[] = null;
        [$m1,$m2,$m3,$m4,$m5,$m6] = $meses;
        $promedio  = $historial['promedio'];

        // ── Acueducto ─────────────────────────────────────────────────────────
        $acueducto = $this->calcularServicio(
            'ACUEDUCTO', $consumoM3, $estratoId, $tarifa, in_array($servicios, ['AG','AG-AL'])
        );

        // ── Alcantarillado ────────────────────────────────────────────────────
        $alcantarillado = $this->calcularServicio(
            'ALCANTARILLADO', $consumoM3, $estratoId, $tarifa, in_array($servicios, ['AL','AG-AL'])
        );

        // ── Subsidio / Contribución por estrato ───────────────────────────────
        // porcentaje_subsidio > 0  → estratos 1-3 reciben descuento (subsidio)
        // porcentaje_subsidio < 0  → estratos 5-6/COM/IND pagan sobretasa (contribución)
        // Se aplica sobre el consumo básico de acueducto únicamente.
        $pctSubsidio = $cliente->estrato ? (float) $cliente->estrato->porcentaje_subsidio : 0.0;
        $subsidioAcueducto = 0.0;
        if ($pctSubsidio != 0 && $acueducto['basico_valor'] > 0) {
            $subsidioAcueducto = round($acueducto['basico_valor'] * $pctSubsidio / 100, 2);
            // Positivo = descuenta del total; negativo = aumenta el total.
            $acueducto['total'] = round($acueducto['total'] - $subsidioAcueducto, 2);
        }

        // ── Otros cobros activos ──────────────────────────────────────────────
        $otrosAcueducto      = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ACUEDUCTO')->activo()->sum('cuota_mensual');
        $saldoOtrosAcueducto = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ACUEDUCTO')->activo()->sum('saldo');

        $otrosAlcantarillado      = ClienteOtrosCobro::where('cliente_id', $cliente->id)
            ->where('tipo_servicio', 'ALCANTARILLADO')->activo()->sum('cuota_mensual');
        $saldoOtrosAlcantarillado = ClienteOtrosCobro::where('cliente_id', $cliente->id)
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
            'otros_cobros_acueducto'                     => $otrosAcueducto,
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
            'otros_cobros_alcantarillado'                    => $otrosAlcantarillado,
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
        if (!$activo || !$estratoId || !$tarifa) {
            return $this->servicioVacio();
        }

        $cargoFijo = $tarifa->cargoFijo($servicio, $estratoId);
        $desglose  = $tarifa->calcularConsumo($consumoM3, $servicio, $estratoId);

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
