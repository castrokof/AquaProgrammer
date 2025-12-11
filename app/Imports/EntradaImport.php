<?php

namespace App\Imports;

use App\Models\Admin\Entrada;
use App\Models\Admin\Archivo;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class EntradaImport implements ToCollection, WithHeadingRow
{
   
  public $data = 0;
   
     public function collection(Collection $rowscoll)
    {
        
        $this->data = 0;
        $ciclo = null;
        $periodo = null;
        
        
         foreach ($rowscoll as $filaentrada1) {
             
             $ciclo = $filaentrada1['ciclo'];
            $periodo = $filaentrada1['year'] . $filaentrada1['mes'];
        
        $Rows = Entrada::where([
        ['Ciclo',$filaentrada1['ciclo']],
        ['Suscriptor',$filaentrada1['suscriptor']],
        ['Periodo', $filaentrada1['year'].$filaentrada1['mes'] ]]
        
        
        )
        ->count();
        
        
        
        if($Rows == 0){
            
         Entrada::create([
            'Ciclo'=> $filaentrada1['ciclo'],
            'Suscriptor'=> $filaentrada1['suscriptor'], 
            'Nombre'=> $filaentrada1['usuario'],
            'Apell'=> 'APELLIDO', 
            'Ref_Medidor'=> trim($filaentrada1['medidor']),
            'Direccion'=> $filaentrada1['direccion'],
            'LA'=> $filaentrada1['lec_anterior'],
            'Promedio'=> $filaentrada1['promedio'], 
            'recorrido'=> $filaentrada1['consecutivo'],
            'uso'=>  $filaentrada1['uso'],
            'estrato'=> NULL,
            'Año'=> $filaentrada1['year'],
            'Mes'=> $filaentrada1['mes'],
            'id_Ruta'=> $filaentrada1['ruta'],
            'Periodo'=> $filaentrada1['year'].$filaentrada1['mes'],
            'consecutivoRuta'=> $filaentrada1['consecutivo'],
            'consecutivo_int'=> $filaentrada1['consecutivo'], 
            'Ruta'=> $filaentrada1['consecutivo'].'_'.$filaentrada1['suscriptor'].'RUTA', 
            'Tope'=> '10',         
            'id_lectura'=> $filaentrada1['id_lectura'],         
             'servicio'=>  $filaentrada1['servicio'],
             ]);
             
             
            
             
             
             
        }else{
        
       $Total = Entrada::where([
                    ['Ciclo', $ciclo],
                    ['Periodo', $periodo],
                ])->count();
        
         
         
          Archivo::create([
                    'nombre' => $periodo . now(),
                    'fecha' => now(),
                    'registros' => $Total,
                    'periodo' => $periodo,
                    'estado' => 'Repetido',
                    'zona' => $ciclo,
                    'usuario' => Auth::user()->usuario,
                    'cantidad' => 0,
                ]);

                
            
           $data = 2;
           return $this->data = $data;
            
            
            
        }
        
         }
         
        if($this->data != 2){
         
        $Total = Entrada::where([
                ['Ciclo', $ciclo],
                ['Periodo', $periodo],
            ])->count();
        
         
         
          $archivo = new Archivo;

             $archivo->nombre=$periodo . now();
             $archivo->fecha=now();
             $archivo->registros= $Total;
             $archivo->periodo=$periodo;
             $archivo->estado='Cargado';
             $archivo->zona=$ciclo;
             $archivo->usuario=auth()->user()->usuario;
             $archivo->cantidad= $Total;

             $archivo->save();
             
             $data = 1;
       
             $this->data = $data;
             
         }
         
          
        
        
        
    }
    
    
    
      /* public function rules()
    {
        return [
           
             '*.suscriptor' => ['integer', 'required'],
             '*.ciclo' => [ 'required'],
             '*.usuario' => ['required'],
             '*.medidor' => [ 'required'],
             '*.direccion' => [ 'required'],
             '*.lec_anterior' => [ 'integer', 'required'],
             '*.promedio' => [ 'integer', 'required'],
             '*.consecutivo' => [ 'required'],
             '*.year' => [ 'integer', 'required'],
             '*.mes' => [ 'integer', 'required'],
             '*.ruta' => [ 'required'],
             '*.tope' => [ 'integer','required']
           
              ];
    }*/
    
    
}