<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Admin\Archivo;
use App\Models\Admin\Entrada;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ValidacionArchivo;

class EntradaController extends Controller
 {
  
 public $rows=0;
  
    public function guardar(ValidacionArchivo $request)
    {     
       
   if($request->ajax()){
      $file = $request->file('file'); 
        
        if($file == null){

         return response()->json(['mensaje' => 'vacio']);//return redirect('admin/archivo')->with('mensaje', 'No seleccionaste ningun archivo');

        
        }else{
            
        $this->importaExcel($request);
         
            
            $row = $this->$rows;
           

            if($row==1){
           
               return response()->json(['mensaje' => 'ng']); //return redirect('admin/archivo')->with('mensaje', 'Registros duplicados en base de datos');
                
            }else{
            
               return response()->json(['mensaje' => 'ok']); //return redirect('admin/archivo')->with('mensaje', 'Archivo cargado exitosamente');
            }

        }
   
    }

   }
    public function importaExcel(request $request)

    {

 // Guardo la colección en $file

 $file = $request->file('file');             


 $name=time().$file->getClientOriginalName();  
                  

 $destinationPath = public_path('xlsxin/');

 $file->move($destinationPath, $name);

 $path=$destinationPath.$name;
 
 
 
 $archivo = new Archivo;

             $archivo->nombre=$name;
             $archivo->fecha=now();
             $archivo->registros=0;
             $archivo->periodo=0;
             $archivo->estado='Cargado';
             $archivo->zona='zona';
             $archivo->usuario=auth()->user()->usuario;
             $archivo->cantidad=0;

             $archivo->save();
 
 

 Excel::load($path, function($reader) { 

        foreach ($reader->get() as $fila1=>$filaentrada2) 
         {     
                 $rows1 = DB::table('entrada')
                    ->where([
                    ['Periodo', '=', $filaentrada2[6].$filaentrada2[7]],
                    ['Ciclo', '=', $filaentrada2[8]],
                    ['Suscriptor', '=', $filaentrada2[0]],])
                    ->count();    
         }
                

if($rows1==0){
 
                    $count=0; 
                    $consecutivo=1;
                       
                         
                   
                   
                       foreach ($reader->get() as $fila=>$filaentrada1) 
                       {  
                                   
                   
                                    $filaentrada = new Entrada;
                                  
                                       $filaentrada->Ciclo=$filaentrada1[8];
                                       $filaentrada->Suscriptor=$filaentrada1[0]; 
                                       $filaentrada->Nombre=$filaentrada1[1];
                                       $filaentrada->Apell='APELLIDO'; 
                                  trim($filaentrada->Ref_Medidor=$filaentrada1[3]);
                                       $filaentrada->Direccion=$filaentrada1[2];
                                       $filaentrada->LA=$filaentrada1[4];
                                       $filaentrada->Promedio=$filaentrada1[5]; 
                                       $filaentrada->recorrido=$filaentrada1[10];
                                       $filaentrada->uso=NULL;
                                       $filaentrada->estrato=NULL;
                                       $filaentrada->Año=$filaentrada1[6];
                                       $filaentrada->Mes=$filaentrada1[7];
                                       $filaentrada->id_Ruta=$filaentrada1[9];
                                       $filaentrada->Periodo=$filaentrada1[6].$filaentrada1[7];
                                       $filaentrada->consecutivoRuta=$filaentrada1[10];
                                       $filaentrada->consecutivo_int=$consecutivo; 
                                       $filaentrada->Ruta=$filaentrada1[10].'_'.$filaentrada1[0].'RUTA'; 
                                       $filaentrada->Tope=$filaentrada1[11];                                     
                   
                                       $filaentrada->save();
                   
                                       
                   
                                              
                   
                          // Incrementamos contado para ver cuantos usuarios se importan.             
                          $count++; 
                          $consecutivo++;
                   
                           
                       }
                       
                       
                       $ids = DB::table('archivo')
                        ->select('id')
                        ->orderByDesc('id')
                        ->limit(1)
                        ->get();
                        
                        
                   
                       $rows = DB::table('entrada')
                       ->select('Periodo', 'Ciclo')
                       ->orderByDesc('id')
                       ->limit(1)
                       ->get();
                   
                   
                       //dd($ids);
                      
                       foreach ($ids as $id) { 
                       foreach ($rows as $row) {
                          
                       DB::table('archivo')
                       ->where('id',$id->id)
                       ->update(['cantidad' => $count,
                                'periodo'=> $row->Periodo,
                                'estado'=>'Procesado',
                                'zona'=>$row->Ciclo,
                                'registros' => $count
                   
                          ]);
                   
                                              }
                           
                                            }
                  
                  
                }else{
                           
                        $this->$rows = 1; 
                     }  
                   
             });

        }         
   }        
    
              



  

