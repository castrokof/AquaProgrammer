<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Ordenesmtl;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\ImageManagerStatic as Image;
use Barryvdh\DomPDF\Facade as PDF;



class  OrdenesmtlasignarController extends Controller
{

//selec de idDivision
  public function idDivisionss(Request $request)
  {   
    if(request()->ajax())
    {    
      $idDivisions=Ordenesmtl::groupBy('idDivision') ->where([
        ['Periodo','=',$request->P],
        ['Ciclo','=',$request->C]])->orderBy('idDivision')->pluck('idDivision');

        return response()->json($idDivisions);
    }
      
  }

// Filtro de ordedescu para asignacion  
  public function index(Request $request)
    {   
      
        $fechaAi=now()->toDateString()." 00:00:01";
        $fechaAf=now()->toDateString()." 23:59:59";
       
        if(request()->ajax())
        {    
        
       
        if(!empty($request->Periodo) && !empty($request->Ciclo) && !empty($request->ruta) && !empty($request->Estado) && empty($request->orden) && empty($request->ordenf)){

            //$datas=DB::table('ordenesmtl')
            $datas=Ordenesmtl::orderBy('id')
            ->where([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['idDivision','=',$request->ruta],
            ['Estado_des','=',$request->Estado]
            ])
            //->whereBetween('ordenesmtl_id', [$request->orden, $request->ordenf])    
            // ->select('id','ordenesmtl_id', 'Estado', 'usuario', 'suscriptor','direccion','recorrido',
            //     'Periodo','Ciclo')
            ->get();
        
        
        
            
        }elseif(!empty($request->Periodo) && !empty($request->Ciclo) && !empty($request->ruta) && !empty($request->Estado) && !empty($request->orden) && !empty($request->ordenf)){  
            
            // $datas=DB::table('ordenesmtl')
            $datas=Ordenesmtl::orderBy('id')
            ->where([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['idDivision','=',$request->ruta],
            ['Estado_des','=',$request->Estado]
            ])
            ->whereBetween('Consecutivo', [$request->orden, $request->ordenf])    
            // ->select('id','ordenesmtl_id', 'Estado', 'usuario', 'suscriptor','direccion','recorrido',
            //     'Periodo','Ciclo')
            ->get();
        }else{      
            //$datas=DB::table('ordenesmtl')
            $datas=Ordenesmtl::orderBy('id')
            ->where([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['Estado_des','=',$request->Estado]
            ])
            ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
            // // ->select('ordenesmtl_id', 'Estado', 'usuario', 'suscriptor','direccion','recorrido',
            // //     'Periodo','Ciclo')
            ->get();   

            }  
            
            return  DataTables()->of($datas)
            ->addColumn('checkbox','<input type="checkbox" name="case[]"  value="{{$id}}" class="case" title="Selecciona Orden"
            />')
            ->rawColumns(['checkbox'])
            ->make(true);
        }
      
        $usuarios=Usuario::orderBy('id')->where('tipodeusuario','movil')->pluck('usuario', 'id');
           
        
        return view('admin.ordenes.index', compact('usuarios'));   
    }
   
// Funcion filtrar critica

  public function critica(Request $request)
  {   

  $fechaAi=now()->toDateString()." 00:00:01";
  $fechaAf=now()->toDateString()." 23:59:59";
          
        if(request()->ajax())
        {    
            
        $all = $request->Critica;
        
        if(!empty($request->Periodo) && !empty($request->Ciclo) && !empty($request->Ruta) && $all == "ALL" && !empty($request->Generado)){

            
          $datas=Ordenesmtl::orderBy('id')
          ->where([
          ['Periodo','=',$request->Periodo],
          ['Ciclo','=',$request->Ciclo],
          ['idDivision','=',$request->Ruta],
          ['Critica','!=',"NORMAL"],
          ['Estado','=', 4],
          ['Coordenada','=', $request->Generado]
          ])
        
          ->get();
      
                  
          
      }else if(!empty($request->Periodo) && !empty($request->Ciclo) && !empty($request->Ruta) && !empty($request->Critica) && !empty($request->Generado)){

            
        $datas=Ordenesmtl::orderBy('id')
        ->where([
        ['Periodo','=',$request->Periodo],
        ['Ciclo','=',$request->Ciclo],
        ['idDivision','=',$request->Ruta],
        ['Critica','=',$request->Critica],
        ['Estado','=', 4],
        ['Coordenada','=', $request->Generado]
        ])
      
        ->get();
    
                
        
    }else if(!empty($request->Periodo) && !empty($request->Ciclo) && $all == "ALL" && !empty($request->Generado)){

            
            $datas=Ordenesmtl::orderBy('id')
            ->where([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['Critica','!=',"NORMAL"],
            ['Estado','=', 4],
            ['Coordenada','=', $request->Generado]
            ])
          
            ->get();
        
                    
            
        }else if(!empty($request->Periodo) && !empty($request->Ciclo)  && !empty($request->Ruta) && $all == "ALL"){

            
          $datas=Ordenesmtl::orderBy('id')
          ->where([
          ['Periodo','=',$request->Periodo],
          ['Ciclo','=',$request->Ciclo],
          ['idDivision','=',$request->Ruta],
          ['Critica','!=', "NORMAL"],
          ['Estado','=', 4],
          ['Coordenada','=', '']
          ])
        
          ->get();
      
                  
          
      }else if(!empty($request->Periodo) && !empty($request->Ciclo) && !empty($request->Ruta) &&  !empty($request->Critica)){

            
          $datas=Ordenesmtl::orderBy('id')
          ->where([
          ['Periodo','=',$request->Periodo],
          ['Ciclo','=',$request->Ciclo],
          ['idDivision','=',$request->Ruta],
          ['Critica','=',$request->Critica],
          ['Estado','=', 4],
          ['Coordenada','=', '']
          ])
        
          ->get();
      
                  
          
      }else{      
          
          $datas=Ordenesmtl::orderBy('id')
          ->where([
          ['Periodo','=',$request->Periodo],
          ['Ciclo','=',$request->Ciclo],
          ['Estado','=', 4],
          ['Critica','=',$request->Critica],
          ['Coordenada','=', $request->Generado]
          ])
          ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
          
          ->get();   

          }    
            return  DataTables()->of($datas)
            ->addColumn('checkbox','<input type="checkbox" name="case[]"  value="{{$id}}" class="case" title="Selecciona Orden"
            />')
            ->rawColumns(['checkbox'])
            ->make(true);
        }
      
        
        
        return view('admin.ordenes.critica');
  }

// Funcion filtrar criticaadd

  public function criticaadd(Request $request)
  {   

  $fechaAi=now()->toDateString()." 00:00:01";
  $fechaAf=now()->toDateString()." 23:59:59";
          
      if(request()->ajax())
      {    
      
      if(!empty($request->Periodo) && !empty($request->Ciclo) && !empty($request->Ruta)){

          
          $datas=Ordenesmtl::orderBy('id')
          ->where([
          ['Periodo','=',$request->Periodo],
          ['Ciclo','=',$request->Ciclo],
          ['Ciclo','=',$request->Ruta],
          ['Estado','=', 4],
          ['Coordenada','=', 'generar']
          ])
         
          ->get();
      
                  
          
      }else{      
        
        $datas=Ordenesmtl::orderBy('id')
        ->where([
        ['Periodo','=',$request->Periodo],
        ['Ciclo','=',$request->Ciclo],
        ['Estado','=', 4]
        ])
        ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
        
        ->get();   

        }    
          return  DataTables()->of($datas)
          ->make(true);
      }
    
      
      
      return view('admin.ordenes.criticaadd');
  }
// Funcion Exportar Pdf

  public function generarcritica(Request $request)
    {   
    
            
       if(!empty($request->Periodo) && !empty($request->Ciclo) &&  !empty($request->ruta)){
        
        
            $datas=Ordenesmtl::orderBy('id')
            ->where([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['idDivision','=',$request->ruta],
            ['Estado','=', 4],
            ['Coordenada','=', 'generar']
            ])
           
            ->get();
           

         

        }
        
       
       

        $pdf = PDF::loadView('admin.ordenes.pdfcritica', compact('datas'));
           
        return $pdf->stream(); 
        
      } 
       
          
    
     
        
     
        
    


  
    
// Funcion Adicionar Orden a Critica

  public function adicionarcritica(Request $request)
  {   

      
  if (request()->ajax()) {
        
    $id = $request->input('id');
       
       
    foreach ($id as $fila ) {

        DB::table('ordenescu')
        ->where([
                 ['id', '=', $fila],
                 ['Estado', '=', 4],
                ])
        ->update(['Coordenada' => 'generar']);           
           
     }
        
    
     return response()->json(['mensaje' => 'ok']);
    }

   
  }
  
 
 

// Funcion Eliminar Orden a Critica

  public function eliminarcritica(Request $request)
  {   

    
  if (request()->ajax()) {
      
  $id = $request->input('id');
     
     
  foreach ($id as $fila ) {

      DB::table('ordenescu')
      ->where([
               ['id', '=', $fila],
               ['Estado', '=', 4],
              ])
      ->update(['Coordenada' => '']);           
         
   }
      
  
   return response()->json(['mensaje' => 'ok']);
  }

 
  }
  // Funcion de asignar ordenes a usuarios
       public function actualizar(Request $request)
    {   

      if (request()->ajax()) {
        
        $id = $request->input('id');
        $usuario = $request->input('Usuario');
        $nombreu = Usuario::where('usuario',$usuario)->first();
        
        
        foreach ($id as $ids ){    
            $Estado2 = DB::table('ordenescu')
            ->where([['id', $ids],
             ['Estado', '=', 1 ]])
             ->count(); 
        
        }    

        if($usuario != null & $Estado2>0){
        
       
        foreach ($id as $fila ) {

            DB::table('ordenescu')
            ->where([
                     ['id', '=', $fila],
                     ['Estado', '=', 1],
                    ])
            ->update(['Usuario' => $usuario,
                     'Estado' => 2,
                     'Estado_des' => 'PENDIENTE',
                     'nombreu' => $nombreu->nombre
                     ]);           
               
         }
            
        
         return response()->json(['mensaje' => 'ok']);
        } else{

        return response()->json(['mensaje'=>'ng']);   
        } 
        
      } else {
        abort(404);
           }
    }

 // Funcion de desasignar ordenes a usuarios
    public function desasignar(Request $request)
    {   

      if (request()->ajax()) {
        
          $id = $request->input('id');
                  
          foreach ($id as $ids ){    
              $Estado2 = DB::table('ordenescu')
              ->where([['id', $ids],
              ['Estado', '=', 2 ]])
              ->count(); 
          
          }    

          if($Estado2>0){

          foreach ($id as $fila ) {

              DB::table('ordenescu')
              ->where([
                      ['id', '=', $fila],
                      ['Estado', '=', 2],
                      ])
              ->update(['usuario' => '',
                      'Estado' => 1,
                      'Estado_des' => 'CARGADO',
                      'nombreu' => ''
                      ]);           
                
          }
          return response()->json(['mensaje' => 'ok']);
          } else{

          return response()->json(['mensaje'=>'ng']);   
          } 
          
      } else {
          abort(404);
            }
    }   
    
//Api de sincronizacion de ordenes a ejecutar
 public function medidorall(Request $request)
    {   
     
       $Usuario=$request->Usuario;
       $Estado=$request->Estado;
        
        $medidorapi = DB::table('ordenescu')
        ->where([
            ['Estado','=',$Estado],
            ['Usuario','=',$Usuario], 
            ])
      ->select('id', 'Ciclo', 'Periodo', 'Ref_Medidor', 'Direccion', 'Nombre', 'Apell', 'LA', 'Promedio', 'Año', 'id_Ruta', 'Ruta', 'consecutivoRuta', 'Usuario', 'Estado', 'Tope', 'Suscriptor')
      ->get();

        return response()->json($medidorapi);
        
    }    

  
 
// Controlador de seguimiento de orden
     public function seguimiento(Request $request)
      {   

        $fechaAi=now()->toDateString()." 00:00:01";
        $fechaAf=now()->toDateString()." 23:59:59";
        
    

        if(request()->ajax())
        {    
        
        $usuario = $request->usuario;
        $cri = $request->critica;
        $med = $request->medidor;
           
        if(!empty($request->periodo) && !empty($request->zona) && !empty($usuario) && empty($request->estado)){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona],
            ['Usuario','=',$usuario]
            ])
           ->get();
                    
        }else  if(!empty($request->periodo) && !empty($request->zona) && !empty($usuario) && !empty($request->estado) && !empty($cri)){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona],
            ['Usuario','=',$usuario],
            ['Estado_des','=',$request->estado],
            ['Critica','=',$cri]
            ])
           ->get(); 
        
        
        
        
        
        }else  if(!empty($request->periodo) && !empty($request->zona) && !empty($usuario) && !empty($request->estado)){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona],
            ['Usuario','=',$usuario],
            ['Estado_des','=',$request->estado]
            ])
           ->get(); 
        
        
        
        
        
        }else  if(!empty($request->periodo) && !empty($request->zona) && !empty($request->estado)){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona],
            ['Estado_des','=',$request->estado]
            ])
           ->get(); 
        
        
        
        
        
        }else  if(!empty($request->periodo) && !empty($request->zona) && !empty($cri)){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona],
            ['Critica','=',$cri]
            ])
           ->get(); 
        
        
        
        
        
        }else if(!empty($request->periodo) && !empty($request->zona)){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona]
            ])
            ->get();
        
       
            }elseif(!empty($med) || !empty($request->estado) || (!empty($request->medidor) && !empty($request->fechaini) && !empty($request->fechafin))){
            
            $datas=Ordenesmtl::orderBy('id')
            ->where([
            ['Ref_Medidor','=',$med],
            ])
            ->orwhere([
                ['Ref_Medidor','=',$med],
                ['Estado_des','=',$request->estado],
              ])
            ->orwhere([
                ['Ref_Medidor','=',$med],
              ])
            ->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"])
            ->get();
        }elseif(!empty($request->suscriptor) || (!empty($request->suscriptor) && !empty($request->estado)) || (!empty($request->suscriptor) && !empty($request->fechaini) && !empty($request->fechafin))){      
            $datas=Ordenesmtl::orderBy('id')
            ->where([
              ['Suscriptor','=',$request->suscriptor],
              ])
            ->orwhere([
                ['Suscriptor','=',$request->suscriptor],
                ['Estado_des','=',$request->estado],
              ])
            ->orwhere([
                ['suscriptor','=',$request->suscriptor],
              ])
            ->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"])
            ->get();   

        }elseif(!empty($request->fechaini) && !empty($request->fechafin)){      
            $datas=Ordenesmtl::orderBy('id')
            ->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"])
            ->get();   
            }else{

            $datas=Ordenesmtl::orderBy('id')
            ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
            ->get();   
        }  
            
            return  DataTables()->of($datas)
            ->addColumn('foto','<a target="_blank" href="{{url("seguimiento/$id")}}" class="tooltipsC" title="Foto"><i class="fa fa-camera"></i>
            </a>')
            ->addColumn('foto_Url','{{url("seguimiento/$id")}}')
            ->addColumn('detalle','<a target="_blank" href="{{url("seguimientodetalle/$id")}}" class="btn btn-xs btn-warning tooltipsC" title="detalle"
            >Orden detalle</a>')
            ->addColumn('detalle_Url','{{url("seguimientodetalle/$id")}}')
            ->rawColumns(['detalle','foto','foto_Url','detalle_Url' ])
            ->make(true);
        }
      
        $usuarios=Usuario::orderBy('id')->where('tipodeusuario','movil')->pluck('nombre', 'usuario', 'id');
        
        $criticas=Ordenesmtl::groupBy('Critica')->orderBy('Critica')->pluck('Critica');
        
        return view('admin.ordenes.seguimiento', compact('usuarios','criticas'));   
     }

  //Actualizacion de marca de agua suscriptor solo link de fotos
    public function fotos($id)
    {
         $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['id','=',$id]
             ])
            ->get();
        
         foreach ($datas as $data ){    
            $suscriptor = $data->Suscriptor;
            $foto1 = $data->foto1;
                       
            }    
       
       if($foto1 != null){
       $img1 = Image::make(public_path($foto1));
        $textimage = 'Suscriptor: '.$suscriptor;
        $img1->text($textimage, 10, 450,
         function($font){ 
           $font->size(24);
           $font->file(public_path('font/OpenSans-Regular.ttf'));
           $font->color('#f1f505'); 
           $font->align('left'); 
           $font->valign('bottom'); 
           $font->angle(0); }); 
        $img1->save(public_path('/tmp/'.$foto1));
        $img1->destroy();
       }
      
     
    
       
         return view('admin.ordenes.fotos', compact('datas'));    
            
      }
    
//Actualizacion de marca de agua suscriptor solo detalle de fotos
    public function detalle($id)
    {
         $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['id','=',$id]
             ])
            ->get();
            
      foreach ($datas as $data ){    
            $suscriptor = $data->Suscriptor;
            $foto1 = $data->foto1;
           
                       }    
       
       if($foto1 != null){
       $img1 = Image::make(public_path($foto1));
        $textimage = 'Suscriptor: '.$suscriptor;
        $img1->text($textimage, 10, 450,
         function($font){ 
           $font->size(24);
           $font->file(public_path('font/OpenSans-Regular.ttf'));
           $font->color('#f1f505'); 
           $font->align('left'); 
           $font->valign('bottom'); 
           $font->angle(0); }); 
        $img1->save(public_path('/tmp/'.$foto1));
        $img1->destroy();
       }
      
     
      
     
            
            
        return view('admin.ordenes.detalle', compact('datas'));    
            
    }
    
//Posicionamiento

  public function posicionamiento(Request $request)
    {       
    
      if($request->ajax()){
    
        if (!empty($request->Periodo) && !empty($request->Ciclo) && !empty($request->ruta)) { 
       
               
            
              $markers1=Ordenesmtl::orderBy('id')
                   ->where
                   ([
                   ['Periodo','=',$request->Periodo],
                   ['Ciclo','=',$request->Ciclo],
                   ['idDivision','=',$request->ruta],
                   ['Estado','=',4]
                   ])
                  ->get();
          
         
            
           }else if (!empty($request->Periodo) && !empty($request->Ciclo)){
               
                 $markers1=Ordenesmtl::orderBy('id')
                   ->where
                   ([
                   ['Periodo','=',$request->Periodo],
                   ['Ciclo','=',$request->Ciclo],
                   ['Estado','=',4]
                   ])
                  ->get();
               
           }
           
           return response()->json($markers1);
           }
             
          return view('admin.ordenes.posicionamiento');
           
                    
    }  
//Exportar excel 
     public function exportarExcel(Request $request)
    {   

        $fechaAi=now()->toDateString()." 00:00:01";
        $fechaAf=now()->toDateString()." 23:59:59";
        
    
        if(!empty($request->Periodo) && !empty($request->Ciclo) && (!empty($request->usuario) || !empty($request->Estado))){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['usuario','=',$request->usuario]
            ])
            ->orwhere
            ([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['Estado','=',$request->Estado]
            ])
            
            ->get();
                    
        }else if(!empty($request->Periodo) && !empty($request->Ciclo)){
            $datas=Ordenesmtl::orderBy('id')
            ->where
            ([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo]
            ])
            ->get();
        
        Excel::create('Ordenes_mtl', function ($excel) use ($datas) {
        
        $excel->sheet('Hoja Uno', function ($sheet) use ($datas) {    
            
                $sheet->with($datas, null, 'A1', false, false);
                });
            
        })->download('xlsx');
        
        
            }elseif(!empty($request->medidor) || (!empty($request->medidor) && !empty($request->Estado)) || (!empty($request->medidor) && !empty($request->fechaini) && !empty($request->fechafin))){
            
            $datas=Ordenesmtl::orderBy('id')
            ->where([
            ['medidor','=',$request->medidor],
            ])
            ->orwhere([
                ['medidor','=',$request->medidor],
                ['Estado','=',$request->Estado],
              ])
            ->orwhere([
                ['medidor','=',$request->medidor],
              ])
            ->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"])
            ->get();
        }elseif(!empty($request->suscriptor) || (!empty($request->suscriptor) && !empty($request->Estado)) || (!empty($request->suscriptor) && !empty($request->fechaini) && !empty($request->fechafin))){      
            $datas=Ordenesmtl::orderBy('id')
            ->where([
              ['suscriptor','=',$request->suscriptor],
              ])
            ->orwhere([
                ['suscriptor','=',$request->suscriptor],
                ['Estado','=',$request->Estado],
              ])
            ->orwhere([
                ['suscriptor','=',$request->suscriptor],
              ])
            ->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"])
            ->get();   

        }elseif(!empty($request->fechaini) && !empty($request->fechafin)){      
            $datas=Ordenesmtl::orderBy('id')
            ->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"])
            ->get();   
            }else{

            $datas=Ordenesmtl::orderBy('id')
            ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
            ->get();   
        }  
           
           
      
    }

}


