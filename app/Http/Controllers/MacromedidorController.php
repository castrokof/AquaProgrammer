<?php

namespace App\Http\Controllers;

use App\Models\Macromedidor;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\Request;

/**
 * Controller WEB - CRUD de Macromedidores.
 * Los macros son dispositivos permanentes que se leen diariamente.
 * El historial de lecturas se almacena en macro_lecturas.
 */
class MacromedidorController extends Controller
{
    /**
     * GET /macromedidores
     */
    public function index(Request $request)
    {
        $query = Macromedidor::with('usuario', 'ultimaLectura');

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('codigo_macro', 'LIKE', '%' . $request->buscar . '%')
                  ->orWhere('ubicacion', 'LIKE', '%' . $request->buscar . '%');
            });
        }

        $macros   = $query->orderBy('codigo_macro')->paginate(50);
        $usuarios = Usuario::orderBy('nombre')->pluck('nombre', 'id');

        return view('macromedidores.index', compact('macros', 'usuarios'));
    }

    /**
     * GET /macromedidores/create
     */
    public function create()
    {
        $usuarios = Usuario::orderBy('nombre')->pluck('nombre', 'id');
        return view('macromedidores.create', compact('usuarios'));
    }

    /**
     * POST /macromedidores
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'codigo_macro'     => 'required|string|unique:macromedidores,codigo_macro',
            'ubicacion'        => 'nullable|string|max:500',
            'lectura_anterior' => 'nullable|integer|min:0',
            'usuario_id'       => 'required|exists:usuario,id',
        ]);

        Macromedidor::create([
            'codigo_macro'     => strtoupper(trim($request->codigo_macro)),
            'ubicacion'        => $request->ubicacion,
            'lectura_anterior' => $request->lectura_anterior ?: 0,
            'estado'           => 'PENDIENTE',
            'usuario_id'       => $request->usuario_id,
        ]);

        return redirect()->route('macromedidores.index')
            ->with('success', 'Macromedidor creado correctamente.');
    }

    /**
     * GET /macromedidores/{id}
     * Muestra el detalle con timeline de todas las lecturas.
     */
    public function show($id)
    {
        $macro = Macromedidor::with(['usuario', 'lecturas' => function ($q) {
            $q->with('fotos', 'usuario')->orderBy('fecha_lectura', 'desc');
        }])->findOrFail($id);

        return view('macromedidores.show', compact('macro'));
    }

    /**
     * GET /macromedidores/{id}/edit
     */
    public function edit($id)
    {
        $macro    = Macromedidor::findOrFail($id);
        $usuarios = Usuario::orderBy('nombre')->pluck('nombre', 'id');
        return view('macromedidores.edit', compact('macro', 'usuarios'));
    }

    /**
     * PUT /macromedidores/{id}
     */
    public function update(Request $request, $id)
    {
        $macro = Macromedidor::findOrFail($id);

        $this->validate($request, [
            'codigo_macro'     => 'required|string|unique:macromedidores,codigo_macro,' . $id,
            'ubicacion'        => 'nullable|string|max:500',
            'lectura_anterior' => 'nullable|integer|min:0',
            'usuario_id'       => 'required|exists:usuario,id',
        ]);

        $macro->update([
            'codigo_macro'     => strtoupper(trim($request->codigo_macro)),
            'ubicacion'        => $request->ubicacion,
            'lectura_anterior' => $request->lectura_anterior ?: 0,
            'usuario_id'       => $request->usuario_id,
        ]);

        return redirect()->route('macromedidores.show', $id)
            ->with('success', 'Macromedidor actualizado.');
    }

    /**
     * DELETE /macromedidores/{id}
     */
    public function destroy($id)
    {
        $macro = Macromedidor::findOrFail($id);

        if ($macro->lecturas()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar un macromedidor que ya tiene lecturas registradas.');
        }

        $macro->delete();

        return redirect()->route('macromedidores.index')
            ->with('success', 'Macromedidor eliminado.');
    }
}
