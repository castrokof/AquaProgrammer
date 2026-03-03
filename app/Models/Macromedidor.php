<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\Usuario;

class Macromedidor extends Model
{
    protected $table = 'macromedidores';

    protected $fillable = [
        'codigo_macro',
        'ubicacion',
        'lectura_anterior',
        'estado',
        'lectura_actual',
        'observacion',
        'gps_latitud_lectura',
        'gps_longitud_lectura',
        'fecha_lectura',
        'sincronizado',
        'usuario_id',
    ];

    protected $casts = [
        'lectura_anterior'     => 'integer',
        'gps_latitud_lectura'  => 'double',
        'gps_longitud_lectura' => 'double',
        'sincronizado'         => 'boolean',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /** Fotos legacy (modelo anterior – se mantiene por compatibilidad) */
    public function fotos()
    {
        return $this->hasMany(MacroFoto::class, 'macromedidor_id');
    }

    /** Historial de lecturas (modelo nuevo – lectura diaria) */
    public function lecturas()
    {
        return $this->hasMany(MacroLectura::class, 'macromedidor_id')->orderBy('fecha_lectura', 'desc');
    }

    public function ultimaLectura()
    {
        return $this->hasOne(MacroLectura::class, 'macromedidor_id')->latest('fecha_lectura');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('usuario_id', $userId);
    }

    // ========================================
    // FORMATO PARA API MOVIL
    // ========================================

    /**
     * Formato que espera la app Android (MacroEntity).
     * lectura_anterior = última lectura registrada (o la inicial).
     * estado siempre PENDIENTE para que la app lo descargue siempre.
     */
    public function toApiArray()
    {
        // La lectura_anterior que ve la app es la última lectura_actual registrada
        $ultimaLectura = $this->ultimaLectura ?? null;
        $lecturaBase   = $ultimaLectura ? $ultimaLectura->lectura_actual : $this->lectura_anterior;

        return [
            'id_orden'              => $this->id,
            'codigo_macro'          => $this->codigo_macro,
            'ubicacion'             => $this->ubicacion,
            'lectura_anterior'      => $lecturaBase,
            'estado'                => 'PENDIENTE',   // siempre disponible para leer
            'lectura_actual'        => null,
            'observacion'           => null,
            'ruta_fotos'            => '',
            'gps_latitud_lectura'   => null,
            'gps_longitud_lectura'  => null,
            'fecha_lectura'         => null,
            'sincronizado'          => false,
        ];
    }
}
