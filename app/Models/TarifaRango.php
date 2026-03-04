<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaRango extends Model
{
    protected $table = 'tarifa_rangos';

    protected $fillable = [
        'tarifa_periodo_id', 'servicio', 'estrato_id',
        'tipo', 'rango_desde', 'rango_hasta', 'precio_m3',
    ];

    protected $casts = [
        'rango_desde' => 'integer',
        'rango_hasta' => 'integer',
        'precio_m3'   => 'decimal:4',
    ];

    public function periodo()
    {
        return $this->belongsTo(TarifaPeriodo::class, 'tarifa_periodo_id');
    }

    public function estrato()
    {
        return $this->belongsTo(Estrato::class, 'estrato_id');
    }
}
