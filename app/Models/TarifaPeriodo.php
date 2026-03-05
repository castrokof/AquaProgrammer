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
        $consumoRestante = $consumoM3;
        $consumoAcumulado = 0;

        foreach ($rangos as $rango) {
            if ($consumoRestante <= 0) break;

            // Límites del rango
            $desde = $rango->rango_desde;
            $hasta = is_null($rango->rango_hasta) ? PHP_INT_MAX : $rango->rango_hasta;
            
            // Cuánto consumo puede entrar en este rango
            $capacidadRango = $hasta - $desde + 1;
            
            // Cuánto del consumo ya fue asignado a rangos anteriores
            $yaAsignadoAntesDeEsteRango = max(0, $consumoAcumulado - $desde);
            
            // Consumo que realmente corresponde a este rango
            $m3EnEsteRango = min($consumoRestante, $capacidadRango - $yaAsignadoAntesDeEsteRango);
            $m3EnEsteRango = max(0, $m3EnEsteRango);

            if ($m3EnEsteRango > 0) {
                $valor = round($m3EnEsteRango * $rango->precio_m3, 2);

                $resultado[] = [
                    'tipo'      => $rango->tipo,
                    'm3'        => $m3EnEsteRango,
                    'precio_m3' => (float) $rango->precio_m3,
                    'valor'     => $valor,
                ];

                $consumoRestante -= $m3EnEsteRango;
                $consumoAcumulado += $m3EnEsteRango;
            }
        }

        return $resultado;
    }
}
