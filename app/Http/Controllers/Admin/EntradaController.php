<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
//use GuzzleHttp\Client;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Admin\Archivo;
use App\Models\Admin\Entrada;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ValidacionArchivo;
use App\Imports\EntradaImport;

class EntradaController extends Controller
 {  
     
             public function sincronizarApi()
{
    // Petición GET con PHP puro
    $json = @file_get_contents('http://localhost/Acusyscom_Backend/public/api');

    if ($json === false) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al conectarse a la API'
        ], 500);
    }

    $payload = json_decode($json, true);

    if (!isset($payload['data'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'No se encontraron datos'
        ]);
    }

    $ciclo = null;
    $periodo = null;

    foreach ($payload['data'] as $filaentrada1) {
        $ciclo = $filaentrada1['ciclo'];
        $periodo = $filaentrada1['year'] . $filaentrada1['mes'];

        $Rows = Entrada::where([
            ['Ciclo', $filaentrada1['ciclo']],
            ['Suscriptor', $filaentrada1['suscriptor']],
            ['Periodo', $periodo]
        ])->count();

        if ($Rows == 0) {
            Entrada::create([
                'Ciclo'          => $filaentrada1['ciclo'],
                'Suscriptor'     => $filaentrada1['suscriptor'],
                'Nombre'         => $filaentrada1['usuario'],
                'Apell'          => 'APELLIDO',
                'Ref_Medidor'    => trim($filaentrada1['medidor']),
                'Direccion'      => $filaentrada1['direccion'],
                'LA'             => $filaentrada1['lec_anterior'],
                'Promedio'       => $filaentrada1['promedio'],
                'recorrido'      => $filaentrada1['consecutivo'],
                'uso'            => $filaentrada1['uso'],
                'estrato'        => null,
                'Año'            => $filaentrada1['year'],
                'Mes'            => $filaentrada1['mes'],
                'id_Ruta'        => $filaentrada1['ruta'],
                'Periodo'        => $periodo,
                'consecutivoRuta'=> $filaentrada1['consecutivo'],
                'consecutivo_int'=> $filaentrada1['consecutivo'],
                'Ruta'           => $filaentrada1['consecutivo'].'_'.$filaentrada1['suscriptor'].'RUTA',
                'Tope'           => '10',
                'id_lectura'     => $filaentrada1['id_lectura'],
                'servicio'       => $filaentrada1['servicio'],
            ]);
        }
    }

    if ($ciclo && $periodo) {
        $Total = Entrada::where([
            ['Ciclo', $ciclo],
            ['Periodo', $periodo],
        ])->count();

        Archivo::create([
            'nombre'   => $periodo . now(),
            'fecha'    => now(),
            'registros'=> $Total,
            'periodo'  => $periodo,
            'estado'   => 'Cargado desde API',
            'zona'     => $ciclo,
            'usuario'  => Auth::user()->usuario,
            'cantidad' => $Total,
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Datos sincronizados desde API',
        'data' => $payload['data']
    ]);
}


    public function guardar(Request $request)
    {     
       
   if($request->ajax()){
      $file = $request->file('file'); 
        
        if($file == null){

         return response()->json(['mensaje' => 'vacio']);//return redirect('admin/archivo')->with('mensaje', 'No seleccionaste ningun archivo');

        
        }else{
            
        $name=time().$file->getClientOriginalName();  
              
         $destinationPath = public_path('xlsxin/');
        
         $file->move($destinationPath, $name);
        
         $path=$destinationPath.$name;
         
 
              try {
                  
              $pruebas =  new EntradaImport;       
              Excel::import($pruebas,$path);
                
           
                $rows1 = $pruebas->data;
                
                } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                // $failures = $e->failures();
                
            }
            
        if($rows1==1){
          
             return response()->json(['mensaje' => 'ok']); //return redirect('admin/archivo')->with('mensaje', 'Registros duplicados en base de datos');}
             
            
        }else if($rows1==2){
                
               return response()->json(['mensaje' => 'ng']); //return redirect('admin/archivo')->with('mensaje', 'Registros duplicados en base de datos');}  
             }

        }
   
    }

   }
    public function importaExcel(request $request, row $row)

    {
        
        
 
 
 // Guardo la colección en $file

 $file = $request->file('file');             


 $name=time().$file->getClientOriginalName();  
                  

 $destinationPath = public_path('xlsxin/');

 $file->move($destinationPath, $name);

 $path=$destinationPath.$name;
 
 
              try {
               Excel::import(new EntradaImport,$path);
                
               return $row = 1;
                
                } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                // $failures = $e->failures();
                    return $row=2;
            }
            
            


        }         
   }        
    
              



  

