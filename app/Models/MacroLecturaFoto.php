<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacroLecturaFoto extends Model
{
    protected $table = 'macro_lectura_fotos';

    protected $fillable = [
        'macro_lectura_id',
        'ruta_foto',
    ];

    public function lectura()
    {
        return $this->belongsTo(MacroLectura::class, 'macro_lectura_id');
    }
}
