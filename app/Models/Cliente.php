<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Ordenesmtl;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'suscriptor',
        'nuip',
        'tipo_documento',
        'nombre',
        'apellido',
        'telefono',
        'direccion',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    public function fotos()
    {
        return $this->hasMany(ClienteFoto::class, 'cliente_id');
    }

    /** Lecturas/órdenes históricas ligadas al suscriptor */
    public function ordenes()
    {
        return $this->hasMany(Ordenesmtl::class, 'Suscriptor', 'suscriptor');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('suscriptor', 'LIKE', "%{$termino}%")
              ->orWhere('nuip', 'LIKE', "%{$termino}%")
              ->orWhere('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('apellido', 'LIKE', "%{$termino}%");
        });
    }

    // ========================================
    // HELPERS
    // ========================================

    /**
     * Crea o actualiza el perfil del cliente a partir de los datos de una orden.
     * Devuelve el modelo Cliente resultante.
     */
    public static function upsertDesdeDatos(array $datos): self
    {
        $cliente = self::firstOrNew(['suscriptor' => $datos['suscriptor']]);

        // Solo sobreescribir campos si vienen informados
        foreach (['nuip', 'tipo_documento', 'nombre', 'apellido', 'telefono', 'direccion'] as $campo) {
            if (!empty($datos[$campo])) {
                $cliente->$campo = $datos[$campo];
            }
        }

        $cliente->save();
        return $cliente;
    }

    /** Formato para la app móvil */
    public function toApiArray(): array
    {
        return [
            'id'             => $this->id,
            'suscriptor'     => $this->suscriptor,
            'nuip'           => $this->nuip,
            'tipo_documento' => $this->tipo_documento,
            'nombre'         => $this->nombre,
            'apellido'       => $this->apellido,
            'telefono'       => $this->telefono,
            'direccion'      => $this->direccion,
            'ruta_fotos'     => $this->fotos->pluck('ruta_foto')->implode(','),
        ];
    }
}
