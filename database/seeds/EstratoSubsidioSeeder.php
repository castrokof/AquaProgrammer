<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstratoSubsidioSeeder extends Seeder
{
    /**
     * Carga o actualiza los valores de subsidio/contribución por estrato.
     *
     * Columnas gestionadas:
     *   porcentaje_subsidio          → % sobre consumo básico (positivo=subsidio, negativo=sobretasa)
     *   subsidio_fijo_acueducto      → monto fijo acueducto (0 = usar porcentaje)
     *   subsidio_fijo_alcantarillado → monto fijo alcantarillado (0 = usar porcentaje)
     *   consumo_minimo_subsidio      → m³ mínimos para que aplique el subsidio/sobretasa
     *
     * Ejecutar: php artisan db:seed --class=EstratoSubsidioSeeder
     */
    public function run()
    {
        $estratos = [
            //  numero  nombre              codigo   pct    fijoAc  fijoAl  consMin
            [1, 'Bajo-bajo',              'E1',    70.00,  0,      0,      4],
            [2, 'Bajo',                   'E2',    40.00,  0,      0,      4],
            [3, 'Medio-bajo',             'E3',    15.00,  0,      0,      4],
            [4, 'Medio',                  'E4',     0.00,  0,      0,      0],
            [5, 'Medio-alto',             'E5',   -50.00,  0,      0,      4],
            [6, 'Alto',                   'E6',   -60.00,  0,      0,      4],
            [7, 'Comercial',              'COM',  -60.00,  0,      0,      4],
            [8, 'Industrial',             'IND',  -60.00,  0,      0,      4],
            [9, 'Oficial / Especial',     'OFI',    0.00,  0,      0,      0],
        ];

        foreach ($estratos as [$numero, $nombre, $codigo, $pct, $fijoAc, $fijoAl, $consMin]) {
            DB::table('estratos')->updateOrInsert(
                ['numero' => $numero],
                [
                    'nombre'                       => $nombre,
                    'codigo'                       => $codigo,
                    'porcentaje_subsidio'          => $pct,
                    'subsidio_fijo_acueducto'      => $fijoAc,
                    'subsidio_fijo_alcantarillado' => $fijoAl,
                    'consumo_minimo_subsidio'      => $consMin,
                    'activo'                       => true,
                    'updated_at'                   => now(),
                ]
            );
        }

        $this->command->info('Estratos y subsidios cargados correctamente.');
    }
}
