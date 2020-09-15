<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Marcas extends Model
{
    protected $table = 'marcas';
    protected $fillable = ['marca_id', 'codigo', 'descripcion'
        ];
}
