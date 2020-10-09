<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;

class OrdenEjecutadaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  public function medidorejecutado(Request $request)
  {   
    
    
    $url1=null;   
    $id_orden=$request->Id_Orden;
    $id_orden_lec=$request->id_Orden_lectura;
    $Estado=$request->Estado;
    $urlfoto1=$request->UrlFoto;
    $critica1=$request->Desviacion;
    $causa=$request->causa_desc;
    $consumo=$request->Consumo;
    $Lec=$request->Lectura;
    /*$VEOCU = DB::table('ordenescu')
   ->where([
    ['Estado','=',2],
    ['id','=',$id_orden],
    ])            
    ->count();*/
    /*$VEOEJE = DB::table('orden_ejecutada')
    ->where([
    ['estado_id','=',4],
    ['ordenejecutada_id','=',$id_orden],
    ])            
    ->count();*/
    
    if($causa != null ){

      $critica = 'CAUSADO';

    }else if($critica1 < 0){

      $critica = 'NEGATIVO';

    }else if($consumo == 0){

      $critica = 'CONSUMO CERO';

    }else if($critica1 >= 1.65 ){

      $critica = 'ALTO CONSUMO';

    }else if($critica1 > 0 && $critica1 < 0.65){

      $critica = 'BAJO CONSUMO';

    }else if($critica1 > 0.64 && $critica1 < 1.65){

      $critica = 'NORMAL';

    }
    
    
  if($id_orden > 0 && $Estado == 4 && $urlfoto1 != null && $critica != 'NORMAL' && $critica != 'CAUSADO'){
    
    // Subimos la foto 1
  
    
    $imagen1 = base64_decode($urlfoto1);
    $imagen_name1 = $id_orden.'_1.jpg';
    $path1 = public_path('/imageneslectura/'.$imagen_name1);
    file_put_contents($path1, $imagen1);
    $img1 = Image::make(public_path('imageneslectura/'.$imagen_name1)); 
    $textimage = $request->Fechar_Gestion;
    $img1->resize(640, 480);
    $img1->text($textimage, 10, 35,
     function($font){ 
       $font->size(24);
       $font->file(public_path('font/OpenSans-Regular.ttf'));
       $font->color('#f1f505'); 
       $font->align('left'); 
       $font->valign('bottom'); 
       $font->angle(0); }); 
    $img1->save(public_path('imageneslectura/'.$imagen_name1)); 
    
    $url1='imageneslectura/'.$imagen_name1;
     
   
   
   $ORDEN = DB::table('orden_ejecutada')
          ->insert([
          'id' => $id_orden,
          'ordenejecutada_id' => $id_orden_lec,
          'suscriptor' => null,
          'usuario' => $request->Usuario,
          'tipo_usuario' => null,
          'fecha_de_ejecucion' => $request->Fechar_Gestion,
          'new_medidor' => $request->nuevo_medidor,
          'Lect_Actual' => $Lec,
          'Cons_Act' => $request->Consumo,
          'Comentario' => $request->Comentario,
          'Critica' => $critica,
          'Desviacion' => $request->Desviacion,
          'coordenada' => null,
          'latitud' => $request->Latitud,
          'longitud' => $request->Longitud,
          'estado' => 'EJECUTADO',
          'estado_id' => $request->Estado,
          'foto1' => $url1,
          'foto2' => null,
          'futuro1' => $request->causa_desc,
          'futuro2' => 0,
          'futuro3' => $request->Observacion,
          'futuro4' => 0,
          'futuro5' => $request->Causa,
          'futuro6' => null,
          'created_at'=>now()
                                   
            ]);

         
        DB::table('ordenescu')
                ->where('id','=',$id_orden)
                ->update([
                  'Usuario' => $request->Usuario,
                  'Lect_Actual' => $Lec,
                  'Cons_Act' => $request->Consumo,
                  'Critica' => $critica,
                  'fecha_de_ejecucion' => $request->Fechar_Gestion,
                  'foto1' => $url1,
                  'foto2' => null,  
                  'Coordenada' => $request->Comentario,
                  'Latitud' => $request->Latitud,
                  'Longitud' => $request->Longitud,
                  'Estado_des' => 'EJECUTADO',
                  'Estado' => $request->Estado,
                  'Causa_id' => 0,
                  'Observacion_id' => 0,
                  'Causa_des' => $request->causa_desc,
                  'Observacion_des' => $request->Observacion,
                  'new_medidor' => $request->nuevo_medidor,
                  'updated_at'=>now()
                 
                ]); 
               

        return response()->json(0);
    
    } else if($id_orden > 0 && $Estado == 4 && $critica == 'NORMAL'){
    
    
   
   $ORDEN = DB::table('orden_ejecutada')
          ->insert([
          'id' => $id_orden,
          'ordenejecutada_id' => $id_orden_lec,
          'suscriptor' => null,
          'usuario' => $request->Usuario,
          'tipo_usuario' => null,
          'fecha_de_ejecucion' => $request->Fechar_Gestion,
          'new_medidor' => $request->nuevo_medidor,
          'Lect_Actual' => $Lec,
          'Cons_Act' => $request->Consumo,
          'Comentario' => $request->Comentario,
          'Critica' => $critica,
          'Desviacion' => $request->Desviacion,
          'coordenada' => null,
          'latitud' => $request->Latitud,
          'longitud' => $request->Longitud,
          'estado' => 'EJECUTADO',
          'estado_id' => $request->Estado,
          'foto1' => $url1,
          'foto2' => null,
          'futuro1' => $request->causa_desc,
          'futuro2' => 0,
          'futuro3' => $request->Observacion,
          'futuro4' => 0,
          'futuro5' => $request->Causa,
          'futuro6' => null,
          'created_at'=>now()
                                   
            ]);

         
        DB::table('ordenescu')
                ->where('id','=',$id_orden)
                ->update([
                  'Usuario' => $request->Usuario,
                  'Lect_Actual' => $Lec,
                  'Cons_Act' => $request->Consumo,
                  'Critica' => $critica,
                  'fecha_de_ejecucion' => $request->Fechar_Gestion,
                  'foto1' => $url1,
                  'foto2' => null,  
                  'Coordenada' => $request->Comentario,
                  'Latitud' => $request->Latitud,
                  'Longitud' => $request->Longitud,
                  'Estado_des' => 'EJECUTADO',
                  'Estado' => $request->Estado,
                  'Causa_id' => 0,
                  'Observacion_id' => 0,
                  'Causa_des' => $request->causa_desc,
                  'Observacion_des' => $request->Observacion,
                  'new_medidor' => $request->nuevo_medidor,
                  'updated_at'=>now()
                 
                ]);
                
                
                 return response()->json(0);
                
                /*else if($id_orden > 0 && $Estado == 4 &&  $urlfoto1 != null ){

      // Subimos la foto 1
    $imagen1 = base64_decode($urlfoto1);

    if(!empty($imagen1)){

    $imagen_name1 = $id_orden.'_1.jpg';
    $path1 = public_path('/imageneslectura/'.$imagen_name1);
    file_put_contents($path1, $imagen1);
    $img1 = Image::make(public_path('imageneslectura/'.$imagen_name1)); 
    $textimage = $request->fecha_de_ejecucion;
    $img1->resize(640, 480);
    $img1->text($textimage, 10, 35,
     function($font){ 
       $font->size(24);
       $font->file(public_path('font/OpenSans-Regular.ttf'));
       $font->color('#f1f505'); 
       $font->align('left'); 
       $font->valign('bottom'); 
       $font->angle(0); }); 
    $img1->save(public_path('imageneslectura/'.$imagen_name1)); 
    
    $url1='imageneslectura/'.$imagen_name1;
     }
      

        DB::table('orden_ejecutada')
        ->where('id','=',$id_orden)
        ->update([
          
          'suscriptor' => null,
          'usuario' => $request->Usuario,
          'tipo_usuario' => null,
          'fecha_de_ejecucion' => $request->Fechar_Gestion,
          'new_medidor' => $request->nuevo_medidor,
          'new_marca' => $request->new_marca,
          'Lect_Actual' => $request->Lectura,
          'Cons_Act' => $request->Consumo,
          'Comentario' => $request->Comentario,
          'Critica' => $critica,
          'Desviacion' => $request->Desviacion,
          'coordenada' => null,
          'latitud' => $request->Latitud,
          'longitud' => $request->Longitud,
          'estado' => 'EJECUTADO',
          'estado_id' => $request->Estado,
          'foto1' => $url1,
          'foto2' => null,
          'futuro1' => $request->Causa,
          'futuro2' => $request->Observacion,
          'futuro3' => null,
          'futuro4' => null,
          'futuro5' => null,
          'futuro6' => null,
             
            ]);

            DB::table('ordenescu')
            ->where('id','=',$id_orden)
            ->update([
             
              'Usuario' => $request->Usuario,
              'Lect_Actual' => $request->Lectura,
              'Cons_Act' => $request->Consumo,
              'Critica' => $critica,
              'fecha_de_ejecucion' => $request->Fechar_Gestion,
              'foto1' => $url1,
              'foto2' => null,  
              'Coordenada' => null,
              'Latitud' => $request->Latitud,
              'Longitud' => $request->Longitud,
              'Estado_des' => 'EJECUTADO',
              'Estado' => $request->Estado,
              'Causa_id' => $request->Causa,
              'Observacion_id' => $request->Observacion,
              'Causa_des' => null,
              'Observacion_des' => null,
              'new_medidor' => $request->nuevo_medidor,
              'updated_at'=>now()
              
            ]); 
           


        return response()->json(0);*/
       
      }else if($id_orden > 0 && $Estado == 4 && $urlfoto1 != null && $critica = 'CAUSADO'){
          
          
    
    // Subimos la foto 1
  
    
    $imagen1 = base64_decode($urlfoto1);
    $imagen_name1 = $id_orden.'_1.jpg';
    $path1 = public_path('/imageneslectura/'.$imagen_name1);
    file_put_contents($path1, $imagen1);
    $img1 = Image::make(public_path('imageneslectura/'.$imagen_name1)); 
    $textimage = $request->Fechar_Gestion;
    $img1->resize(640, 480);
    $img1->text($textimage, 10, 35,
     function($font){ 
       $font->size(24);
       $font->file(public_path('font/OpenSans-Regular.ttf'));
       $font->color('#f1f505'); 
       $font->align('left'); 
       $font->valign('bottom'); 
       $font->angle(0); }); 
    $img1->save(public_path('imageneslectura/'.$imagen_name1)); 
    
    $url1='imageneslectura/'.$imagen_name1;
     
   
   
   $ORDEN = DB::table('orden_ejecutada')
          ->insert([
          'id' => $id_orden,
          'ordenejecutada_id' => $id_orden_lec,
          'suscriptor' => null,
          'usuario' => $request->Usuario,
          'tipo_usuario' => null,
          'fecha_de_ejecucion' => $request->Fechar_Gestion,
          'new_medidor' => $request->nuevo_medidor,
          'Lect_Actual' => null,
          'Cons_Act' => null,
          'Comentario' => $request->Comentario,
          'Critica' => $critica,
          'Desviacion' => $request->Desviacion,
          'coordenada' => null,
          'latitud' => $request->Latitud,
          'longitud' => $request->Longitud,
          'estado' => 'EJECUTADO',
          'estado_id' => $request->Estado,
          'foto1' => $url1,
          'foto2' => null,
          'futuro1' => $request->causa_desc,
          'futuro2' => 0,
          'futuro3' => $request->Observacion,
          'futuro4' => 0,
          'futuro5' => $request->Causa,
          'futuro6' => null,
          'created_at'=>now()
                                   
            ]);

         
        DB::table('ordenescu')
                ->where('id','=',$id_orden)
                ->update([
                  'Usuario' => $request->Usuario,
                  'Lect_Actual' => null,
                  'Cons_Act' => null,
                  'Critica' => $critica,
                  'fecha_de_ejecucion' => $request->Fechar_Gestion,
                  'foto1' => $url1,
                  'foto2' => null,  
                  'Coordenada' => $request->Comentario,
                  'Latitud' => $request->Latitud,
                  'Longitud' => $request->Longitud,
                  'Estado_des' => 'EJECUTADO',
                  'Estado' => $request->Estado,
                  'Causa_id' => 0,
                  'Observacion_id' => 0,
                  'Causa_des' => $request->causa_desc,
                  'Observacion_des' => $request->Observacion,
                  'new_medidor' => $request->nuevo_medidor,
                  'updated_at'=>now()
                 
                ]); 
               

        return response()->json(0);
          
          
          

        }return response()->json(1);
        
    
      
      
  }
      
  
    
    


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
