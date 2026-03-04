<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaPeriodo extends Model
{
    protected $table = 'tarifa_periodos';

    protected $fillable = [
        'nombre', 'numero_resolucion', 'vigente_desde', 'vigente_hasta', 'activo', 'observaciones',
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
        'activo'        => 'boolean',
    ];

    public function cargos()
    {
        return $this->hasMany(TarifaCargoFijo::class, 'tarifa_periodo_id');
    }

    public function rangos()
    {
        return $this->hasMany(TarifaRango::class, 'tarifa_periodo_id');
    }

    public function periodosLectura()
    {
        return $this->hasMany(PeriodoLectura::class, 'tarifa_periodo_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'tarifa_periodo_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Devuelve la tarifa actualmente vigente */
    public static function vigente(): ?self
    {
        return self::where('activo', true)
            ->whereNull('vigente_hasta')
            ->orWhere('vigente_hasta', '>=', now()->toDateString())
            ->orderBy('vigente_desde', 'desc')
            ->first();
    }

    /**
     * Obtiene el cargo fijo para un servicio y estrato.
     */
    public function cargoFijo(string $servicio, int $estratoId): float
    {
        $cargo = $this->cargos()
            ->where('servicio', $servicio)
            ->where('estrato_id', $estratoId)
            ->first();

        return $cargo ? (float) $cargo->cargo_fijo : 0.0;
    }

    /**
     * Desglosa el consumo en m³ por tipo (BASICO/COMPLEMENTARIO/SUNTUARIO)
     * devuelve [['tipo'=>'BASICO','m3'=>16,'precio_m3'=>1050,'valor'=>16800], …]
     */
    public function calcularConsumo(int $consumoM3, string $servicio, int $estratoId): array
    {
        $rangos = $this->rangos()
            ->where('servicio', $servicio)
            ->where('estrato_id', $estratoId)
            ->orderBy('rango_desde')
            ->get();

        $resultado = [];
        $restante = $consumoM3;

        foreach ($rangos as $rango) {
            if ($restante <= 0) break;

            $limite = is_null($rango->rango_hasta)
                ? $restante
                : ($rango->rango_hasta - $rango->rango_desde);

            $m3 = min($restante, $limite);
            $valor = round($m3 * $rango->precio_m3, 2);

            $resultado[] = [
                'tipo'      => $rango->tipo,
                'm3'        => $m3,
                'precio_m3' => (float) $rango->precio_m3,
                'valor'     => $valor,
            ];

            $restante -= $m3;
        }

        return $resultado;
    }
}
