<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Orden_ejecutada extends Model
{
    protected $table = 'orden_ejecutada';
    protected $fillable = ['id', 'ordenejecutada_id', 'suscriptor', 'usuario', 'tipo_usuario', 'fecha_de_ejecucion', 'new_medidor', 'Cons_Act', 'Comentario', 'Critica', 'Desviacion', 'coordenada', 'latitud', 'longitud', 'estado', 'estado_id', 'foto1', 'foto2','futuro1','futuro2','futuro3','futuro4','futuro5','futuro6'
        ];
}
