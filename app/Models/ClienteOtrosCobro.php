<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteOtrosCobro extends Model
{
    protected $table = 'cliente_otros_cobros';

    protected $fillable = [
        'cliente_id', 'catalogo_id', 'tipo_servicio',
        'concepto', 'diametro', 'observaciones',
        'monto_total', 'num_cuotas', 'cuota_mensual',
        'cuotas_pagadas', 'saldo', 'fecha_inicio', 'estado', 'usuario_id',
    ];

    protected $casts = [
        'monto_total'    => 'decimal:2',
        'cuota_mensual'  => 'decimal:2',
        'saldo'          => 'decimal:2',
        'fecha_inicio'   => 'date',
        'cuotas_pagadas' => 'integer',
        'num_cuotas'     => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function catalogo()
    {
        return $this->belongsTo(OtrosCobrosCatalogo::class, 'catalogo_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function scopeAcueducto($query)
    {
        return $query->where('tipo_servicio', 'ACUEDUCTO');
    }

    public function scopeAlcantarillado($query)
    {
        return $query->where('tipo_servicio', 'ALCANTARILLADO');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Registra el pago de una cuota y actualiza saldo */
    public function pagarCuota(): void
    {
        $this->cuotas_pagadas += 1;
        $this->saldo = max(0, $this->saldo - $this->cuota_mensual);

        if ($this->cuotas_pagadas >= $this->num_cuotas || $this->saldo <= 0) {
            $this->estado = 'PAGADO';
            $this->saldo  = 0;
        }

        $this->save();
    }

    public function estaAlDia(): bool
    {
        return $this->estado !== 'ACTIVO' || $this->saldo <= 0;
    }
}
