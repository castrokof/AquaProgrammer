<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'factura_id', 'fecha_pago', 'numero_recibo', 'medio_pago', 'banco',
        'referencia_pasarela', 'estado_pasarela',
        'pagos_acueducto', 'pagos_alcantarillado',
        'pago_otros_cobros_acueducto', 'pago_otros_cobros_alcantarillado',
        'pago_conexion_acueducto', 'pago_conexion_alcantarillado',
        'total_pago_realizado', 'usuario_id', 'observaciones',
    ];

    protected $casts = [
        'fecha_pago'                       => 'date',
        'pagos_acueducto'                  => 'decimal:2',
        'pagos_alcantarillado'             => 'decimal:2',
        'pago_otros_cobros_acueducto'      => 'decimal:2',
        'pago_otros_cobros_alcantarillado' => 'decimal:2',
        'pago_conexion_acueducto'          => 'decimal:2',
        'pago_conexion_alcantarillado'     => 'decimal:2',
        'total_pago_realizado'             => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    // ── Hooks ─────────────────────────────────────────────────────────────────

    protected static function booted()
    {
        // Al crear un pago, recalcular automáticamente el total y el estado de la factura
        static::created(function (self $pago) {
            // fresh(['pagos']) recarga la relación para que saldoPendiente() incluya el nuevo pago
            $factura = $pago->factura->fresh(['pagos']);

            if ($factura->saldoPendiente() <= 0) {
                $factura->update(['estado' => 'PAGADA']);
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Calcula el total sumando todos los conceptos antes de guardar */
    public function calcularTotal(): float
    {
        return (float) (
            $this->pagos_acueducto +
            $this->pagos_alcantarillado +
            $this->pago_otros_cobros_acueducto +
            $this->pago_otros_cobros_alcantarillado +
            $this->pago_conexion_acueducto +
            $this->pago_conexion_alcantarillado
        );
    }
}
