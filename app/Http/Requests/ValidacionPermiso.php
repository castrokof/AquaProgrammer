<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidacionPermiso extends FormRequest
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
        return [
            'nombre'  => 'required|max:100|unique:permiso,nombre,'.$this->route('id'),
            'slug'    => 'required|max:100|unique:permiso,slug,'.$this->route('id'),
        ];
    }
}
