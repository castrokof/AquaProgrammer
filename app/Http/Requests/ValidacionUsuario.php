<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidacionUsuario extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if($this->route('id')){
            
        return [
            
            'nombre'  => 'required|max:100',
            'tipodeusuario'  => 'required',
            'email'  => 'required|email|max:100|unique:usuario,email,'.$this->route('id'),
            'empresa'  => 'required|max:50',
            'estado'  => 'required',
            'rol_id' => 'required|integer'
        ];
            
        }else{
        
        
        
        return [
            'usuario'  => 'required|max:50|unique:usuario,usuario,'.$this->route('id'),
            'nombre'  => 'required|max:100',
            'tipodeusuario'  => 'required',
            'email'  => 'required|email|max:100|unique:usuario,email,'.$this->route('id'),
            'empresa'  => 'required|max:50',
            'password'  => 'required|min:6',
            'remenber_token'  => 'required|same:password',
            'estado'  => 'required',
            'rol_id' => 'required|integer'
        ];
    
            
            }
        }
}
