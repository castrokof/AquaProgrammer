<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteHistoricoConsumo extends Model
{
    protected $table = 'cliente_historico_consumos';

    protected $fillable = [
        'cliente_id', 'suscriptor', 'periodo',
        'consumo_m3', 'lectura_anterior', 'lectura_actual', 'dias_facturados',
    ];

    protected $casts = [
        'consumo_m3'       => 'integer',
        'lectura_anterior' => 'integer',
        'lectura_actual'   => 'integer',
        'dias_facturados'  => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // ── Helpers estáticos ─────────────────────────────────────────────────────

    /**
     * Obtiene los últimos N meses de consumo para un cliente
     * y devuelve el promedio y el detalle por mes.
     *
     * @return array ['promedio' => float, 'meses' => [['periodo'=>'202404','consumo'=>40], …]]
     */
    public static function promedioYDetalle(int $clienteId, int $meses = 6): array
    {
        $registros = self::where('cliente_id', $clienteId)
            ->orderBy('periodo', 'desc')
            ->limit($meses)
            ->get(['periodo', 'consumo_m3']);

        $consumos = $registros->pluck('consumo_m3')->toArray();
        $promedio = count($consumos) > 0 ? round(array_sum($consumos) / count($consumos), 2) : 0;

        return [
            'promedio' => $promedio,
            'meses'    => $registros->toArray(),
        ];
    }

    /**
     * Inserta o actualiza el consumo del período.
     * Luego recalcula y guarda el promedio en clientes.promedio_consumo.
     */
    public static function registrarYActualizarPromedio(
        int $clienteId,
        string $suscriptor,
        string $periodo,
        int $consumoM3,
        ?int $lectAnterior = null,
        ?int $lectActual = null,
        int $diasFacturados = 30
    ): void {
        self::updateOrCreate(
            ['cliente_id' => $clienteId, 'periodo' => $periodo],
            [
                'suscriptor'       => $suscriptor,
                'consumo_m3'       => $consumoM3,
                'lectura_anterior' => $lectAnterior,
                'lectura_actual'   => $lectActual,
                'dias_facturados'  => $diasFacturados,
            ]
        );

        // Recalcular y persistir el promedio en el cliente
        $info = self::promedioYDetalle($clienteId, 6);
        Cliente::where('id', $clienteId)->update(['promedio_consumo' => $info['promedio']]);
    }
}
