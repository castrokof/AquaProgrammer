<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estrato extends Model
{
    protected $table = 'estratos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'numero', 'nombre', 'codigo', 'porcentaje_subsidio',
        'subsidio_fijo_acueducto', 'subsidio_fijo_alcantarillado', 'activo',
    ];

    protected $casts = [
        'numero'                       => 'integer',
        'porcentaje_subsidio'          => 'decimal:2',
        'subsidio_fijo_acueducto'      => 'decimal:2',
        'subsidio_fijo_alcantarillado' => 'decimal:2',
        'activo'                       => 'boolean',
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'estrato_id');
    }

    public function cargos()
    {
        return $this->hasMany(TarifaCargoFijo::class, 'estrato_id');
    }

    public function rangos()
    {
        return $this->hasMany(TarifaRango::class, 'estrato_id');
    }

    /** Indica si aplica subsidio (estratos 1-3) */
    public function tieneSubsidio(): bool
    {
        return $this->porcentaje_subsidio > 0;
    }

    /** Indica si aplica sobretasa (estratos 5-6, comercial, industrial) */
    public function tieneSobretasa(): bool
    {
        return $this->porcentaje_subsidio < 0;
    }
}
