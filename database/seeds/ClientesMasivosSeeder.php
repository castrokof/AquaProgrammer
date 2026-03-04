<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Puebla la tabla `clientes` de forma masiva usando los registros
 * ya existentes en `ordenescu`.
 *
 * Por cada suscriptor único toma el registro MÁS RECIENTE (mayor Periodo)
 * para obtener: nombre, apellido, dirección, teléfono, serie medidor,
 * ruta y consecutivo.
 *
 * Es idempotente: omite suscriptores que ya existen en `clientes`.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=ClientesMasivosSeeder
 */
class ClientesMasivosSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Iniciando carga masiva de clientes desde ordenescu...');

        // Último período registrado por suscriptor
        $subquery = DB::table('ordenescu')
            ->select('Suscriptor', DB::raw('MAX(Periodo) as ultimo_periodo'))
            ->whereNotNull('Suscriptor')
            ->where('Suscriptor', '<>', '')
            ->groupBy('Suscriptor');

        // Datos del registro más reciente de cada suscriptor
        $registros = DB::table('ordenescu as o')
            ->joinSub($subquery, 'ult', function ($join) {
                $join->on('o.Suscriptor', '=', 'ult.Suscriptor')
                     ->on('o.Periodo',    '=', 'ult.ultimo_periodo');
            })
            ->select(
                'o.Suscriptor',
                'o.Nombre',
                'o.Apell',
                'o.Direccion',
                'o.Telefono',
                'o.Ref_Medidor',
                'o.Ruta',
                'o.consecutivoRuta'
            )
            ->get();

        $this->command->info("  Suscriptores únicos encontrados en ordenescu: {$registros->count()}");

        // Suscriptores ya registrados en clientes
        $yaExisten = DB::table('clientes')
            ->pluck('suscriptor')
            ->flip()
            ->toArray();

        $now      = now()->format('Y-m-d H:i:s');
        $creados  = 0;
        $omitidos = 0;
        $lote     = [];

        foreach ($registros as $r) {
            $suscriptor = trim($r->Suscriptor);

            if (isset($yaExisten[$suscriptor])) {
                $omitidos++;
                continue;
            }

            $lote[] = [
                'suscriptor'   => $suscriptor,
                'nombre'       => trim($r->Nombre ?? ''),
                'apellido'     => trim($r->Apell  ?? ''),
                'direccion'    => $r->Direccion   ?? null,
                'telefono'     => $r->Telefono    ?? null,
                'serie_medidor'=> $r->Ref_Medidor ?? null,
                'ruta'         => $r->Ruta        ?? null,
                'consecutivo'  => $r->consecutivoRuta ?? null,
                'servicios'    => 'AG-AL',
                'tipo_uso'     => 'RESIDENCIAL',
                'tiene_medidor'=> 1,
                'estado'       => 'ACTIVO',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $creados++;

            // Insertar en lotes de 500 para no saturar memoria
            if (count($lote) >= 500) {
                DB::table('clientes')->insert($lote);
                $lote = [];
                $this->command->info("  ... {$creados} clientes insertados hasta ahora.");
            }
        }

        if (!empty($lote)) {
            DB::table('clientes')->insert($lote);
        }

        $this->command->info('');
        $this->command->info("  ✔  Carga masiva completada.");
        $this->command->info("     Creados : {$creados}");
        $this->command->info("     Omitidos (ya existían): {$omitidos}");
        $this->command->info('');
        $this->command->warn('  Recuerda completar: estrato_id, sector, tipo_uso, tiene_medidor');
        $this->command->warn('  para cada cliente desde el panel /clientes o en bloque con UPDATE.');
    }
}
