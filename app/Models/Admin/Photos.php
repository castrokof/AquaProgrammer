<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Photos extends Model
{
    protected $table = 'photos';
    protected $fillable = ['id_orden_ejecutada', 'photo_data'];
}
