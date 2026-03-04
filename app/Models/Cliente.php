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
        'serie_medidor',
        // Campos de facturación
        'estrato_id',
        'servicios',
        'tipo_uso',
        'tiene_medidor',
        'sector',
        'promedio_consumo',
        'estado',
        'fecha_corte',
    ];

    protected $casts = [
        'tiene_medidor'    => 'boolean',
        'promedio_consumo' => 'decimal:2',
        'fecha_corte'      => 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function estrato()
    {
        return $this->belongsTo(Estrato::class, 'estrato_id');
    }

    public function fotos()
    {
        return $this->hasMany(ClienteFoto::class, 'cliente_id');
    }

    public function series()
    {
        return $this->hasMany(ClienteSerie::class, 'cliente_id')->orderBy('periodo', 'desc');
    }

    /** Lecturas/órdenes históricas ligadas al suscriptor */
    public function ordenes()
    {
        return $this->hasMany(Ordenesmtl::class, 'Suscriptor', 'suscriptor');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'cliente_id')->orderBy('periodo', 'desc');
    }

    public function otrosCobros()
    {
        return $this->hasMany(ClienteOtrosCobro::class, 'cliente_id');
    }

    public function historicoConsumos()
    {
        return $this->hasMany(ClienteHistoricoConsumo::class, 'cliente_id')->orderBy('periodo', 'desc');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('suscriptor', 'LIKE', "%{$termino}%")
              ->orWhere('nuip', 'LIKE', "%{$termino}%")
              ->orWhere('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('apellido', 'LIKE', "%{$termino}%");
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Indica si la lectura está dentro del rango normal del promedio (±tolerance%) */
    public function lecturaEsNormal(int $consumoM3, float $tolerancia = 0.50): bool
    {
        if ($this->promedio_consumo <= 0) {
            return true; // Sin historial, se considera normal
        }
        $diff = abs($consumoM3 - $this->promedio_consumo) / $this->promedio_consumo;
        return $diff <= $tolerancia;
    }

    /** Devuelve los 6 últimos consumos individuales como array [m1..m6] */
    public function ultimosSeisMeses(): array
    {
        $rows = $this->historicoConsumos()->limit(6)->pluck('consumo_m3')->toArray();
        while (count($rows) < 6) {
            $rows[] = null;
        }
        return $rows;
    }

    // ── Helpers legados ───────────────────────────────────────────────────────

    /**
     * Crea o actualiza el perfil del cliente (helper legado).
     * Si el cliente es nuevo, auto-puebla nombre/dirección/teléfono/serie desde
     * la orden de lectura más reciente registrada en el sistema (Ordenesmtl).
     * Devuelve el modelo Cliente resultante.
     */
    public static function upsertDesdeDatos(array $datos): self
    {
        $esNuevo  = !self::where('suscriptor', $datos['suscriptor'])->exists();
        $cliente  = self::firstOrNew(['suscriptor' => $datos['suscriptor']]);

        // Si es nuevo, intentar pre-llenar desde la última orden cargada en el sistema
        if ($esNuevo) {
            $orden = Ordenesmtl::where('Suscriptor', $datos['suscriptor'])
                ->orderBy('Periodo', 'desc')
                ->first();

            if ($orden) {
                $cliente->nombre        = trim($orden->Nombre ?? '');
                $cliente->apellido      = trim($orden->Apell ?? '');
                $cliente->direccion     = $orden->Direccion ?? null;
                $cliente->telefono      = $orden->Telefono ?? null;
                // Ref_Medidor como serie inicial si no viene en los datos
                if (empty($datos['serie_medidor']) && !empty($orden->Ref_Medidor)) {
                    $cliente->serie_medidor = $orden->Ref_Medidor;
                }
            }
        }

        // Los datos explícitos siempre sobreescriben (si vienen informados)
        foreach (['nuip', 'tipo_documento', 'nombre', 'apellido', 'telefono', 'direccion', 'serie_medidor'] as $campo) {
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
            'serie_medidor'  => $this->serie_medidor,
            'foto_medidor'   => optional($this->fotos->where('tipo', 'medidor')->first())->ruta_foto,
            'foto_predio'    => optional($this->fotos->where('tipo', 'predio')->first())->ruta_foto,
            'ruta_fotos'     => $this->fotos->pluck('ruta_foto')->implode(','),
        ];
    }
}
