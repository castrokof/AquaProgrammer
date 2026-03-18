<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre', 'nit', 'direccion', 'telefono', 'email',
        'logo_path', 'prefijo_factura', 'texto_documento_equivalente',
        'texto_pie', 'nombre_banco', 'numero_cuenta',
        'wompi_public_key', 'wompi_private_key', 'wompi_integrity_key',
        'wompi_test_mode', 'wompi_redirect_url',
        // Diseño de factura
        'factura_color_primario', 'factura_color_acento', 'factura_subtitulo',
        'factura_mostrar_logo', 'factura_mostrar_lectura',
        'factura_mostrar_serie_medidor', 'factura_mostrar_sector',
        'factura_mostrar_tipo_uso', 'factura_mostrar_estrato',
        'factura_mostrar_tarifa', 'factura_mostrar_saldo_anterior',
        'factura_mostrar_creditos', 'factura_mostrar_barras_consumo',
        'factura_mostrar_observaciones', 'factura_mostrar_codigo_barras',
    ];

    protected $casts = [
        'wompi_test_mode'               => 'boolean',
        'factura_mostrar_logo'          => 'boolean',
        'factura_mostrar_lectura'       => 'boolean',
        'factura_mostrar_serie_medidor' => 'boolean',
        'factura_mostrar_sector'        => 'boolean',
        'factura_mostrar_tipo_uso'      => 'boolean',
        'factura_mostrar_estrato'       => 'boolean',
        'factura_mostrar_tarifa'        => 'boolean',
        'factura_mostrar_saldo_anterior'=> 'boolean',
        'factura_mostrar_creditos'      => 'boolean',
        'factura_mostrar_barras_consumo'=> 'boolean',
        'factura_mostrar_observaciones' => 'boolean',
        'factura_mostrar_codigo_barras' => 'boolean',
    ];

    /** Devuelve el color primario con fallback al azul por defecto. */
    public function colorPrimario(): string
    {
        return $this->factura_color_primario ?: '#2e50e4';
    }

    /** Devuelve el color de acento con fallback. */
    public function colorAcento(): string
    {
        return $this->factura_color_acento ?: '#2e50e4';
    }

    /** Obtiene (o crea) el único registro de configuración. */
    public static function instancia(): self
    {
        try {
            return self::firstOrCreate(['id' => 1], [
                'nombre'                     => 'EMPRESA DE SERVICIOS PÚBLICOS',
                'texto_documento_equivalente'=> 'Documento Equivalente Servicios Públicos Domiciliarios',
            ]);
        } catch (\Throwable $e) {
            // Tabla aún no migrada: retorna instancia en memoria con valores por defecto
            $default = new self();
            $default->nombre                      = 'EMPRESA DE SERVICIOS PÚBLICOS';
            $default->texto_documento_equivalente = 'Documento Equivalente Servicios Públicos Domiciliarios';
            return $default;
        }
    }

    /** URL pública del logo, o null si no hay. */
    public function logoUrl(): ?string
    {
        if (!$this->logo_path) return null;
        return asset('storage/' . $this->logo_path);
    }

    /** Ruta absoluta al archivo del logo para incrustarlo en PDF (base64). */
    public function logoBase64(): ?string
    {
        if (!$this->logo_path) return null;
        $path = storage_path('app/public/' . $this->logo_path);
        if (!file_exists($path)) return null;
        $mime = mime_content_type($path);
        $data = base64_encode(file_get_contents($path));
        return "data:{$mime};base64,{$data}";
    }
}
