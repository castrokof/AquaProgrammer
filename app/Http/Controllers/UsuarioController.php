<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidacionUsuario;
use App\Models\Admin\Rol;
use App\Models\Seguridad\Usuario;
use Barryvdh\DomPDF\Facade as PDF;
use PHPUnit\TextUI\ResultPrinter;


class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {  
    
        $Rols1 = Rol::orderBy('id')->pluck('nombre', 'id')->toArray();
        $datas = Usuario::with('roles1:id,nombre')->orderBy('id')->get();
        return view('admin.usuario.index', compact('datas','Rols1'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crear()
    {
        return view('admin.usuario.crear');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guardar(ValidacionUsuario $request)
    {
        $usuario = Usuario::create($request->all());
        $usuario->roles1()->attach($request->rol_id);

        return redirect('usuario')->with('mensaje', 'Usuario creado con exito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editar($id)
    {   $Rols1 = Rol::orderBy('id')->pluck('nombre', 'id')->toArray();
        $data = Usuario::with('roles1')->findOrFail($id);
        return view('admin.usuario.editar', compact('data','Rols1'));
    }
    
    public function editarpassword($id)
    {   $data = Usuario::with('roles1')->findOrFail($id);
        return view('admin.usuario.editarpassword', compact('data'));
    }
    
    public function editarpassword1($id)
    {   $data = Usuario::with('roles1')->findOrFail($id);
        return view('admin.usuario.editarpassword1', compact('data'));
    }
    
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actualizar(ValidacionUsuario $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update($request->all());
        $usuario->roles1()->sync($request->rol_id);
        return redirect('usuario')->with('mensaje', 'Usuario actualizado con exito!!');
    }

    public function actualizarpassword(Request $request, $id)
    {
       Usuario::findOrFail($id)->update($request->all());
            
        return redirect('usuario')->with('mensaje', 'Password actualizado con exito!!');
    }
    public function actualizarpassword1(Request $request, $id)
    {                                 
        if ($request->ajax()) {
        
        if($request->password == $request->remenber_token){    
            Usuario::findOrFail($id)->update($request->all());
                return response()->json(['respuesta' => 'ok']);
            }else{

                return response()->json(['respuesta' => 'ng']);
            }
        }
            return redirect('admin/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function eliminar($id)
    {
        //
    }

    public function pdf(Request $request)
    {   //$Rols1 = Rol::orderBy('id')->pluck('nombre', 'id')->toArray();
        $datas = Usuario::with('roles1:id,nombre')->orderBy('id')->get();
        
        $pdf = PDF::loadView('admin.usuario.index1', compact('datas'));
        
        return 
        $pdf->setPaper('a4', 'landscape')->download('usuarios.pdf');
    }


}
