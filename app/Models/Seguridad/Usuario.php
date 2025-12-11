<?php

namespace App\Models\Seguridad;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Admin\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    protected $table = 'usuario';
    //protected $remember_token = false;
    //protected $guarded = ['id'];
    protected $fillable = [
        
        'usuario', 'nombre', 'tipodeusuario', 'email', 'empresa',  'password', 'remember_token', 'estado', 'api_token'
    ];




    protected $hidden = [
        'password', 'api_token'
    ];

     // ⭐ NUEVO: Especificar el campo de autenticación principal
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function roles1(){
        return $this->belongsToMany(Rol::class, 'usuario_rol');
    }

    public function setSession(){

    $roles1 = $this->roles1()->get()->toArray();

        if (count($roles1) == 1) {
            Session::put(
                [
                    'rol_id' => $roles1[0]['id'],
                    'rol_nombre' => $roles1[0]['nombre'],
                    'usuario' => $this->usuario,
                    'usuario_id' => $this->id,
                    'nombre_usuario' => $this->nombre,
                    'estado'=>$this->estado
                ]
                );
        }
        
    }
    public function setPasswordAttribute($value)
    {
        if ( !empty ($value))
        {
            $this->attributes['password'] = Hash::make($value);
            $this->attributes['remenber_token'] = Hash::make($value);
        }
    }

}
