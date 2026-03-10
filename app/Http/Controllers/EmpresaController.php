<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    public function edit()
    {
        $empresa = Empresa::instancia();
        return view('configuracion.empresa', compact('empresa'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nombre'                      => 'required|string|max:150',
            'nit'                         => 'nullable|string|max:30',
            'direccion'                   => 'nullable|string|max:200',
            'telefono'                    => 'nullable|string|max:50',
            'email'                       => 'nullable|email|max:100',
            'logo'                        => 'nullable|image|max:2048',
            'prefijo_factura'             => 'nullable|string|max:20',
            'texto_documento_equivalente' => 'nullable|string|max:200',
            'texto_pie'                   => 'nullable|string|max:300',
            'nombre_banco'                => 'nullable|string|max:100',
            'numero_cuenta'               => 'nullable|string|max:50',
            'wompi_public_key'            => 'nullable|string|max:120',
            'wompi_private_key'           => 'nullable|string|max:120',
            'wompi_integrity_key'         => 'nullable|string|max:120',
            'wompi_test_mode'             => 'nullable|boolean',
            'wompi_redirect_url'          => 'nullable|url|max:300',
        ]);

        $empresa = Empresa::instancia();

        $data = $request->only([
            'nombre', 'nit', 'direccion', 'telefono', 'email',
            'prefijo_factura', 'texto_documento_equivalente',
            'texto_pie', 'nombre_banco', 'numero_cuenta',
            'wompi_public_key', 'wompi_private_key', 'wompi_integrity_key',
            'wompi_redirect_url',
        ]);

        $data['wompi_test_mode'] = $request->input('wompi_test_mode', 1) ? true : false;

        if ($request->hasFile('logo')) {
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $empresa->update($data);

        return redirect()->route('empresa.edit')
            ->with('success', 'Configuración de empresa guardada correctamente.');
    }
}
