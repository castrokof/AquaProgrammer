<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaCargoFijo extends Model
{
    protected $table = 'tarifa_cargos_fijos';

    protected $fillable = ['tarifa_periodo_id', 'servicio', 'estrato_id', 'cargo_fijo'];

    protected $casts = ['cargo_fijo' => 'decimal:2'];

    public function periodo()
    {
        return $this->belongsTo(TarifaPeriodo::class, 'tarifa_periodo_id');
    }

    public function estrato()
    {
        return $this->belongsTo(Estrato::class, 'estrato_id');
    }
}
