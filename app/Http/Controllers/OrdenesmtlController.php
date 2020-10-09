<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Ordenesmtl;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\DB;

class OrdenesmtlController extends Controller
{
    public function index(Request $request)
    {   
        $Periodo=$request->periodo;
        $Zona=$request->zona;
        $Estado=$request->estado;
        $orden=$request->orden;
        $ordenf=$request->ordenf;

        dd($Periodo, $Zona, $Estado);

        if(($Periodo != null & $Zona != null & $Estado != null) || ($orden != null & $ordenf != null  )){

            $datas=DB::table('ordenesmtl')
            ->where([
            ['periodo','=',$Periodo],
            ['zona','=',$Zona],
            ['estado','=',$Estado],
            ])
            ->orwhereBetween('ordenesmtl_id', [$orden, $ordenf])    
            ->select('id','ordenesmtl_id', 'estado', 'usuario', 'poliza','direccion','recorrido',
                'periodo','zona')
            ->get();
            
            
            
            $usuarios=Usuario::orderBy('id')->where('tipodeusuario','movil')->pluck('usuario', 'id');
            
            
            return view('admin.ordenes.index', compact("datas","usuarios"));
    
       }else{


        return view('admin.ordenes.index');   

        } 
    }
   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actualizar(Request $request)
    {   

        $asignar = $request->asignar;
        $desasignar  = $request->desasignar;
        
        
        if ($asignar == $asignar & $desasignar == null ) {
        
         
        $id=$request->id;
        $usuario=$request->usuario;

            
       
        foreach ($id as $ids ){    
            $estado2 = DB::table('ordenesmtl')
            ->where([['id', $ids],
             ['estado_id', '=', 1 ]])
             ->count(); 
        
        }    

        if($usuario != null & $estado2>0){

        foreach ($id as $fila ) {

            DB::table('ordenesmtl')
            ->where([
                     ['id', '=', $fila],
                     ['estado_id', '=', 1],
                    ])
            ->update(['usuario' => $usuario,
                     'estado_id' => 2,
                     'estado' => 'PENDIENTE'  
                     ]);           
               
         }
       
         return redirect('admin/asignacion')->with('mensaje', 'Ordenes asignadas correctamente');
        }else{
    
       return redirect('admin/asignacion')->with('mensaje', 'Debe seleccionar un usuario y una orden cargada');
             
    }  
    
       
        return redirect('admin/asignacion')->with('mensaje', 'Debe seleccionar una orden cargada');

    } else if ($asignar == null & $desasignar == $desasignar) {
        
        $id=$request->id;
        $usuario=$request->usuario;
    
    foreach ($id as $ids ){  
       $estado1 = DB::table('ordenesmtl')
           ->where([['id', $ids],
            ['estado_id', '=', 2]])
            ->count(); 
        } 
            if($estado1>0){

            foreach ($id as $fila ) {
           
             DB::table('ordenesmtl')
            ->where([['id', $fila],
                     ['estado_id', '=', 2]])
            ->update(['usuario' => '',
                     'estado_id' => 1,
                     'estado' => 'CARGADO'  
                     ]);           
               
        }
        return redirect('admin/asignacion')->with('mensaje', 'Ordenes desasignadas correctamente');

        } 
        return redirect('admin/asignacion')->with('mensaje', 'Debe seleccionar una orden pendiente');

    }else{return redirect('admin/asignacion')->with('mensaje', 'Debe seleccionar una orden');}

 }

//  public function index1(Request $request)
//  {   


//  $fechaAi=now()->toDateString()." 00:00:01";
//  $fechaAf=now()->toDateString()." 23:59:59";

//  if(request()->ajax())
//  {

//      if (!empty($request->periodo1) & !empty($request->zona1)) {
        
//          $data = DB::table('ordenesmtl')
//         ->select('zona','periodo','usuario', 'lote',
//          DB::raw('SUM(CASE WHEN estado_id > 0 THEN 1 ELSE 0 END) AS Asignados'),
//          DB::raw('SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) AS Pendientes'),
//          DB::raw('SUM(CASE WHEN estado_id = 4 THEN 1 ELSE 0 END) AS Ejecutadas'),
//          DB::raw('SUM(CASE WHEN oposicion = "SI" THEN 1 ELSE 0 END) AS oposicion'),
//          DB::raw('SUM(CASE WHEN mtl = "MTL" THEN 1 ELSE 0 END) AS MTL'),
//          DB::raw('SUM(CASE WHEN mtl = "MTLC" THEN 1 ELSE 0 END) AS MTL_TIPO_C'),
//          'fecha_de_ejecucion as inicio', 'fecha_de_ejecucion as Final')
//          ->where([
//              ['periodo', $request->periodo1],
//              ['zona', $request->zona1],
//              ['estado', '!=', 'CARGADO'],
//              ])
//          ->orderBy('inicio', 'asc')
//          ->orderBy('Final', 'desc')
//          ->groupBy('zona', 'periodo', 'usuario', 'lote', 'fecha_de_ejecucion')
//          ->get();
         
//      }else{

//          $data = DB::table('ordenesmtl')
//          ->select('zona','periodo','usuario', 'lote',
//          DB::raw('SUM(CASE WHEN estado_id > 0 THEN 1 ELSE 0 END) AS Asignados'),
//          DB::raw('SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) AS Pendientes'),
//          DB::raw('SUM(CASE WHEN estado_id = 4 THEN 1 ELSE 0 END) AS Ejecutadas'),
//          DB::raw('SUM(CASE WHEN oposicion = "SI" THEN 1 ELSE 0 END) AS oposicion'),
//          DB::raw('SUM(CASE WHEN mtl = "MTL" THEN 1 ELSE 0 END) AS MTL'),
//          DB::raw('SUM(CASE WHEN mtl = "MTLC" THEN 1 ELSE 0 END) AS MTL_TIPO_C'),
//          'fecha_de_ejecucion as inicio', 'fecha_de_ejecucion as Final')
//          ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
//          ->orderBy('inicio', 'asc')
//          ->orderBy('Final', 'desc')
//          ->groupBy('zona', 'periodo', 'usuario', 'lote', 'fecha_de_ejecucion')
//          ->get();

//      }

//     return  DataTables()->of($data)
//     ->addColumn('detalle','<input type="button" name="id[]" class=btn-accion-tabla tooltipsC" title="Consulta el detalle"
//     value="{{$id}}" />')
//     ->rowcolumn()
//     ->make(true);

//  }
 
 
//  return view('admin.detalleorden.index'); 





// }



}


