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
        \Illuminate\Support\Facades\Log::info('=== INICIO calcularConsumo ===', [
            'consumoM3' => $consumoM3,
            'servicio' => $servicio,
            'estratoId' => $estratoId,
            'tarifa_periodo_id' => $this->id,
        ]);

        $rangos = $this->rangos()
            ->where('servicio', $servicio)
            ->where('estrato_id', $estratoId)
            ->orderBy('rango_desde')
            ->get();

        \Illuminate\Support\Facades\Log::info('Rangos encontrados', [
            'cantidad' => $rangos->count(),
            'rangos' => $rangos->toArray(),
        ]);

        if ($rangos->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning('No hay rangos configurados para este servicio/estrato');
            return [];
        }

        $resultado = [];
        $consumoPorAsignar = $consumoM3;
        $consumoYaAsignado = 0;

        foreach ($rangos as $index => $rango) {
            \Illuminate\Support\Facades\Log::info('Procesando rango', [
                'index' => $index,
                'tipo' => $rango->tipo,
                'rango_desde' => $rango->rango_desde,
                'rango_hasta' => $rango->rango_hasta,
                'precio_m3' => $rango->precio_m3,
                'consumoPorAsignar_antes' => $consumoPorAsignar,
                'consumoYaAsignado_antes' => $consumoYaAsignado,
            ]);

            if ($consumoPorAsignar <= 0) {
                \Illuminate\Support\Facades\Log::info('No hay consumo por asignar, se sale del loop');
                break;
            }

            // Límites del rango
            $desde = $rango->rango_desde;
            $hasta = is_null($rango->rango_hasta) ? PHP_INT_MAX : $rango->rango_hasta;
            
            // El consumo total que debe haber llegado a este punto para entrar en este rango
            // es igual al 'desde' del rango. Si ya asignamos menos que eso, necesitamos
            // completar hasta llegar al inicio del rango antes de asignar a este rango.
            
            // Consumo que corresponde específicamente a este rango
            // Es la intersección entre [consumoYaAsignado, consumoYaAsignado + consumoPorAsignar] y [desde, hasta]
            $inicioInterseccion = max($consumoYaAsignado, $desde);
            $finInterseccion = min($consumoYaAsignado + $consumoPorAsignar, $hasta);
            
            $m3EnEsteRango = max(0, $finInterseccion - $inicioInterseccion);

            \Illuminate\Support\Facades\Log::info('Calculos del rango', [
                'desde' => $desde,
                'hasta' => $hasta,
                'inicioInterseccion' => $inicioInterseccion,
                'finInterseccion' => $finInterseccion,
                'm3EnEsteRango' => $m3EnEsteRango,
            ]);

            if ($m3EnEsteRango > 0) {
                $valor = round($m3EnEsteRango * $rango->precio_m3, 2);

                $resultado[] = [
                    'tipo'      => $rango->tipo,
                    'm3'        => $m3EnEsteRango,
                    'precio_m3' => (float) $rango->precio_m3,
                    'valor'     => $valor,
                ];

                \Illuminate\Support\Facades\Log::info('Agregado al resultado', [
                    'tipo' => $rango->tipo,
                    'm3' => $m3EnEsteRango,
                    'precio_m3' => $rango->precio_m3,
                    'valor' => $valor,
                ]);

                $consumoPorAsignar -= $m3EnEsteRango;
                $consumoYaAsignado += $m3EnEsteRango;
            }
        }

        \Illuminate\Support\Facades\Log::info('=== FIN calcularConsumo ===', [
            'resultado' => $resultado,
            'consumoPorAsignar_final' => $consumoPorAsignar,
            'consumoTotal_asignado' => $consumoYaAsignado,
        ]);

        return $resultado;
    }
}
