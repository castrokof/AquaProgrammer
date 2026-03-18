<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exportacion extends Model
{
    protected $table = 'exportaciones';

    protected $fillable = [
        'usuario_id',
        'ids',
        'estado',
        'total',
        'procesados',
        'progreso',
        'archivo',
        'mensaje_error',
        'periodo',
        'id_ruta',
        'tipo',
    ];

    protected $casts = [
        'ids' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    /** Actualiza progreso y estado basado en cuántos se han procesado */
    public function actualizarProgreso(int $procesados): void
    {
        $total    = $this->total ?: 1;
        $progreso = (int) round(($procesados / $total) * 100);

        $this->update([
            'procesados' => $procesados,
            'progreso'   => min($progreso, 99), // 100% solo cuando esté LISTO
        ]);
    }

    public function marcarListo(string $archivo): void
    {
        $this->update([
            'estado'     => 'LISTO',
            'progreso'   => 100,
            'procesados' => $this->total,
            'archivo'    => $archivo,
        ]);
    }

    public function marcarError(string $mensaje): void
    {
        $this->update([
            'estado'         => 'ERROR',
            'mensaje_error'  => $mensaje,
        ]);
    }
}
