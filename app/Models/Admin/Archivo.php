<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    protected $table = 'archivo';
    protected $fillable = ['nombre', 'fecha', 'registros', 'periodo', 'estado', 'zona', 'usuario', 'cantidad'
        ];
}

