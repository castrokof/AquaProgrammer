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
    ];

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
