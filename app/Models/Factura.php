<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        // Encabezado
        'numero_factura', 'suscriptor', 'cliente_id', 'periodo_lectura_id', 'tarifa_periodo_id',
        'periodo', 'mes_cuenta', 'fecha_del', 'fecha_hasta', 'fecha_expedicion',
        'fecha_vencimiento', 'fecha_corte',
        // Predio (snapshot)
        'serie_medidor', 'sector', 'estrato_snapshot', 'clase_uso',
        'tiene_medidor_snapshot', 'servicios_snapshot',
        // Lectura y promedio
        'lectura_anterior', 'lectura_actual', 'consumo_m3', 'dias_facturados',
        'prom_m1', 'prom_m2', 'prom_m3', 'prom_m4', 'prom_m5', 'prom_m6',
        'promedio_consumo_snapshot',
        // Acueducto
        'cargo_fijo_acueducto',
        'consumo_basico_acueducto_m3', 'consumo_basico_acueducto_valor',
        'consumo_complementario_acueducto_m3', 'consumo_complementario_acueducto_valor',
        'consumo_suntuario_acueducto_m3', 'consumo_suntuario_acueducto_valor',
        'subtotal_facturacion_acueducto', 'subsidio_emergencia', 'total_facturacion_acueducto',
        'otros_cobros_acueducto', 'cuota_otros_cobros_acueducto',
        'saldo_otros_cobros_acueducto', 'subtotal_conexion_otros_acueducto',
        // Alcantarillado
        'cargo_fijo_alcantarillado',
        'consumo_basico_alcantarillado_m3', 'consumo_basico_alcantarillado_valor',
        'consumo_complementario_alcantarillado_m3', 'consumo_complementario_alcantarillado_valor',
        'consumo_suntuario_alcantarillado_m3', 'consumo_suntuario_alcantarillado_valor',
        'subtotal_alcantarillado', 'subsidio_alcantarillado', 'total_facturacion_alcantarillado',
        'otros_cobros_alcantarillado', 'cuota_otros_cobros_alcantarillado',
        'saldo_otros_cobros_alcantarillado', 'subtotal_conexion_otros_alcantarillado',
        // Conexión
        'conexion_acueducto', 'cuota_conexion_acueducto',
        'pagos_conexion_acueducto', 'saldo_conexion_acueducto',
        'conexion_alcantarillado', 'cuota_conexion_alcantarillado',
        'pagos_conexion_alcantarillado', 'saldo_conexion_alcantarillado',
        // Totales y control
        'saldo_anterior', 'facturas_en_mora', 'total_a_pagar',
        'estado', 'es_automatica', 'orden_revision_id', 'usuario_id', 'observaciones',
    ];

    protected $casts = [
        'fecha_del'          => 'date',
        'fecha_hasta'        => 'date',
        'fecha_expedicion'   => 'date',
        'fecha_vencimiento'  => 'date',
        'fecha_corte'        => 'date',
        'es_automatica'      => 'boolean',
        'tiene_medidor_snapshot' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function periodoLectura()
    {
        return $this->belongsTo(PeriodoLectura::class, 'periodo_lectura_id');
    }

    public function tarifaPeriodo()
    {
        return $this->belongsTo(TarifaPeriodo::class, 'tarifa_periodo_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePendiente($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopeVencida($query)
    {
        return $query->where('estado', 'VENCIDA');
    }

    public function scopeMora($query)
    {
        return $query->whereIn('estado', ['PENDIENTE', 'VENCIDA'])
                     ->where('fecha_vencimiento', '<', now()->toDateString());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Total pagado hasta ahora (suma de todos los pagos) */
    public function totalPagado(): float
    {
        return (float) $this->pagos()->sum('total_pago_realizado');
    }

    /** Saldo real pendiente después de pagos registrados */
    public function saldoPendiente(): float
    {
        return max(0, $this->total_a_pagar - $this->totalPagado());
    }

    public function estaPagada(): bool
    {
        return $this->estado === 'PAGADA' || $this->saldoPendiente() <= 0;
    }

    public function estaVencida(): bool
    {
        return $this->estado === 'VENCIDA'
            || ($this->estado === 'PENDIENTE' && $this->fecha_vencimiento < now()->toDateString());
    }

    /**
     * Genera el número de factura correlativo por período.
     * Formato: {año}{mes}{secuencia5digitos}  ej: 2024040048630
     * O puede recibir el número externo del sistema legado.
     */
    public static function generarNumero(string $periodo): string
    {
        $ultimo = self::where('periodo', $periodo)->max('numero_factura');
        $secuencia = $ultimo ? ((int) substr($ultimo, -5) + 1) : 1;
        return $periodo . str_pad($secuencia, 5, '0', STR_PAD_LEFT);
    }
}
