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

        foreach ($rangos as $rango) {
            if ($consumoRestante <= 0) break;

            // Calcular el tamaño máximo del rango
            $tamanoRango = is_null($rango->rango_hasta)
                ? PHP_INT_MAX  // Ilimitado para suntuario
                : ($rango->rango_hasta - $rango->rango_desde + 1);

            // Calcular cuántos m³ del consumo restante caen en este rango
            // El consumo ya consumido es: consumoM3 - consumoRestante
            $consumoYaAsignado = $consumoM3 - $consumoRestante;
            
            // Si el consumo ya asignado supera el rango desde, empezamos desde donde quedamos
            $inicioEnRango = max(0, $consumoYaAsignado - $rango->rango_desde);
            
            // Los m³ que realmente corresponden a este rango
            $m3EnEsteRango = min($consumoRestante, $tamanoRango - $inicioEnRango);
            
            // Asegurar que no sea negativo
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
            }
        }

        return $resultado;
    }
}
