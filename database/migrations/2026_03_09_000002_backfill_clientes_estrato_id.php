<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Rellena clientes.estrato_id usando el último estrato registrado en ordenescu.
 * También puebla estrato_snapshot en facturas existentes donde sea null.
 */
class BackfillClientesEstratoId extends Migration
{
    public function up()
    {
        // 1. Construir mapa numero → id desde la tabla estratos
        $estratosMap = DB::table('estratos')
            ->pluck('id', 'numero')  // [1 => 1, 2 => 2, ...]
            ->toArray();

        if (empty($estratosMap)) {
            return; // Sin estratos no hay nada que hacer
        }

        // 2. Para cada cliente sin estrato_id, tomar el estrato más reciente de ordenescu
        $clientes = DB::table('clientes')
            ->whereNull('estrato_id')
            ->pluck('suscriptor', 'id'); // [cliente_id => suscriptor]

        foreach ($clientes as $clienteId => $suscriptor) {
            $orden = DB::table('ordenescu')
                ->where('Suscriptor', $suscriptor)
                ->whereNotNull('estrato')
                ->where('estrato', '>', 0)
                ->orderByDesc('Año')
                ->orderByDesc('Mes')
                ->value('estrato');

            if ($orden && isset($estratosMap[$orden])) {
                DB::table('clientes')
                    ->where('id', $clienteId)
                    ->update(['estrato_id' => $estratosMap[$orden]]);
            }
        }

        // 3. Rellenar estrato_snapshot en facturas donde sea null,
        //    usando el estrato_id que acabamos de asignar al cliente
        DB::statement("
            UPDATE facturas f
            JOIN clientes c ON c.id = f.cliente_id
            JOIN estratos e ON e.id = c.estrato_id
            SET f.estrato_snapshot = e.numero
            WHERE f.estrato_snapshot IS NULL
              AND c.estrato_id IS NOT NULL
        ");

        // 4. Recalcular subsidio_emergencia en facturas donde sea 0
        //    pero el estrato tiene porcentaje != 0
        $estratos = DB::table('estratos')
            ->where('porcentaje_subsidio', '!=', 0)
            ->get(['id', 'numero', 'porcentaje_subsidio']);

        foreach ($estratos as $estrato) {
            $pct = (float) $estrato->porcentaje_subsidio;

            $facturas = DB::table('facturas')
                ->where('estrato_snapshot', $estrato->numero)
                ->where('subsidio_emergencia', 0)
                ->where('consumo_basico_acueducto_valor', '>', 0)
                ->select('id', 'consumo_basico_acueducto_valor',
                         'total_facturacion_acueducto',
                         'subtotal_conexion_otros_acueducto',
                         'total_a_pagar')
                ->get();

            foreach ($facturas as $f) {
                $subsidio = round((float) $f->consumo_basico_acueducto_valor * $pct / 100, 2);

                DB::table('facturas')->where('id', $f->id)->update([
                    'subsidio_emergencia'               => $subsidio,
                    'total_facturacion_acueducto'       => round((float) $f->total_facturacion_acueducto - $subsidio, 2),
                    'subtotal_conexion_otros_acueducto' => round((float) $f->subtotal_conexion_otros_acueducto - $subsidio, 2),
                    'total_a_pagar'                     => round((float) $f->total_a_pagar - $subsidio, 2),
                ]);
            }
        }
    }

    public function down()
    {
        // Revertir: limpiar estrato_id de clientes y subsidio de facturas
        DB::table('clientes')->update(['estrato_id' => null]);
        DB::table('facturas')->update([
            'estrato_snapshot'   => null,
            'subsidio_emergencia' => 0,
        ]);
    }
}
