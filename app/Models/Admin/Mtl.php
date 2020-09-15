<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Mtl extends Model
{
    protected $table = 'mtl';
    protected $fillable = ['mtl_id', 'codigo', 'descripcion'
        ];
}

