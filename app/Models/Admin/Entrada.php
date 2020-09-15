<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Entrada extends Model
{
    protected $table = 'entrada';
    protected $fillable = ['Ciclo', 'Suscriptor', 'Nombre', 'Apell', 'Direccion', 'Ref_Medidor', 'LA', 'Promedio', 'recorrido', 'uso', 'estrato', 'Año', 'Mes', 'id_Ruta', 'Periodo', 'consecutivoRuta', 'consecutivo_int', 'Ruta', 'Tope'
        ];
}
