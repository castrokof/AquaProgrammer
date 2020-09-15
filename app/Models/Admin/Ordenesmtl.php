<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Ordenesmtl extends Model
{
    protected $table = 'ordenescu';
    protected $fillable = ['ordenescu_id', 'Suscriptor', 'Periodo','Año', 'Mes', 'Ciclo', 'Nombre','Apell', 'Direccion','Telefono', 'Ref_Medidor', 'idDivision', 'id_Ruta', 
    'Ruta', 'Consecutivo', 'Lect_Actual', 'LA', 'Cons_Act', 'Promedio', 'Critica', 'Usuario','nombreu', 'fecha_de_ejecucion', 'recorrido', 'foto1',
     'foto2', 'Coordenada', 'Latitud', 'Longitud', 'Causa_id', 'Observacion_id', 'Causa_des','Observacion_des','Estado_des', 'Estado', 'Tope','consecutivoRuta', 'new_medidor'
    ];
}
