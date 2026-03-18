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
use App\Models\Admin\Photos;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Exports\OrdenesExport;
use Carbon\Carbon;



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
      
        $usuarios=Usuario::orderBy('id')->where([['tipodeusuario','movil'],['estado','activo']])->pluck('usuario', 'id');
           
        
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
        
        if($request->Periodo != '' && $request->Ciclo != '' && $request->Ruta != ''){

            
        $datas=Ordenesmtl::orderBy('Cons_Act','DESC');
          
          
       if($request->Periodo != '' && $request->Ciclo != '' && $request->Ruta != ''){
       $datas->where([
          ['Periodo','=',$request->Periodo],
          ['Ciclo','=',$request->Ciclo],
          ['idDivision','=',$request->Ruta],
          ['Estado','=', 4],
          ['Critica','!=', '54-NORMAL']
          ]);
        }
          
        
      if(!empty($all)){
        $datas->where('Critica','=',$all);
        }
        
     if($request->Generado == 'generar'){
        $datas->where('Coordenada','=', 'generar');
        }
        
     if($request->Generado == '1'){
       $datas->whereNull('Coordenada');
        }
     
        $datas->get();
     
            return  DataTables()->of($datas)
            ->addColumn('checkbox','<input type="checkbox" name="case[]"  value="{{$id}}" class="case" title="Selecciona Orden"
            />')
            ->addColumn('foto','<a target="_blank" href="{{url("seguimiento/$id")}}" class="tooltipsC" title="Foto"><i class="fa fa-camera"></i>
            </a>')
            ->addColumn('foto_Url','{{url("seguimiento/$id")}}')
            ->addColumn('detalle','<a target="_blank" href="{{url("seguimientodetalle/$id")}}" class="btn btn-xs btn-warning tooltipsC" title="detalle">Orden detalle</a>')
            ->addColumn('detalle_Url','{{url("seguimientodetalle/$id")}}')
            ->rawColumns(['checkbox','detalle','foto','foto_Url','detalle_Url' ])
            ->make(true);
        
          
      }else{      
          
          $datas1=Ordenesmtl::orderBy('id')
          ->where([
          ['Periodo','=',$request->Periodo],
          ['Ciclo','=',$request->Ciclo],
          ['Estado','=', 4],
          ['Critica','=',$request->Critica],
          ['Coordenada','=', $request->Generado]
          ])
          ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
          ->get();
          
            return  DataTables()->of($datas1)
            ->addColumn('checkbox','<input type="checkbox" name="case[]"  value="{{$id}}" class="case" title="Selecciona Orden"
            />')
            ->addColumn('foto','<a target="_blank" href="{{url("seguimiento/$id")}}" class="tooltipsC" title="Foto"><i class="fa fa-camera"></i>
            </a>')
            
            ->addColumn('foto_Url','{{url("seguimiento/$id")}}')
            ->addColumn('detalle','<a target="_blank" href="{{url("seguimientodetalle/$id")}}" class="btn btn-xs btn-warning tooltipsC" title="detalle"
            >Orden detalle</a>')
            ->addColumn('detalle_Url','{{url("seguimientodetalle/$id")}}')
            ->rawColumns(['checkbox','detalle','foto','foto_Url','detalle_Url' ])
            ->make(true); 

          }    
          
        
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
          ['id_Ruta','=',$request->Ruta],
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
           
            $datasn=Ordenesmtl::orderBy('id')
            ->where([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['idDivision','=',$request->ruta],
            ['Estado','=', 4],
            ['Coordenada','=', 'generar']
            ])
           
            ->first();
            

        }
        
       
       

        $pdf = PDF::loadView('admin.ordenes.pdfcritica', compact('datas'));
           
        return $pdf->download($datasn->Periodo.'--'.$datasn->Ciclo.'--Ruta--'.$datasn->idDivision.'--'.$datasn->Usuario."--".'.pdf'); 
        
      } 
       
       
    
    public function generarfactura(Request $request)
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
           
            $datasn=Ordenesmtl::orderBy('id')
            ->where([
            ['Periodo','=',$request->Periodo],
            ['Ciclo','=',$request->Ciclo],
            ['idDivision','=',$request->ruta],
            ['Estado','=', 4],
            ['Coordenada','=', 'generar']
            ])
           
            ->first();
            

        }
        
       
       

        $pdf = PDF::loadView('admin.ordenes.facturas.factura1', compact('datas'));
           
        return $pdf->download($datasn->Periodo.'--'.$datasn->Ciclo.'--Ruta--'.$datasn->idDivision.'--'.$datasn->Usuario."--".'.pdf'); 
        
      } 
       
       
       
          
     public function factura(Request $request)
  {   

       return view('admin.ordenes.facturas.index');
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
      ->update(['Coordenada' => NULL]);           
         
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
        // El usuario ya está autenticado por el middleware
        $usuario = $request->user();
        $estado = '2'; // Estado por defecto o desde un parámetro
        
        $medidores = DB::table('ordenescu')
            ->where('Usuario', $usuario->usuario)
            ->where('Estado', $estado)
            ->select(
                'id', 'Ciclo', 'Periodo', 'Ref_Medidor', 'Direccion', 
                'Nombre', 'Apell', 'LA', 'Promedio', 'Año', 'id_Ruta', 
                'Ruta', 'consecutivoRuta', 'Usuario', 'Estado', 'Tope', 'Suscriptor'
            )
            ->orderBy('id_Ruta')->orderBy('consecutivoRuta')
            ->get();

        if ($medidores->count() > 0) {
            return response()->json($medidores, 200);
        } else {
            return response()->json(['error' => 'Sin medidores asignados'], 200);
        }
    }  

    
    // Controlador de actualizar lectura
public function actualizarLectura(Request $request)
{
    $usuario = $request->user()->usuario;

    $orden = Ordenesmtl::find($request->orden_id);
    
    if($orden) {
        $orden->Lect_Actual = $request->lectura_nueva;
        $orden->new_medidor = 'Lectura actualizada en analisis usuario: ' . $usuario;
        $orden->save();
        // El trigger se ejecutará automáticamente
        
        return response()->json(['success' => true]);
    }
    
    return response()->json(['success' => false], 400);
}

// Nueva función para obtener solo la URL de la foto (para el modal)
public function getFotoUrl($id)
{
    try {
        $orden = Ordenesmtl::where('id', $id)->first();
        
        if (!$orden) {
            return response()->json([
                'foto_url' => null,
                'tiene_foto' => false,
                'success' => false,
                'error' => 'Orden no encontrada'
            ], 404);
        }
        
        $foto1 = $orden->foto1;
        $fotoUrl = null;
        $tieneFoto = false;
        
        if ($foto1 != null && $foto1 != '') {
            // Verificar si ya existe la foto procesada en /tmp/
            $tmpFotoPath = public_path('/tmp/' . $foto1);
            
            // Si no existe en /tmp/, procesarla
            if (!file_exists($tmpFotoPath)) {
                // Procesar la imagen con marca de agua
                $suscriptor = $orden->Suscriptor;
                $medidor = $orden->Ref_Medidor;
                $lectura = $orden->Lect_Actual;
                
                // Verificar que la foto original exista
                if (file_exists(public_path($foto1))) {
                    $img1 = Image::make(public_path($foto1));
                    
                    // Agregar texto del suscriptor
                    $textimage = 'Suscriptor: ' . $suscriptor;
                    $img1->text($textimage, 10, 450, function($font) { 
                        $font->size(24);
                        $font->file(public_path('font/OpenSans-Regular.ttf'));
                        $font->color('#f1f505'); 
                        $font->align('left'); 
                        $font->valign('bottom'); 
                        $font->angle(0); 
                    });
                    
                    // Agregar texto del medidor
                    $textimage1 = 'Med: ' . $medidor;  
                    $img1->text($textimage1, 10, 60, function($font) { 
                        $font->size(24);
                        $font->file(public_path('font/OpenSans-Regular.ttf'));
                        $font->color('#f1f505'); 
                        $font->align('left'); 
                        $font->valign('bottom'); 
                        $font->angle(0); 
                    });
                    
                    // Agregar texto de la lectura
                    $textimage2 = 'L: ' . $lectura;  
                    $img1->text($textimage2, 10, 95, function($font) { 
                        $font->size(32);
                        $font->file(public_path('font/OpenSans-Regular.ttf'));
                        $font->color('#f1f505'); 
                        $font->align('left'); 
                        $font->valign('bottom'); 
                        $font->angle(0); 
                    });
                    
                    // Guardar en tmp
                    $img1->save($tmpFotoPath);
                    $img1->destroy();
                }
            }
            
            // Construir la URL de la foto procesada
            if (file_exists($tmpFotoPath)) {
                $fotoUrl = url('/tmp/' . $foto1);
                $tieneFoto = true;
            }
        }
        
        return response()->json([
            'foto_url' => $fotoUrl,
            'tiene_foto' => $tieneFoto,
            'success' => true
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'foto_url' => null,
            'tiene_foto' => false,
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
 
// Controlador de seguimiento de orden
public function seguimiento()
      { 
           $usuarios=Usuario::orderBy('id')->where('tipodeusuario','movil')->pluck('nombre', 'usuario', 'id');
        
        $criticas=Ordenesmtl::groupBy('Critica')->orderBy('Critica')->pluck('Critica');
        
        return view('admin.ordenes.seguimiento', compact('usuarios','criticas')); 
     }
          
          
     public function seguimiento1(Request $request)
      {   

        $fechaAi=now()->toDateString()." 00:00:01";
        $fechaAf=now()->toDateString()." 23:59:59";
        $rol_id = $request->session()->get('rol_id');
        $user_id = $request->session()->get('usuario');
    

        if(request()->ajax())
        {    
        
        $usuario = $request->usuario;
        $cri = $request->critica;
        $med = $request->medidor;
         
      
      if($rol_id == 1 || $user_id == 'dfsalas'){
          $usuario = $request->usuario;
      
      if(empty($usuario) && empty($request->periodo) && empty($request->zona) && empty($request->estado) && empty($cri) && empty($med) && empty($request->suscriptor) && empty($request->fechaini) && empty($request->fechafin))
      
       {  
           $data1=Ordenesmtl::orderBy('id')
            ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf]);
            
            $data1->get();
         
         
           
            return  DataTables()->of($data1)
            ->addColumn('foto','<a target="_blank" href="{{url("seguimiento/$id")}}" class="tooltipsC" title="Foto"><i class="fa fa-camera"></i>
            </a>')
            ->addColumn('foto_Url','{{url("seguimiento/$id")}}')
            ->addColumn('detalle','<a target="_blank" href="{{url("seguimientodetalle/$id")}}" class="btn btn-outline-warning btn-block btn-flat tooltipsC" title="detalle"
            ><i class="fa fa-book"></i> Orden detalle</a>')
            ->addColumn('detalle_Url','{{url("seguimientodetalle/$id")}}')
            ->addColumn('edit_lectura', function ($orden) {
                        $button = '<button type="button" class="edit_lectura btn btn-sm btn-info tooltipsC" 
                            data-id="' . $orden->id . '" 
                            data-lectura="' . $orden->Lect_Actual . '" 
                            data-suscriptor="' . $orden->Suscriptor . '" 
                            title="Editar Lectura">
                            <i class="fa fa-edit"></i>
                        </button>';
                        return $button;
                    })
            ->addColumn('action', function ($orden) {
                $button = '<button type="button" name="show_detail" id="' . $orden->id . '
                    " class="update_orden btn btn-app bg-warning tooltipsC" title="Devolver Orden"  >
                    <span class="badge bg-teal">' . $orden->id . '</span><i class="fas fa-edit"></i> </button>';
                    return $button;
                })
                ->rawColumns(['detalle','foto','foto_Url','detalle_Url', 'edit_lectura', 'action' ])
            ->make(true);
      //  btn btn-xs btn-warning 
        
        
       }else{
           
        
       $datas = Ordenesmtl::orderBy('id');
            
            
            //Se filtra por usuario  
        if($request->usuario != ''){
            
            $datas->where
            ([
             ['Usuario','=',$usuario]
            ]);
              
        }
        
       
        //Se filtra por periodo 
         if($request->periodo != '' && $request->zona != ''){
            $datas->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona]
            ]);
         } 
        
       
        
        //Se filtra por estado 
        
        if($request->estado != ''){
            $datas->where
            ([
            ['Estado_des','=',$request->estado]
            ]); 
        
        }
        
        //Se filtra por critica 
        
        if($cri != ''){
            $datas->where
            ([
            ['Critica','=',$cri]
            ]); 
        
        }
        
       
       //Se filtra por medidor 
        if($med != ''){
            
            $datas->where([
            ['Ref_Medidor','=',$med],
            ]);
        }
        
        //Se filtra por suscriptor
        if($request->suscriptor != ''){      
            $datas->where([
              ['Suscriptor','=',$request->suscriptor],
              ]);   

        }
        
         //Se filtra por fecha de ejecución
        if($request->fechaini != '' && $request->fechafin != ''){      
            $datas->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"]);
            }   
        
         $datas->get();
         
         
           
            return  DataTables()->of($datas)
            ->addColumn('foto','<a target="_blank" href="{{url("seguimiento/$id")}}" class="tooltipsC" title="Foto"><i class="fa fa-camera"></i>
            </a>')
            ->addColumn('foto_Url','{{url("seguimiento/$id")}}')
            ->addColumn('detalle','<a target="_blank" href="{{url("seguimientodetalle/$id")}}" class="btn btn-outline-warning btn-block btn-flat tooltipsC" title="detalle"
            ><i class="fa fa-book"></i> Orden detalle</a>')
            ->addColumn('detalle_Url','{{url("seguimientodetalle/$id")}}')
            ->addColumn('edit_lectura', function ($orden) {
                        $button = '<button type="button" class="edit_lectura btn btn-sm btn-info tooltipsC" 
                            data-id="' . $orden->id . '" 
                            data-lectura="' . $orden->Lect_Actual . '" 
                            data-suscriptor="' . $orden->Suscriptor . '" 
                            title="Editar Lectura">
                            <i class="fa fa-edit"></i>
                        </button>';
                        return $button;
                    })
            ->addColumn('action', function ($orden) {
                $button = '<button type="button" name="show_detail" id="' . $orden->id . '
                    " class="update_orden btn btn-app bg-warning tooltipsC" title="Devolver Orden"  >
                    <span class="badge bg-teal">' . $orden->id . '</span><i class="fas fa-edit"></i> </button>';
                    return $button;
                })
                ->rawColumns(['detalle','foto','foto_Url','detalle_Url','edit_lectura', 'action'])
            
            ->make(true);
            
       }
      }else{
          
           if(empty($usuario) && empty($request->periodo) && empty($request->zona) && empty($request->estado) && empty($cri) && empty($med) && empty($request->suscriptor) && empty($request->fechaini) && empty($request->fechafin))
      
       {  
           $data1=Ordenesmtl::orderBy('id')
            ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf]);
            
            $data1->get();
         
         
           
            return  DataTables()->of($data1)
            ->addColumn('foto','<a target="_blank" href="{{url("seguimiento/$id")}}" class="tooltipsC" title="Foto"><i class="fa fa-camera"></i>
            </a>')
            ->addColumn('foto_Url','{{url("seguimiento/$id")}}')
            ->addColumn('detalle','<a target="_blank" href="{{url("seguimientodetalle/$id")}}" class="btn btn-outline-warning btn-block btn-flat tooltipsC" title="detalle"
            ><i class="fa fa-book"></i> Orden detalle</a>')
            ->addColumn('detalle_Url','{{url("seguimientodetalle/$id")}}')
            ->addColumn('edit_lectura', function ($orden) {
                        $button = '<button type="button" class=" btn btn-sm btn-info tooltipsC" 
                            data-id="" 
                            data-lectura="" 
                            data-suscriptor="" 
                            title="Editar Lectura">
                            
                        </button>';
                        return $button;
                    })
            ->addColumn('action', function ($orden) {
                $button = '<button type="button" name="show_detail" id="" class="btn tooltipsC readonly" title=""  ></button>';
                    return $button;
                })
                ->rawColumns(['detalle','foto','foto_Url','detalle_Url', 'action','edit_lectura' ])
            ->make(true);
      //  btn btn-xs btn-warning 
        
        
       }else{
           
        
       $datas = Ordenesmtl::orderBy('id');
            
            
            //Se filtra por usuario  
        if($request->usuario != ''){
            
            $datas->where
            ([
             ['Usuario','=',$usuario]
            ]);
              
        }
        
       
        //Se filtra por periodo 
         if($request->periodo != '' && $request->zona != ''){
            $datas->where
            ([
            ['Periodo','=',$request->periodo],
            ['Ciclo','=',$request->zona]
            ]);
         } 
        
       
        
        //Se filtra por estado 
        
        if($request->estado != ''){
            $datas->where
            ([
            ['Estado_des','=',$request->estado]
            ]); 
        
        }
        
        //Se filtra por critica 
        
        if($cri != ''){
            $datas->where
            ([
            ['Critica','=',$cri]
            ]); 
        
        }
        
       
       //Se filtra por medidor 
        if($med != ''){
            
            $datas->where([
            ['Ref_Medidor','=',$med],
            ]);
        }
        
        //Se filtra por suscriptor
        if($request->suscriptor != ''){      
            $datas->where([
              ['Suscriptor','=',$request->suscriptor],
              ]);   

        }
        
         //Se filtra por fecha de ejecución
        if($request->fechaini != '' && $request->fechafin != ''){      
            $datas->whereBetween('fecha_de_ejecucion', [$request->fechaini." 00:00:01",$request->fechafin." 23:59:59"]);
            }   
        
         $datas->get();
         
         
           
            return  DataTables()->of($datas)
            ->addColumn('foto','<a target="_blank" href="{{url("seguimiento/$id")}}" class="tooltipsC" title="Foto"><i class="fa fa-camera"></i>
            </a>')
            ->addColumn('foto_Url','{{url("seguimiento/$id")}}')
            ->addColumn('detalle','<a target="_blank" href="{{url("seguimientodetalle/$id")}}" class="btn btn-outline-warning btn-block btn-flat tooltipsC" title="detalle"
            ><i class="fa fa-book"></i> Orden detalle</a>')
            ->addColumn('detalle_Url','{{url("seguimientodetalle/$id")}}')
            ->addColumn('edit_lectura', function ($orden) {
                        $button = '<button type="button" class=" btn btn-sm btn-info tooltipsC" 
                            data-id="" 
                            data-lectura="" 
                            data-suscriptor="" 
                            title="Editar Lectura">
                            
                        </button>';
                        return $button;
                    })
            ->addColumn('action', function ($orden) {
                $button = '<button type="button" name="show_detail" id="" class="btn tooltipsC readonly" title=""  ></button>';
                    return $button;
                })
                ->rawColumns(['detalle','foto','foto_Url','detalle_Url', 'action','edit_lectura' ])
            
            ->make(true);
            
       }
          
          
      }
         
         
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
            $medidor = $data->Ref_Medidor;
            $lectura = $data->Lect_Actual;
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
           
        $textimage1 = 'Med: '.$medidor;  
        $img1->text($textimage1, 10, 60,
         function($font){ 
           $font->size(24);
           $font->file(public_path('font/OpenSans-Regular.ttf'));
           $font->color('#f1f505'); 
           $font->align('left'); 
           $font->valign('bottom'); 
           $font->angle(0); });
           
        $textimage2 = 'L: '.$lectura;  
        $img1->text($textimage2, 10, 95,
         function($font){ 
           $font->size(32);
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
         $datas=Ordenesmtl::orderBy('id')->where([['id','=',$id]])->get();
            
           
      foreach ($datas as $data ){    
            $suscriptor = $data->Suscriptor;
            $medidor = $data->Ref_Medidor;
            $lectura = $data->Lect_Actual;
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
           
        $textimage1 = 'Med: '.$medidor;  
        $img1->text($textimage1, 10, 60,
         function($font){ 
           $font->size(24);
           $font->file(public_path('font/OpenSans-Regular.ttf'));
           $font->color('#f1f505'); 
           $font->align('left'); 
           $font->valign('bottom'); 
           $font->angle(0); });
           
        $textimage2 = 'L: '.$lectura;  
        $img1->text($textimage2, 10, 95,
         function($font){ 
           $font->size(32);
           $font->file(public_path('font/OpenSans-Regular.ttf'));
           $font->color('#f1f505'); 
           $font->align('left'); 
           $font->valign('bottom'); 
           $font->angle(0); }); 
        $img1->save(public_path('/tmp/'.$foto1));
        $img1->destroy();
       }
       
       
       // Obtener la foto de la base de datos
    /*$foto = Photos::where('id_orden_ejecutada', $id)->firstOrFail();
    $fotoBlob = $foto->photo_data; // El blob de la imagen

    // Guardar el blob como archivo temporal
    $tempImagePath = storage_path('app/temp/foto_'.$id.'.jpg');
    File::put($tempImagePath, $fotoBlob);
      
      // Verificar si la imagen existe
    if (File::exists($tempImagePath)) {
        // Si la imagen existe, enviar la ruta a la vista
        return view('admin.ordenes.detalle', compact('datas','tempImagePath')); 
    } else {
        
        $tempImagePath = null;*/
        // Si la imagen no existe, enviar una variable indicando que la imagen no está disponible
        return view('admin.ordenes.detalle', compact('datas')); 
    
      
            
     //dd($tempImagePath);
         
            
       
            
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
     public function exportarCiclo(Request $request)
    {   
        
         $usuarios=Usuario::orderBy('id')->where('tipodeusuario','movil')->pluck('nombre', 'usuario', 'id');
        
        $criticas=Ordenesmtl::groupBy('Critica')->orderBy('Critica')->pluck('Critica');
        
        return view('admin.ordenes.exportar.exportarCiclo', compact('usuarios','criticas')); 
       
           
           
      
    }
    
    
    
//Exportar excel 
     public function exportarExcel(Request $request)
    {   

        // Definir la hora de inicio y fin para el día actual
    $fechaAi = now()->toDateString() . " 00:00:01";
    $fechaAf = now()->toDateString() . " 23:59:59";

    // Verificar si se proporcionan filtros
    if (empty($request->periodo) && empty($request->zona) && empty($request->estado) && empty($request->fechaini) && empty($request->fechafin)) {
        return response()->json(['respuesta' => 'Debes seleccionar', 'titulo' => 'Un filtro periodo y ciclo', 'icon' => 'warning']);
    } 

    // Construir la consulta en base a los filtros proporcionados
    $datas = Ordenesmtl::select('Suscriptor', 'Lect_Actual', 'Causa_des', 'Observacion_des', 'Fecha_de_ejecucion')
        ->whereNotIn('Estado_des', ['ANULADO', 'ANULADA'])
        ->orderBy('id');

    // Aplicar filtros basados en el request
    if (!empty($request->periodo) && !empty($request->zona) && !empty($request->estado)) {
        $datas->where([
            ['Periodo', '=', $request->periodo],
            ['Ciclo', '=', $request->zona],
            ['Estado_des', '=', $request->estado]
        ]);
    }

    if (!empty($request->fechaini) && !empty($request->fechafin)) {
        $datas->whereBetween('fecha_de_ejecucion', [$request->fechaini . " 00:00:01", $request->fechafin . " 23:59:59"]);
    }

    // Ejecutar la consulta y obtener los resultados
    $data = $datas->get();

    // Verificar si hay resultados
    if ($data->isEmpty()) {
        return response()->json(['respuesta' => 'No se encontraron datos', 'titulo' => 'No hay datos', 'icon' => 'info']);
    }

    // Exportar los datos a un archivo CSV
    $export = new OrdenesExport($data);
    $fileName = now()->format('Y-m-d_H-i-s') . "-Ordenes_ejecutadas.csv";
    $filePath = $export->store("exportv/".$fileName, 'public', \Maatwebsite\Excel\Excel::CSV, ['Content-Type' => 'text/csv']);
    
    $destinationPath = public_path('exportn/');
    
    $contenidoArchivo = storage_path('app/public/exportv/'.$fileName);

    // Copia el archivo a la nueva ubicación
    File::move($contenidoArchivo, $destinationPath . $fileName);
    
  
    // Obtener todos los archivos CSV en el directorio de exportación
    $archivos =File::files($destinationPath);
    
   // Filtrar solo los archivos CSV
    $archivosCSV = array_filter($archivos, function($archivo) {
            return pathinfo($archivo, PATHINFO_EXTENSION) === 'csv';
    });

     // Generar URLs públicas para los archivos CSV
    $archivosCSVs = array_map(function ($archivo) {
    return asset('exportn/' . basename($archivo));
    }, $archivosCSV);

    // Contar el número de filas en los datos
    $datac = $datas->count();
    
    

    // Retornar la respuesta con el enlace de descarga
    return response()->json([
        'respuesta' => 'Generando Excel -> Registros - '.$datac, 
        'titulo' => 'Filas - '.$datac, 
        'icon' => 'success', 
        'ruta' => $archivosCSVs
    ]);
    
    
      
    }
    
    
    
    
        public function updateEstado(Request $request)
        {       
        
          if($request->ajax()){
        
        
        
            if (!empty($request->orden) || $request->orden != null || $request->orden != '' ) { 
                
                 
           
                  new Ordenesmtl;
                  
                  $noupdate = Ordenesmtl::where('id',$request->orden)->where('sync',2)->count();
                 
                    if($noupdate>0){
                        
                   return response()->json(['respuesta' => 'Orden sync en ACUSYSCOM', 'titulo' => 'Orden # '.$request->orden, 'icon' => 'error']);     
                    }else{
                  
                  $Orden = Ordenesmtl::findOrFail($request->orden);
                  $Orden->Estado = 2;
                  $Orden->Estado_des = "PENDIENTE";
                  $Orden->save();
              
              
                return response()->json(['respuesta' => 'Orden actualizada', 'titulo' => 'Orden # '.$request->orden, 'icon' => 'success']);
                    }
                
               }
               
              
              
                 
              
               
                        
        }  
    
        }
        
        
        //Api de sincronizacion de ordenes a ejecutar
 public function medidorejecutadosync(Request $request)
    { 
    $Estado = 4;
    $year = $request->year;
    $month = $request->month;

    // Buscar órdenes no sincronizadas
    $ordenes = DB::table('ordenescu')
        ->where('Estado', $Estado)
        ->where('Año', $year)
        ->where('Mes', $month)
        ->where('sync', 1)
        ->select(
            'id_lectura', 'Usuario', 'Lect_Actual', 'Cons_Act',
            'Causa_id', 'Observacion_id', 'new_medidor', 'Critica',
            'Latitud', 'Longitud', 'Estado_des', 'Estado', 'fecha_de_ejecucion'
        )
        ->get();

    if ($ordenes->isNotEmpty()) {
        // Marcar como sincronizados y registrar fecha y hora de sincronización
        DB::table('ordenescu')
            ->where('Estado', $Estado)
            ->where('Año', $year)
            ->where('Mes', $month)
            ->where('sync', 1)
            ->update([
                'sync' => 2,
                'sync_at' => Carbon::now()
            ]);

        return response()->json([
                                'mensaje' => 'Lecturas sincronizadas correctamente',
                                'datos' => $ordenes
                                ], 200);
    } else {
        return response()->json(['error' => 'sin medidores ejecutados'], 200);
    }
    }  

}

