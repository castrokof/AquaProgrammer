<?php

namespace App\Imports;

use App\Models\Admin\Ordenesmtl;
use App\Models\Cliente;
use App\Models\ClienteHistoricoConsumo;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Importa lecturas anteriores desde Excel.
 *
 * Columnas requeridas (encabezado en fila 1):
 *   suscriptor | lec_anterior | promedio | consumo
 *
 * - lec_anterior + promedio  →  ordenescu.LA / Promedio  (período destino)
 * - consumo                  →  cliente_historico_consumos (período facturado)
 */
class LecturaAnteriorImport implements ToCollection, WithHeadingRow
{
    public int $actualizados  = 0;
    public int $noEncontrados = 0;
    public int $errores       = 0;

    /** Período de las órdenes a actualizar (YYYYMM). */
    private string $periodoDestino;

    /** Período al que pertenece el consumo histórico (YYYYMM). Null = no registrar. */
    private ?string $periodoFacturado;

    public function __construct(string $periodoDestino, ?string $periodoFacturado = null)
    {
        $this->periodoDestino  = $periodoDestino;
        $this->periodoFacturado = $periodoFacturado;
    }

    public function collection(Collection $rows)
    {
        // Pre-cargar clientes por suscriptor para evitar N+1
        $suscriptores = $rows
            ->pluck('suscriptor')
            ->filter()
            ->map(fn ($s) => trim((string) $s))
            ->unique()
            ->values();

        $clientes = Cliente::whereIn('suscriptor', $suscriptores)
            ->get(['id', 'suscriptor'])
            ->keyBy('suscriptor');

        foreach ($rows as $row) {
            try {
                $suscriptor  = trim((string) ($row['suscriptor']  ?? ''));
                $lecAnterior = $row['lec_anterior'] ?? null;
                $promedio    = $row['promedio']     ?? null;
                $consumo     = $row['consumo']      ?? null;

                if ($suscriptor === '') continue;

                // ── 1. Actualizar LA y Promedio en ordenescu ──────────────────
                $existe = Ordenesmtl::where('Periodo', $this->periodoDestino)
                    ->where('Suscriptor', $suscriptor)
                    ->exists();

                if (!$existe) {
                    $this->noEncontrados++;
                    continue;
                }

                $datosOrden = [];
                if ($lecAnterior !== null && $lecAnterior !== '') {
                    $datosOrden['LA'] = (int) $lecAnterior;
                }
                if ($promedio !== null && $promedio !== '') {
                    $datosOrden['Promedio'] = (int) round((float) $promedio);
                }

                if (!empty($datosOrden)) {
                    Ordenesmtl::where('Periodo', $this->periodoDestino)
                        ->where('Suscriptor', $suscriptor)
                        ->update($datosOrden);
                    $this->actualizados++;
                }

                // ── 2. Registrar consumo en histórico ─────────────────────────
                if ($this->periodoFacturado && $consumo !== null && $consumo !== '') {
                    $cliente = $clientes->get($suscriptor);
                    if ($cliente) {
                        ClienteHistoricoConsumo::registrarYActualizarPromedio(
                            $cliente->id,
                            $suscriptor,
                            $this->periodoFacturado,
                            (int) $consumo,
                            ($lecAnterior !== null && $lecAnterior !== '') ? (int) $lecAnterior : null,
                        );
                    }
                }
            } catch (\Throwable $e) {
                $this->errores++;
            }
        }
    }
}
