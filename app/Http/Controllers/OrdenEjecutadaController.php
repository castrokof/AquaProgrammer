<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Photos;
use App\Models\Admin\Orden_ejecutada;
use Illuminate\Support\Facades\Storage;

class OrdenEjecutadaController extends Controller
{
    public function medidorejecutado(Request $request)
    {   
        // ⭐ El usuario ya está autenticado por el middleware
        $user = $request->user();
        
        Log::info('Usuario autenticado: ' . $user->usuario);
        
        // Variables enviadas desde móvil
        $id_orden = $request->id;
        $Estado = $request->tipo;
        $urlfoto1 = $request->campoFoto;
        $critica1 = $request->critica;
        $causa = $request->causal;
        $causades = $request->texcausa;
        $observacion = $request->observ;
        $observaciondes = $request->texobser;
        $Lec = $request->lectact;
        $latitud = $request->latitud;
        $longitud = $request->longitud;
        $dateejec = Carbon::createFromFormat('d/m/Y H:i:s', $request->ffinlec);
        $dateejemplo = $request->ffinlec;
        
        DB::beginTransaction();
        
        $lectura_ejecutada = DB::table('orden_ejecutada')->where('id', $id_orden)->count();
        
        try {
            $url1 = "";
            
            if ($lectura_ejecutada <= 0 || $lectura_ejecutada == '' || $lectura_ejecutada == null) {
                
                if ($urlfoto1 != null && $urlfoto1 != "") { 
                    $imagen1 = base64_decode($urlfoto1);
                    $imagen_name1 = $id_orden.'_1.jpg';
                    $path1 = public_path('/imageneslectura/'.$imagen_name1);
                    file_put_contents($path1, $imagen1);
                    $img1 = Image::make(public_path('imageneslectura/'.$imagen_name1)); 
                    $textimage = $dateejec;
                    $img1->resize(640, 480);
                    $img1->text($textimage, 10, 35,
                        function($font){ 
                            $font->size(24);
                            $font->file(public_path('font/OpenSans-Regular.ttf'));
                            $font->color('#f1f505'); 
                            $font->align('left'); 
                            $font->valign('bottom'); 
                            $font->angle(0); 
                        }
                    ); 
                    $img1->save(public_path('imageneslectura/'.$imagen_name1)); 
                    $url1 = 'imageneslectura/'.$imagen_name1;
                }      
                
                $orden = DB::table('orden_ejecutada')
                    ->insert([
                        'id' => $id_orden,
                        'ordenejecutada_id' => $id_orden,
                        'suscriptor' => $request->suscriptor ?? 'sinsus',
                        'usuario' => $user->usuario, // ⭐ Usar el usuario autenticado
                        'tipo_usuario' => 'movil',
                        'fecha_de_ejecucion' => $dateejec,
                        'new_medidor' => null,
                        'Lect_Actual' => $Lec,
                        'Cons_Act' => $request->consumo,
                        'Comentario' => $request->observg,
                        'Critica' => $critica1,
                        'Desviacion' => null,
                        'coordenada' => null,
                        'latitud' => $latitud,
                        'longitud' => $longitud,
                        'estado' => 'EJECUTADO',
                        'estado_id' => $Estado,
                        'foto1' => $url1,
                        'foto2' => null,
                        'futuro1' => null,
                        'futuro2' => 0,
                        'futuro3' => $causa,
                        'futuro4' => 0,
                        'futuro5' => $observacion,
                        'futuro6' => $dateejemplo,
                        'created_at' => now()
                    ]);
                
                Log::info("Registro recibido: " . json_encode($request->all()) . ' Orden: ' . $id_orden);
                
                if ($orden) {
                    DB::table('ordenescu')
                        ->where('id', $id_orden)
                        ->update([
                            'Lect_Actual' => $Lec,
                            'Cons_Act' => $request->consumo,
                            'Critica' => $critica1,
                            'fecha_de_ejecucion' => $dateejec,
                            'foto1' => $url1,
                            'foto2' => null,  
                            'Coordenada' => null,
                            'Latitud' => $latitud,
                            'Longitud' => $longitud,
                            'Estado_des' => 'EJECUTADO',
                            'Estado' => $Estado,
                            'Causa_id' => $causa,
                            'Observacion_id' => $observacion,
                            'Causa_des' => $causades,
                            'Observacion_des' => $observaciondes,
                            'new_medidor' => $request->observg,
                            'updated_at' => now()
                        ]);
                    
                    DB::commit();         
                    Log::info("Registro recibido en cu y commit ok: " . json_encode($request->all()));
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Lectura cargada en servidor'
                    ], 200);
                }
                
            } else {
                // Actualización de registro existente
                $this->guardarFotoEnTabla($id_orden);        
                
                if ($urlfoto1 != null && $urlfoto1 != "") { 
                    $imagen1 = base64_decode($urlfoto1);
                    $imagen_name1 = $id_orden.'_1.jpg';
                    $path1 = public_path('/imageneslectura/'.$imagen_name1);
                    file_put_contents($path1, $imagen1);
                    $img1 = Image::make(public_path('imageneslectura/'.$imagen_name1)); 
                    $textimage = $dateejec;
                    $img1->resize(640, 480);
                    $img1->text($textimage, 10, 35,
                        function($font){ 
                            $font->size(24);
                            $font->file(public_path('font/OpenSans-Regular.ttf'));
                            $font->color('#f1f505'); 
                            $font->align('left'); 
                            $font->valign('bottom'); 
                            $font->angle(0); 
                        }
                    ); 
                    $img1->save(public_path('imageneslectura/'.$imagen_name1)); 
                    $url1 = 'imageneslectura/'.$imagen_name1;
                }      
                
                $ordenupdate = DB::table('orden_ejecutada')
                    ->where('id', $id_orden)
                    ->update([
                        'suscriptor' => $request->suscriptor ?? 'sinsus',
                        'usuario' => $user->usuario, // ⭐ Usar el usuario autenticado
                        'tipo_usuario' => 'movil',
                        'fecha_de_ejecucion' => $dateejec,
                        'new_medidor' => null,
                        'Lect_Actual' => $Lec,
                        'Cons_Act' => $request->consumo,
                        'Comentario' => $request->observg,
                        'Critica' => $critica1,
                        'Desviacion' => null,
                        'coordenada' => null,
                        'latitud' => $latitud,
                        'longitud' => $longitud,
                        'estado' => 'EJECUTADO',
                        'estado_id' => $Estado,
                        'foto1' => $url1,
                        'foto2' => null,
                        'futuro1' => "update",
                        'futuro2' => 0,
                        'futuro3' => $causa,
                        'futuro4' => 0,
                        'futuro5' => $observacion,
                        'futuro6' => $dateejemplo,
                        'updated_at' => now()
                    ]);
                
                Log::info("Registro recibido UPDATE: " . json_encode($request->all()) . ' Orden: ' . $id_orden);
                
                if ($ordenupdate) {
                    DB::table('ordenescu')
                        ->where('id', $id_orden)
                        ->update([
                            'Lect_Actual' => $Lec,
                            'Cons_Act' => $request->consumo,
                            'Critica' => $critica1,
                            'fecha_de_ejecucion' => $dateejec,
                            'foto1' => $url1,
                            'foto2' => "update",  
                            'Coordenada' => null,
                            'Latitud' => $latitud,
                            'Longitud' => $longitud,
                            'Estado_des' => 'EJECUTADO',
                            'Estado' => $Estado,
                            'Causa_id' => $causa,
                            'Observacion_id' => $observacion,
                            'Causa_des' => $causades,
                            'Observacion_des' => $observaciondes,
                            'new_medidor' => $request->observg,
                            'updated_at' => now()
                        ]);
                    
                    DB::commit();        
                    Log::info("Registro actualizado con commit: " . json_encode($request->all()));
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Lectura actualizada en servidor'
                    ], 200);
                }
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            
            $logMessage = "Registro con errores: " . $e->getMessage();
            Log::error($logMessage);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }            
    }
    
    public function guardarFotoEnTabla($id) {
        $orden = Orden_ejecutada::findOrFail($id);
        $nombreFoto = $orden->foto1; 
        
        if ($nombreFoto) {
            $rutaFoto = public_path($nombreFoto);
            
            if (file_exists($rutaFoto)) {
                $contenidoImagen = file_get_contents($rutaFoto);
                
                $photo = new Photos;
                $photo->photo_data = $contenidoImagen;
                $photo->id_orden_ejecutada = $id;
                $photo->save();
                
                Log::info("Registro actualizado en fotos " . $id);
            } else {
                Log::info('La foto no existe en la ruta especificada.' . $id . $nombreFoto);
            }
        } else {
            Log::info('No se encontró la foto.' . $id);
        }
    }
}