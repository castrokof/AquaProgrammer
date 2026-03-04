<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Ordenesmtl;

class PeriodoLectura extends Model
{
    protected $table = 'periodos_lectura';

    protected $fillable = [
        'codigo', 'nombre', 'ciclo', 'estado', 'tarifa_periodo_id',
        'fecha_inicio_lectura', 'fecha_fin_lectura',
        'fecha_expedicion', 'fecha_vencimiento', 'fecha_corte',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio_lectura' => 'date',
        'fecha_fin_lectura'    => 'date',
        'fecha_expedicion'     => 'date',
        'fecha_vencimiento'    => 'date',
        'fecha_corte'          => 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function tarifa()
    {
        return $this->belongsTo(TarifaPeriodo::class, 'tarifa_periodo_id');
    }

    public function ordenes()
    {
        return $this->hasMany(Ordenesmtl::class, 'periodo_lectura_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'periodo_lectura_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function scopeAbierto($query)
    {
        return $query->whereIn('estado', ['PLANIFICADO', 'ACTIVO', 'LECTURA_CERRADA']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function puedeLeerse(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    public function puedeFacturar(): bool
    {
        return in_array($this->estado, ['LECTURA_CERRADA', 'FACTURADO']);
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
