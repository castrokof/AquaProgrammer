<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\Usuario;

class MacroLectura extends Model
{
    protected $table = 'macro_lecturas';

    protected $fillable = [
        'macromedidor_id',
        'usuario_id',
        'lectura_anterior',
        'lectura_actual',
        'consumo',
        'observacion',
        'gps_latitud',
        'gps_longitud',
        'fecha_lectura',
        'sincronizado',
    ];

    protected $casts = [
        'lectura_anterior' => 'integer',
        'lectura_actual'   => 'integer',
        'consumo'          => 'integer',
        'gps_latitud'      => 'double',
        'gps_longitud'     => 'double',
        'sincronizado'     => 'boolean',
        'fecha_lectura'    => 'datetime',
    ];

    public function macromedidor()
    {
        return $this->belongsTo(Macromedidor::class, 'macromedidor_id');
    }

    public function fotos()
    {
        return $this->hasMany(MacroLecturaFoto::class, 'macro_lectura_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
