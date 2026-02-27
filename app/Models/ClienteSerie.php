<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteSerie extends Model
{
    protected $table = 'cliente_series';

    protected $fillable = [
        'cliente_id',
        'serie',
        'periodo',
        'fecha_registro',
        'orden_ejecutada_id',
    ];

    protected $casts = [
        'fecha_registro' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Registra la serie solo si es distinta a la última registrada para ese período.
     */
    public static function registrar(int $clienteId, string $serie, string $periodo, $ordenEjecutadaId = null): void
    {
        // Si ya existe un registro para este período con la misma serie, no duplicar
        $existe = self::where('cliente_id', $clienteId)
            ->where('periodo', $periodo)
            ->where('serie', $serie)
            ->exists();

        if (!$existe) {
            self::create([
                'cliente_id'        => $clienteId,
                'serie'             => $serie,
                'periodo'           => $periodo,
                'fecha_registro'    => now()->toDateString(),
                'orden_ejecutada_id' => $ordenEjecutadaId,
            ]);
        }
    }
}
