<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Factura;
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

    // ── Diseño de factura ──────────────────────────────────────────────────────

    public function editDiseno()
    {
        $empresa  = Empresa::instancia();
        $factura  = Factura::with(['cliente', 'pagos', 'tarifaPeriodo'])->latest()->first();
        return view('configuracion.diseno-factura', compact('empresa', 'factura'));
    }

    public function updateDiseno(Request $request)
    {
        $request->validate([
            'factura_color_primario'        => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'factura_color_acento'          => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'factura_subtitulo'             => 'nullable|string|max:150',
        ]);

        $empresa = Empresa::instancia();

        $data = $request->only([
            'factura_color_primario', 'factura_color_acento', 'factura_subtitulo',
        ]);

        // Checkboxes: si no viene el campo, vale false
        $bools = [
            'factura_mostrar_logo', 'factura_mostrar_lectura',
            'factura_mostrar_serie_medidor', 'factura_mostrar_sector',
            'factura_mostrar_tipo_uso', 'factura_mostrar_estrato',
            'factura_mostrar_tarifa', 'factura_mostrar_saldo_anterior',
            'factura_mostrar_creditos', 'factura_mostrar_barras_consumo',
            'factura_mostrar_observaciones', 'factura_mostrar_codigo_barras',
        ];
        foreach ($bools as $campo) {
            $data[$campo] = $request->boolean($campo);
        }

        $empresa->update($data);

        return redirect()->route('diseno-factura.edit')
            ->with('success', 'Diseño de factura guardado correctamente.');
    }

    /**
     * Devuelve el HTML de una factura de muestra con los parámetros de diseño
     * enviados por GET (preview en tiempo real sin guardar).
     */
    public function previewDiseno(Request $request)
    {
        // Construir empresa virtual con los valores del request
        $empresa = Empresa::instancia();
        $empresa->factura_color_primario        = $request->get('factura_color_primario',  $empresa->colorPrimario());
        $empresa->factura_color_acento          = $request->get('factura_color_acento',    $empresa->colorAcento());
        $empresa->factura_subtitulo             = $request->get('factura_subtitulo',        $empresa->factura_subtitulo ?? 'Servicio Público Domiciliario');
        $empresa->factura_mostrar_logo          = filter_var($request->get('factura_mostrar_logo',          true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_lectura       = filter_var($request->get('factura_mostrar_lectura',       true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_serie_medidor = filter_var($request->get('factura_mostrar_serie_medidor', true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_sector        = filter_var($request->get('factura_mostrar_sector',        true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_tipo_uso      = filter_var($request->get('factura_mostrar_tipo_uso',      true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_estrato       = filter_var($request->get('factura_mostrar_estrato',       true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_tarifa        = filter_var($request->get('factura_mostrar_tarifa',        true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_saldo_anterior= filter_var($request->get('factura_mostrar_saldo_anterior',true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_creditos      = filter_var($request->get('factura_mostrar_creditos',      true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_barras_consumo= filter_var($request->get('factura_mostrar_barras_consumo',true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_observaciones = filter_var($request->get('factura_mostrar_observaciones', true), FILTER_VALIDATE_BOOLEAN);
        $empresa->factura_mostrar_codigo_barras = filter_var($request->get('factura_mostrar_codigo_barras', true), FILTER_VALIDATE_BOOLEAN);

        $factura  = Factura::with(['cliente', 'pagos', 'tarifaPeriodo'])->latest()->first();
        $facturas = $factura ? collect([$factura]) : collect();

        return response()->view('facturacion.facturas.pdf', compact('facturas', 'empresa'))
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }
}
