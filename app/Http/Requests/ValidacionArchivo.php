<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidacionArchivo extends FormRequest
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
            'file' => 'mimes:xls,xlsx'
            /*'zona' => 'required',
            'poliza' => 'required',
            'direccion' => 'required',
            'recorrido' => 'required',
            'medidor' => 'required',
            'nombre' => 'required',
            'year' => 'required',
            'mes' => 'required',
            'lote' => 'required',
            'periodo' => 'required',
            'consecutivo' => 'required',
            'consecutivo_int' => 'required',
            'ruta' => 'required',
            'tope' => 'required'*/
            ];
            
 }
}