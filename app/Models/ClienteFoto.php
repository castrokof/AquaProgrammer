<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteFoto extends Model
{
    protected $table = 'cliente_fotos';

    protected $fillable = [
        'cliente_id',
        'ruta_foto',
        'tipo',
        'orden_ejecutada_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
