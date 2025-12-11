<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Marcas;
use Illuminate\Support\Facades\DB;

class MarcasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = Marcas::orderBy('id')->get();
        return view('admin.marcas.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crear()
    {
        return view('admin.marcas.crear');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guardar(Request $request)
    {
        Marcas::create($request->all());
        return redirect('marca')->with('mensaje', 'Motivo creado con exito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function mostrar($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editar($id)
    {
        $data = Marcas::findOrFail($id);
        return view('admin.marcas.editar', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actualizar(Request $request, $id)
    {
        Marcas::findOrFail($id)->update($request->all());
        return redirect('marca')->with('mensaje', 'Registro actualizado con exito!!');
    }
    
    public function marcasall(Request $request)
    {   
        // El usuario ya está autenticado por el middleware
        $usuario = $request->user();
        
        
        // Verificar si el usuario tiene órdenes pendientes
        $tieneOrdenes = DB::table('ordenescu')
            ->where('Usuario', $usuario->usuario)
            ->where('Estado', '2') // O el estado que necesites
            ->exists();
        
        if ($tieneOrdenes) {    
            $marcas = Marcas::all();
            return response()->json($marcas, 200);
        } else {
            return response()->json(['error' => 'No puede sincronizar listas'], 403);
        }  
    }
}
