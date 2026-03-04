<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtrosCobrosCatalogo extends Model
{
    protected $table = 'otros_cobros_catalogo';

    protected $fillable = [
        'nombre', 'codigo', 'descripcion',
        'aplica_acueducto', 'aplica_alcantarillado',
        'requiere_diametro', 'permite_cuotas', 'activo',
    ];

    protected $casts = [
        'aplica_acueducto'      => 'boolean',
        'aplica_alcantarillado' => 'boolean',
        'requiere_diametro'     => 'boolean',
        'permite_cuotas'        => 'boolean',
        'activo'                => 'boolean',
    ];

    public function cobrosCliente()
    {
        return $this->hasMany(ClienteOtrosCobro::class, 'catalogo_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
