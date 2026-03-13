<?php

namespace App\Imports;

use App\Models\Admin\Ordenesmtl;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Importa lecturas anteriores desde Excel para actualizar ordenescu.
 *
 * Columnas requeridas (encabezado en fila 1):
 *   suscriptor | lec_anterior | consumo | promedio
 *
 * El período destino se pasa por constructor.
 */
class LecturaAnteriorImport implements ToCollection, WithHeadingRow
{
    public int $actualizados  = 0;
    public int $noEncontrados = 0;
    public int $errores       = 0;

    private string $periodoDestino;

    public function __construct(string $periodoDestino)
    {
        $this->periodoDestino = $periodoDestino;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $suscriptor   = trim((string)($row['suscriptor']   ?? ''));
                $lecAnterior  = $row['lec_anterior'] ?? null;
                $consumo      = $row['consumo']      ?? null;
                $promedio     = $row['promedio']      ?? null;

                if ($suscriptor === '') continue;

                $count = Ordenesmtl::where('Periodo', $this->periodoDestino)
                    ->where('Suscriptor', $suscriptor)
                    ->count();

                if ($count === 0) {
                    $this->noEncontrados++;
                    continue;
                }

                $datos = [];
                if ($lecAnterior !== null && $lecAnterior !== '') {
                    $datos['LA'] = (int) $lecAnterior;
                }
                if ($consumo !== null && $consumo !== '') {
                    $datos['Cons_Act'] = (int) $consumo;
                }
                if ($promedio !== null && $promedio !== '') {
                    $datos['Promedio'] = (int) round((float) $promedio);
                }

                if (!empty($datos)) {
                    Ordenesmtl::where('Periodo', $this->periodoDestino)
                        ->where('Suscriptor', $suscriptor)
                        ->update($datos);
                    $this->actualizados++;
                }
            } catch (\Throwable $e) {
                $this->errores++;
            }
        }
    }
}
